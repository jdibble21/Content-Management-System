<?php
include_once '../../../../../classes/businessLogic.php';
session_start();
$bl = new businessLogic();

$userID = $_POST['userID'];
$orgID = $_POST['orgID'];
$bl->unBanUserFromOrg($userID,$orgID);
header("Location: /Pages/organization/orgPage.php?orgID=$orgID");
