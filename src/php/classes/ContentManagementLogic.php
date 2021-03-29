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
    
   //Blocked posts and related functions
    public function createBlockMessage($postID,$blockReason){
        $this->addBlockedPostReference($postID);
        return $this->dl->insertBlockMessage([$blockReason,$postID,"No resolution yet","No appeal"]);
    }
    public function getBlockMessages(){
        return $this->getBlocks();

    }
    public function resolveBlockMessage($msgID,$description){
        $this->dl->updateResolveBlockMessage([0,$description,$msgID]);
    }
    public function generateResolvedMessageList(){
        $resolvedMessages = $this->dl->getResolvedBlockMessages();
        foreach($resolvedMessages as $message){
            $this->generateResolveMessage($message);
        }
    }
    public function getLimitedApprovedOrgPosts($orgID,$limit){
        return $this->dl->getLimitedOrgUpdates($orgID,$limit);
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
    public function removeBlockedPosts($posts){
        $tagged_posts = $this->checkBlockedPosts($posts);
        $counter = 0;
        foreach($tagged_posts as $post){
            if($post['blockStatus'] == "0"){
                unset($tagged_posts[$counter]);
            }
            $counter++;
        }
        return $tagged_posts;
    }
    public function addBlockedPostReference($postID){
        $this->dl->insertBlockPostReference($postID);
    }
    public function removeBlockedPostReference($postID){
        $this->dl->deleteBlockPostReference($postID);
    }
    public function saveDeletedPost($userID,$fullName,$postID,$content,$image){
        $this->dl->insertDeletedPost([$userID,$fullName,$postID,$content,$image]);
    }
    public function getRecentBlockMessage(){
        return $this->dl->getRecentBlockMessage();
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
    public function AddUserPostToOrg(array $postData){
        $this->dl->insertUserPostToOrg($postData);
    }
    public function getBannedOrgUsers($orgID){
        return $this->dl->getOrgBannedUsers($orgID);
    }
    //ID getters and setters
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

    //posts
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