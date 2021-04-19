<?php
require('includes.php');

class ContentManagementLogic
{
    private $dl;
    private $tf;

    public function __construct(){
        $this->dl = new FilterDataLayer();
        $this->tf = new TextFilter();
    }

    //exposed functions to library user
    //------------------------------------------------------------------------------------------------------------------
    //Posts
    public function cleanPostFeed($updates){
        $blockedPosts = $this->dl->getBlockedPosts();
        for($i = 0; $i < sizeof($updates); $i++){
            $blocked=false;
            for($j = 0; $j < sizeof($blockedPosts); $j++){
                if($updates[$i]['postID'] == $blockedPosts[$j]['postID']){
                    array_push($updates[$i],"block");
                    $blocked=true;
                    break;
                }
            }
            if(!$blocked){
                array_push($updates[$i],"safe");
            }
        }
        return $updates;
    }
    public function getBlockedImageData($postID){
        $data = $this->dl->getBlockedImageData($postID);
        $format_value = floatval($data['blockValue'])*100;
        return [$data['blockReason'],round($format_value,2)];
    }
    public function addBlockedImageData($postID,$filterReason,$filterValue){
        $this->dl->insertBlockedImage([$postID,$filterReason,$filterValue]);
    }
    public function resolveUserContentData($userID){
        $this->dl->deleteAllUserBanEntries($userID);
    }

    //Text Filter functions
    public function checkInputForProfanity($input){
        return $this->tf->checkForProfanityInWords($input);
    }
    public function generateCurrentWhitelist(){
        $words = $this->tf->getWhitelist();
        foreach ($words as $word){
            $this->generateWhitelistWord($word);
        }
    }
    public function generateCurrentBlacklist(){
        $words = $this->tf->getBlacklist();
        foreach ($words as $word){
            $this->generateBlacklistWord($word);
        }
    }
    public function getWhitelist(){
        return $this->dl->getWhitelist();
    }
    public function getBlacklist(){
        return $this->dl->getBlacklist();
    }
    public function addWhitelistWord($word){
        $this->dl->insertWhitelistWord($word);
    }
    public function addBlacklistWord($word){
        $this->dl->insertBlacklistWord($word);
    }
    public function removeWhitelistWord($word){
        $this->dl->deleteWhitelistWord($word);
    }
    public function removeBlacklistWord($word){
        $this->dl->deleteBlacklistWord($word);
    }

    //Custom Org Blacklists
    public function generateCurrentOrgBlacklist($orgID){
        $words = $this->tf->getOrgBlacklist($orgID);
        foreach ($words as $word){
            $this->generateBlacklistWord($word);
        }
    }
    public function addOrgBlacklistWord($word,$orgID){
        $this->dl->insertOrgBlacklistWord($word, $orgID);
    }

    public function checkOrgProfanity($input, $orgID){
        return $this->tf->checkForProfanityOrg($input, $orgID);
    }

    public function createOrgSettings($orgID){
        $this->dl->insertOrgSettings($orgID);
    }
    public function getOrgContentSettings($orgID){
        $settings = $this->dl->getOrgContentSettings($orgID);
        return [$settings['enablePostApproval'],$settings['enableBlacklist']];
    }
    public function enableOrgPostApproval($orgID){
        $this->dl->updateEnableOrgPostApproval($orgID);
    }
    public function disableOrgPostApproval($orgID){
        $this->dl->updateDisableOrgPostApproval($orgID);
    }
   //Blocked posts and related functions
    public function createBlockMessage($postID,$blockReason){
        $this->addBlockedPostReference($postID);
        return $this->dl->insertBlockMessage([$blockReason,$postID,"No resolution yet","No appeal"]);
    }
    public function getBlockMessages(){
        return $this->getBlocks();
    }
    public function getDeletedPost($postID){
        return $this->dl->getDeletedPost($postID);
    }
    public function resolveBlockMessage($msgID,$description){
        $this->dl->updateResolveBlockMessage([0,$description,$msgID]);
    }
   public function getResolvedBlockMessages(){
        return $this->dl->getResolvedBlockMessages();
   }
    public function getLimitedApprovedOrgPosts($orgID,$limit){
        return $this->dl->getLimitedOrgUpdates($orgID,$limit);
    }
    public function getApprovedOrgPosts($orgID){
        return $this->dl->getOrgUpdates($orgID);
    }
    public function getUserOrgPostIDs($userID,$orgID){
        return $this->dl->getUserOrgPosts([$userID,$orgID]);
    }
    public function allowPost($postID){
        $this->dl->updatePostBlockedValue([1,$postID]);
    }
    public function appealBlock($postID, $appealMessage){
        $this->dl->updateBlockedMessageAppeal([0, $appealMessage, $postID]);
    }

