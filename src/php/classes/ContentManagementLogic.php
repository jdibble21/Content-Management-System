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

    //Posts
    /**
     * Associates a postID feed array with block value.
     * If post is blocked, a "block" value is appended to postID, if not
     * "safe" value is appended. Used to determine which posts to show or
     * hide to other users.
     * @param $updates
     * @return updates
     */
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

    /**
     * Return predicted class and percentage of confidence for an
     * image that is blocked
     * @param $postID
     * @return array
     */
    public function getBlockedImageData($postID){
        $data = $this->dl->getBlockedImageData($postID);
        $format_value = floatval($data['blockValue'])*100;
        return [$data['blockReason'],round($format_value,2)];
    }

    /**
     * Inserts a reference to blocked image, and predicted class and
     * percentage of confidence for the reason it was blocked.
     * @param $postID
     * @param $filterReason
     * @param $filterValue
     */
    public function addBlockedImageData($postID,$filterReason,$filterValue){
        $this->dl->insertBlockedImage([$postID,$filterReason,$filterValue]);
    }

    /**
     * Used to wipe old traces of userID rows in database on user deletion
     * @param $userID
     */
    public function resolveUserContentData($userID){
        $this->dl->deleteAllUserBanEntries($userID);
    }

    //Text Filter functions

    /**
     * Used to call TextFilter class to check a string of input for
     * profanity. Returns True if no profanity is detected, False if profanity exists
     * @param $input
     * @return bool
     */
    public function checkInputForProfanity($input){
        return $this->tf->checkForProfanityInWords($input);
    }

    /**
     * Produces an HTML table of whitelisted words for the text filter.
     * Designed to be viewed in admin dashboard
     */
    public function generateCurrentWhitelist(){
        $words = $this->tf->getWhitelist();
        foreach ($words as $word){
            $this->generateWhitelistWord($word);
        }
    }
    /**
     * Produces an HTML table of blacklisted words for the text filter.
     * Designed to be viewed in admin dashboard
     */
    public function generateCurrentBlacklist(){
        $words = $this->tf->getBlacklist();
        foreach ($words as $word){
            $this->generateBlacklistWord($word);
        }
    }

    /**
     * Gets whitelisted words stored in database
     * @return array
     */
    public function getWhitelist(){
        return $this->dl->getWhitelist();
    }

    /**
     * Gets blacklisted words stored in database
     * @return array
     */
    public function getBlacklist(){
        return $this->dl->getBlacklist();
    }

    /**
     * Adds a word to global whitelist
     * @param $word
     */
    public function addWhitelistWord($word){
        $this->dl->insertWhitelistWord($word);
    }

    /**
     * Adds a word to global blacklist
     * @param $word
     */
    public function addBlacklistWord($word){
        $this->dl->insertBlacklistWord($word);
    }

    /**
     * Removes a whitelisted word from global whitelist
     * @param $word
     */
    public function removeWhitelistWord($word){
        $this->dl->deleteWhitelistWord($word);
    }

    /**
     * Removes a blacklisted words from global blacklist
     * @param $word
     */
    public function removeBlacklistWord($word){
        $this->dl->deleteBlacklistWord($word);
    }

    //Custom Org Blacklists

    /**
     * Outputs an HTML table of custom organization blacklist.
     * Feature has to be enabled in org settings to use this.
     * @param $orgID
     */
    public function generateCurrentOrgBlacklist($orgID){
        $words = $this->tf->getOrgBlacklist($orgID);
        foreach ($words as $word){
            $this->generateBlacklistWord($word);
        }
    }

    /**
     * Adds a word to be used in custom organization blacklist.
     * Feature has to be enabled in org settings to use this
     * @param $word
     * @param $orgID
     */
    public function addOrgBlacklistWord($word,$orgID){
        $this->dl->insertOrgBlacklistWord($word, $orgID);
    }

    /**
     * Modified TextFilter profanity check to make use of custom
     * organization blacklist in addition to a normal profanity check
     * @param $input
     * @param $orgID
     * @return bool
     */
    public function checkOrgProfanity($input, $orgID){
        return $this->tf->checkForProfanityOrg($input, $orgID);
    }

    /**
     * Called when organization is created to initialize default organization
     * settings for post approval and custom blacklist toggles
     * @param $orgID
     */
    public function createOrgSettings($orgID){
        $this->dl->insertOrgSettings($orgID);
    }

    /**
     * Returns organization settings for post approval and custom blacklist
     * @param $orgID
     * @return array
     */
    public function getOrgContentSettings($orgID){
        $this->createOrgSettings($orgID);
        $settings = $this->dl->getOrgContentSettings($orgID);
        return [$settings['enablePostApproval'],$settings['enableBlacklist']];
    }

    /**
     * Used for determining whether or not a 'pending' value should be applied to
     * a user post to org page
     * @param $orgID
     * @return bool
     */
    public function checkOrgApprovalEnabled($orgID){
        $enabled = $this->dl->getOrgContentSettings($orgID);
        if($enabled['enablePostApproval'] == "0"){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Toggles on post approval for organizations. Activating this requires user's posts
     * to be approved by org admin before publicly viewable
     * @param $orgID
     */
    public function enableOrgPostApproval($orgID){
        //$this->createOrgSettings($orgID);
        //echo $this->dl->updateEnableOrgPostApproval($orgID);
        if($this->dl->updateEnableOrgPostApproval($orgID)){
            $this->createOrgSettings($orgID);
            $this->dl->updateEnableOrgPostApproval($orgID);
        }else{

        }
    }

    /**
     * Toggles off post approval for organizations. Activating this approves all currently
     * pending posts, and removes need for posts to be approved before public view
     * @param $orgID
     */
    public function disableOrgPostApproval($orgID){
        $this->dl->updateDisableOrgPostApproval($orgID);
        $this->dl->updateApproveAllOrgPosts($orgID);
    }

    /**
     * Toggles on custom blacklist. Will check uploaded content against a custom blacklist
     * @param $orgID
     */
    public function enableOrgBlacklist($orgID){
        if($this->dl->updateEnableOrgBlacklist($orgID)){
            $this->createOrgSettings($orgID);
            $this->dl->updateEnableOrgBlacklist($orgID);
        }
    }

    /**
     * Toggles off custom blacklist. Will not check uploaded content against blacklist
     * @param $orgID
     */
    public function disableOrgBlacklist($orgID){
        $this->dl->updateDisableOrgBlacklist($orgID);
    }
   //Blocked posts and related functions

    /**
     * Used when profanity or inapropriate content is found in a post. Will show up
     * in admin dashboard to be handled
     * @param $postID
     * @param $blockReason
     * @return string
     */
    public function createBlockMessage($postID,$blockReason){
        $this->addBlockedPostReference($postID);
        return $this->dl->insertBlockMessage([$blockReason,$postID,"No resolution yet","No appeal"]);
    }

    /**
     * Returns all blocked messages for blocked posts to be handled by super admin
     * @return array
     */
    public function getBlockMessages(){
        return $this->getBlocks();
    }

    /**
     * Returns some content of a deleted post for reference in resolved block messages
     * @param $postID
     * @return mixed
     */
    public function getDeletedPost($postID){
        return $this->dl->getDeletedPost($postID);
    }

    /**
     * Updates block message to resolved status, meaning post has been allowed, or deleted by user or admin
     * @param $msgID
     * @param $description
     */
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
    public function checkForOrgPendingPosts($orgID){
        return $this->dl->getOrgPendingPosts($orgID);
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