<?php
include_once "../classes/ContentManagementLogic.php";
session_start();

$cms = new ContentManagementLogic();

$word = $_POST['word'];
$_SESSION['testVal'] = $word;
$cms->addBlacklistWord($word);