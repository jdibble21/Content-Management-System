<?php
include_once "../classes/ContentManagementLogic.php";
session_start();

$cms = new ContentManagementLogic();

$word = $_POST['word'];
$cms->addWhitelistWord($word);