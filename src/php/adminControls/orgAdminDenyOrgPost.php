<?php
include_once '../../../../../classes/businessLogic.php';
session_start();
$bl = new businessLogic();

$postID = $_POST['postID'];

$bl->denyOrgPost($postID);

