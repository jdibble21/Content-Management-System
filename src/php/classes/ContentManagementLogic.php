<?php
require_once ("src\php\classes\FilterDataLayer.php");
require_once ("src\php\classes\TextFilter.php");

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
     * @param $postID
     * @param $blockReason
     */
    public function createBlockMessage($postID,$blockReason){
        $this->dl->insertBlockMessage([$blockReason,$postID,"No resolution yet","No appeal"]);
    }

    /**
     * @param $postID
     */
    public function allowPost($postID){
        $this->dl->updatePostBlockedValue([1,$postID]);
    }
    /**
     * Scan string of input for profanity. 'words' to check determined
     * by space between characters
     * @param $input
     *
     * @return bool
     */
    public function checkInputForProfanity($input){
        return $this->tf->checkForProfanityInWords($input);
    }

    public function appealBlock($postID, $appealMessage){

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

    public function viewBlockQueue(){

    }
    public function viewResolvedMessagesInHTMLTable(){

    }
    // end exposed functions
    //-----------------------------------------------------------------------------------------------------------------

    //admin tools
    protected function getRecentBlockMessage(){
        return $this->dl->getRecentBlockMessage();

    }


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
    protected function generateResolvedMessageList(){
        $resolvedMessages = $this->dl->getResolvedBlockMessages();
        foreach($resolvedMessages as $message){
            $this->generateResolveMessage($message);
        }
    }
    protected function generateResolveMessage(array $messageData){
        $blockReason = $messageData['blockReason'];
        $postID = $messageData['target'];
        $post = $this->getPost($postID);
        if($post['postText'] == ""){
            list($username, $postContent, $postImage) = $this->getDeletedPost($postID);
            $resolution = $messageData['resolution'];
            $originalBlockDate = $messageData['blockDate'];
        }else{
            $post = $this->getPost($postID);
            $postContent = $post['postText'];
            $type = $this->getPoster($postID)[3];
            list($blockedUser, $username, $blockedLocation) = $this->getPosterInfoFromPostID($type, $postID);
            $resolution = $messageData['resolution'];
            $originalBlockDate = $messageData['blockDate'];
        }
        $this->displayResolvedMessage($username,$blockReason,$postContent,$resolution,$originalBlockDate);
    }
    protected function displayResolvedMessage($userLink, $reason, $originalContent,$resolution,$originalBlockDate){
        echo "<table border size=8 cellpadding=8>";
        echo "<tr>";
        echo "<th>User</th>";
        echo "<th>Resolution</th>";
        echo "<th>Reason for Original Block</th>";
        echo "<th>Original Content</th>";
        echo "<th>Original Block Date</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>$userLink</td>";
        echo "<td>$resolution</td>";
        echo "<td>$reason</td>";
        echo "<td>$originalContent</td>";
        echo "<td>$originalBlockDate</td>";
        echo "</tr>";
        echo "</table>";
        echo "<br>";
    }

    //blocking
    protected function getBlockPosts(){
        return $this->dl->getBlockedPosts();
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
}