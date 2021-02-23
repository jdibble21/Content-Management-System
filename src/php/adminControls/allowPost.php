<?php
session_start();
$cml = new ContentManagementLogic();

$blockMsgID = $_POST['msgID'];

$cml->allowPost($cml->getPostFromBlockID($blockMsgID));
$cml->resolveBlockMessage($blockMsgID,"Post was allowed by moderator. RESOLVED ON: ".date("Y-m-d")." ".date("h:i:sa"));
header('Location: '.$_SERVER['REQUEST_URI']);