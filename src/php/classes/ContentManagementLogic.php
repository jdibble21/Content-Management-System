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

    /**
     * Create a message to be seen in admin dashboard referencing
     * a blocked post by user. When viewed in dashboard, options
     * (allow, delete post, delete user) will be available to admin
     * @param $postID
     * @param $blockReason
     */
    public function createBlockMessage($postID,$blockReason){
        $this->dl->insertBlockMessage([$blockReason,$postID,"No resolution yet","No appeal"]);
    }

    /**
     * Print a list of current block messages and options
     * for each message (allow, delete post, delete user). Intended
     * to be used in admin dashboard.
     */
    public function getBlockMessages(){
        return $this->getBlocks();

    }

    /**
     * Print a list of past resolved block messages, includes
     * a description of the resolution and details associated with
     * original message
     */
    public function generateResolvedMessageList(){
        $resolvedMessages = $this->dl->getResolvedBlockMessages();
        foreach($resolvedMessages as $message){
            $this->generateResolveMessage($message);
        }
    }

    /**
     * Admin option to changed a posts block value to false, allowing
     * the post to be seen by all users and resolving the block message
     * @param $postID
     */
    public function allowPost($postID){
        $this->dl->updatePostBlockedValue([1,$postID]);
    }
    function resolveBlockMessage($msgID,$description){
        $this->dl->updateResolveBlockMessage([0,$description,$msgID]);
    }
    /**
     * Scan string of input for profanity. 'words' to check determined
     * by space between characters
     * @param $input
     * @return bool
     */
    public function checkInputForProfanity($input){
        return $this->tf->checkForProfanityInWords($input);
    }

    /**
     * Display current words whitelisted for the filter to allow.
     * Used in Admin dashboard
     */
    public function generateCurrentWhitelist(){
        $words = $this->tf->getWhitelist();
        foreach ($words as $word){
            $this->generateWhitelistWord($word);
        }
    }
    /**
     * Display current words blacklisted for the filter to block.
     * Used in Admin dashboard
     */
    function generateCurrentBlacklist(){
        $words = $this->tf->getBlacklist();
        foreach ($words as $word){
            $this->generateBlacklistWord($word);
        }
    }
    function getWhitelist(){
        return $this->dl->getWhitelist();
    }

    function getBlacklist(){
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

    /**
     * Enable a user to appeal a blocked post for review by admin.
     * Will update the block message associated with post as having
     * been appealed
     * @param $postID
     * @param $appealMessage
     */
    public function appealBlock($postID, $appealMessage){
        $this->dl->updateBlockedMessageAppeal([0, $appealMessage, $postID]);
    }

    /**
     * Remove posts marked as blocked using object array received
     * from host database.
     *
     * Recommended use: before displaying posts, using object directly
     * fetched from database
     * @param $posts
     * @return array
     */
    public function removeBlockedPosts($posts){
        $tagged_posts = $this->checkBlockedPosts($posts);
        $counter = 0;
        foreach($tagged_posts as $post){
            // if post contains blocked 'true' value, remove from posts
            if($post['blockStatus'] == "0"){
                unset($tagged_posts[$counter]);
            }
            $counter++;
        }
        return $tagged_posts;
    }
    public function addBlockedPostReference($postID){

    }
    public function getBlockMessageByPostID($postID){
        return $this->dl->getBlockMessageFromPostID($postID);
    }

    public function viewResolvedMessagesInHTMLTable(){

    }
    // end exposed functions
    //-----------------------------------------------------------------------------------------------------------------

    //admin tools



    protected function saveDeletedPost($userID,$postID,$content,$image){
        $this->dl->insertDeletedPost([$userID,$postID,$content,$image]);
    }

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
        $modifiedArray = [];
        $blockedPostArray = $this->getBlockPosts();
        foreach ($standardPostsObject as $post){
            $blockValue = $this->getArrayValue($post['postID'],$blockedPostArray);
            $modifiedPost = $this->appendBlockValue($post,$blockValue);
            array_push($modifiedArray,$modifiedPost);
        }
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