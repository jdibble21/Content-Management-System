<?php
include_once '../../../../../classes/businessLogic.php';
session_start();
$bl = new businessLogic();

$orgID = $_POST['orgID'];

$posts = $bl->checkIfOrgPendingPosts($orgID);

if($posts == null){
    $_SESSION['pendingOrgPosts'] = "1";
}else{
    $_SESSION['pendingOrgPosts'] = "0";
}