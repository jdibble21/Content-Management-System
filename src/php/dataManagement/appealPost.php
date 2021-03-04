<?php
include_once '../classes/ContentManagementLogic.php';
session_start();

$cms = new ContentManagementLogic();

$postID = $_POST['postID'];
$reason = $_POST['reason'];

$cms->appealBlock($postID,$reason);