<?php
require_once ("src\php\classes\FilterDataLayer.php");

class ContentManagementLogic
{
    private $dl;

    public function __construct(){
        $this->dl = new FilterDataLayer();
    }


    //exposed functions to library user
    //------------------------------------------------------------------------------------------------------------------
    public function checkInputForProfanity($input){

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
    function getRecentBlockMessage(){
        return $this->dl->getRecentBlockMessage();

    }


    function saveDeletedPost($userID,$postID,$content,$image){
        $this->dl->insertDeletedPost([$userID,$postID,$content,$image]);
    }
    function allowPost($postID){
        $this->dl->updatePostBlockedValue([1,$postID]);
    }

    function blockPost($postID){
        $this->dl->updatePostBlockedValue([0,$postID]);
    }
    function getUserPost($postID){
        $postData = $this->dl->getPostById($postID);
        return $postData['blocked'];
    }
    function getResolveMessageById($msgID){
        return $this->dl->getResolvedBlockMessageByID($msgID);
    }
    function generateResolvedMessageList(){
        $resolvedMessages = $this->dl->getResolvedBlockMessages();
        foreach($resolvedMessages as $message){
            $this->generateResolveMessage($message);
        }
    }
    function generateResolveMessage(array $messageData){
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
    function displayResolvedMessage($userLink, $reason, $originalContent,$resolution,$originalBlockDate){
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
    function createBlockMessage($postID,$blockReason){
        $resolution = "No resolution yet";
    }
    function getBlockPosts(){
        return $this->dl->getBlockedPosts();
    }

    //posts
    function checkBlockedPosts($standardPostsObject){
        $modifiedArray = [];
        $blockedPostArray = $this->getBlockPosts();
        foreach ($standardPostsObject as $post){
            $blockValue = $this->getArrayValue($post['postID'],$blockedPostArray);
            $modifiedPost = $this->appendBlockValue($post,$blockValue);
            array_push($modifiedArray,$modifiedPost);
        }
        return $modifiedArray;
    }
    function getArrayValue($postIDToGet,$objectArray){
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