<?php
include_once '../classes/businessLogic.php';
session_start();

$bl = new businessLogic();

$postID = $_POST['postID'];
$flagID = $_POST['flagID'];
$userID = $_POST['userID'];

$bl->deleteFlaggedPost($postID, $flagID, $userID);