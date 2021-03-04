<?php
require('includes.php');

class ContentManagementLogic
{
    private $dl;
    private $tf;
    private $bl;

    public function __construct(){
        $this->dl = new FilterDataLayer();
        $this->tf = new TextFilter();
        $this->bl = new businessLogic();
        // necessary to use businessLogic for betterflye db related features
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
    public function generateBlockList(){
        $blocks = $this->getBlocks();
        foreach ($blocks as $block){
            $this->generateBlock($block);
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
    protected function generateResolvedMessageList(){
        $resolvedMessages = $this->dl->getResolvedBlockMessages();
        foreach($resolvedMessages as $message){
            $this->generateResolveMessage($message);
        }
    }
    protected function generateResolveMessage(array $messageData){
        $blockReason = $messageData['blockReason'];
        $postID = $messageData['target'];
        $post = $this->bl->getPost($postID);
        if($post['postText'] == ""){
            list($username, $postContent, $postImage) = $this->getDeletedPost($postID);
            $resolution = $messageData['resolution'];
            $originalBlockDate = $messageData['blockDate'];
        }else{
            $post = $this->bl->getPost($postID);
            $postContent = $post['postText'];
            $type = $this->bl->getPoster($postID)[3];
            list($blockedUser, $username, $blockedLocation) = $this->bl->getPosterInfoFromPostID($type, $postID);
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

    protected function getBlocks(){
        return $this->dl->getBlockMessages();
    }

    protected function generateBlock(array $msgData){
        $msgID = $msgData['messageID'];$blockReason = $msgData['blockReason'];$postID = $msgData['target'];
        $appeal = $msgData['appeal'];$appealMessage = $msgData['appealMessage'];$blockDate = $msgData['blockDate'];

        //Add if statements to detect type of content that was blocked
        list($title, $content, $flaggedUserLink, $flaggedLocation, $imageLink) = $this->generatePostBlockData($blockReason,$postID);
        $this->displayBlockMessage($msgID,$title, $content,$flaggedUserLink,$flaggedLocation,$imageLink,$blockDate,$appeal,$appealMessage);

    }

    protected function generatePostBlockData($reason, $postID){
        $post = $this->bl->getPost($postID);
        $title = "The following <b>post</b> was blocked for: $reason";
        $content = $post['postText'];
        $image = (($post['postImage'] != 'null') ? "$post[postImage]" : "No Image");
        $type = $this->bl->getPoster($postID)[3];
        list($blockedUser, $blockedUserLink, $blockedLocation) = $this->bl->getPosterInfoFromPostID($type, $postID);
        $imagePath = $this->bl->getImagePathFromPostID($postID, $image, $blockedUser);
        $imageLink = $this->bl->getImageLinkFromImagePath($imagePath, $image);
        return array($title, $content, $blockedUserLink, $blockedLocation, $imageLink);
    }

    protected function displayBlockMessage($blockID,$title, $content, $userLink, $location, $imageLink, $blockDate, $appeal, $appealMessage){
        $headStyle = '';
        $messageStyle = '';
        if($appeal == 0){
            $headStyle = "style='background-color: orange;'";
            $messageStyle = "style='color: orange;'";
        }elseif ($appeal == 1){
            $appealMessage = "No appeal";
        }
        echo "<table border size=8 cellpadding=10>";
        echo "<tr>";
        echo "<th>User</th>";
        echo "<th>Reason</th>";
        echo "<th>Content</th>";
        echo "<th>Image</th>";
        echo "<th>Date of Block</th>";
        echo "<th $headStyle>Appeal</th>";
        echo "<th></th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>$userLink</td>";
        echo "<td>$title</td>";
        echo "<td>$content</td>";
        echo "<td>$imageLink</td>";
        echo "<td>$blockDate</td>";
        echo "<td $messageStyle>$appealMessage</td>";
        echo "<td>".$this->getBlockMessageOptions($blockID)."</td>";
        echo "</tr>";
        echo "</table>";
        echo "<br>";
    }

    protected function getBlockMessageOptions($blockID){
        $selectName = "#blockOptions".$blockID." option:selected";
        return "<p>Options:</p><p><select id='blockOptions$blockID'>
                         <option value='allow'>Allow</option>
                         <option value='deletePost'>Delete Post</option>
                         <option value='deleteUser'>Delete User</option>
                    </select></p>
                    <button class='btn btn-warning' onclick='resolve($blockID,\"post\",$(\"$selectName\").text())'>Resolve</button>";
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