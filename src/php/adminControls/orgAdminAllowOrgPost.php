<?php
session_start();
$cml = new ContentManagementLogic();

$postID = $_POST['postID'];

$cml->allowUserOrgPost($postID);