    public function addBlockedPostReference($postID){
        $this->dl->insertBlockPostReference($postID);
    }
    public function removeBlockedPostReference($postID){
        $this->dl->deleteBlockPostReference($postID);
    }
    public function deleteOrgPostReference($postID){
        $this->dl->deleteOrgPost($postID);
    }
    public function saveDeletedPost($userID,$fullName,$postID,$content,$image){
        $this->dl->insertDeletedPost([$userID,$fullName,$postID,$content,$image]);
    }
    public function getRecentBlockMessage(){
        return $this->dl->getRecentBlockMessage();
    }
    public function getPendingPost($orgID,$userID){
        return $this->dl->getOrgPendingPost([$orgID,$userID]);
    }
    public function getApprovedPost($postID){
        return $this->dl->getOrgPostByPostID($postID);
    }
    //Banning Users from Orgs
    public function getOrgBanStatus($userID){
        return $this->dl->getOrgBanStatus($userID);
    }
    public function banUserFromOrg(array $orgBan){
        $this->dl->insertBannedOrgMember($orgBan);
    }
    public function removeUserOrgBan($userID,$orgID){
        $this->dl->deleteUserBanEntry([$userID,$orgID]);
    }
    public function AddUserPostToOrg($postID,$userID,$orgID,$approved=1){
        $this->dl->insertUserPostToOrg([$postID,$userID,$orgID,$approved]);
    }
    public function getBannedOrgUsers($orgID){
        return $this->dl->getOrgBannedUsers($orgID);
    }
    public function allowUserOrgPost($postID){
        $this->dl->updateApproveOrgPost($postID);
    }
    public function denyOrgPost($postID){
        $this->dl->deleteOrgPendingPost($postID);
    }
    public function checkForPendingPost($orgID,$userID){
        $data = $this->dl->getOrgPendingPost([$orgID,$userID]);
        if($data['postID'] != ""){
            return true;
        }
        return false;
    }
    //ID getters and setters
    public function getPendingOrgPostIDs($orgID){
        $ids =  $this->dl->getOrgPendingPosts($orgID);
        if ($ids == null){
            echo "<p>No pending posts to review</p>";
        }
        return $ids;
    }
    public function getOrgIDByOrgPost($userID){
        return $this->dl->getOrgIDByOrgPoster($userID);
    }
    public function removeBlockMessageByID($msgID){
        $this->dl->deleteBlockMessageByID($msgID);
    }
    public function getBlockMessageByPostID($postID){
        return $this->dl->getBlockMessageFromPostID($postID);
    }
    public function getPostIDFromBlockID($blockID){
        $postID = $this->dl->getPostIDFromBlockID($blockID);
        return $postID['target'];
    }
    public function getBlockIDFromPostID($postID){
        $blockID = $this->dl->getBlockIDFromPostID($postID);
        return $blockID['messageID'];
    }
    // end exposed functions
    //-----------------------------------------------------------------------------------------------------------------


    protected function blockPost($postID){
        $this->dl->updatePostBlockedValue([0,$postID]);
    }
    protected function getUserPost($postID){
        $postData = $this->dl->getPostById($postID);
        return $postData['blocked'];
    }
    protected function getResolveMessageById($msgID){
        return $this->dl->getResolvedBlockMessageByID($msgID);
    }



    //blocking
    protected function getBlockPosts(){
        return $this->dl->getBlockedPosts();
    }

    protected function getBlocks(){
        return $this->dl->getBlockMessages();
    }

    protected function checkBlockedPosts($standardPostsObject){
        $blockedPostArray = $this->getBlockPosts();
        $blockValue = $this->getArrayValue($standardPostsObject['postID'],$blockedPostArray);
        $modifiedPost = $this->appendBlockValue($standardPostsObject,$blockValue);
        array_push($modifiedArray,$modifiedPost);

        return $modifiedArray;
    }
    protected function getArrayValue($postIDToGet,$objectArray){
        foreach($objectArray as $object){
            if($postIDToGet == $object['postID']){
                return $object['blockStatus'];
            }
        }
        return null;
    }

    protected function appendBlockValue($post,$value){
        $post['blockStatus'] = $value;
        return $post;
    }

    //whitelist and blacklist
    protected function generateWhitelistWord(array $wordData){
        $wordText = $wordData['word'];
        $addDate = $wordData['dateAdded'];
        $this->displayWhitelistWord($wordText,$addDate);
    }

    protected function displayWhitelistWord($wordText, $addDate){
        echo "<tr>";
        echo "<td>$wordText</td>";
        echo "<td>$addDate</td>";
        echo "</tr>";
    }
    protected function generateBlacklistWord(array $wordData){
        $wordText = $wordData['word'];
        $addDate = $wordData['dateAdded'];
        $this->displayBlacklistWord($wordText,$addDate);
    }

    protected function displayBlacklistWord($wordText, $addDate){
        echo "<tr>";
        echo "<td>$wordText</td>";
        echo "<td>$addDate</td>";
        echo "</tr>";
    }
}