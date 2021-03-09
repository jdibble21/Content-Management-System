<?php
include_once '../../../../../classes/businessLogic.php';

session_start();
$bl = new businessLogic();

$blockMsgID = $_POST['msgID'];

$postID = $bl->getPostIDFromBlockID($blockMsgID);
$userID = $bl->getUserIDFromPostID($postID);

//need to fix saving posts first
//$bl->addDeletedPost($userID,$postID);
$bl->deletePost($postID);
$bl->cms->resolveBlockMessage($blockMsgID,"Post was deleted by moderator. RESOLVED ON: ".date("Y-m-d")." ".date("h:i:sa"));
header("Location: /Pages/adminDashboard/adminDashboard.php");
