<?php
include_once '../../../../../classes/businessLogic.php';
session_start();
$bl = new businessLogic();

$orgID = $_POST['orgID'];
$bl->disableOrgPostApproval($orgID);