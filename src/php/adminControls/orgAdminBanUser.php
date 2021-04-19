<?php
include_once '../../../../../classes/businessLogic.php';
session_start();
$bl = new businessLogic();

$username = $_POST['username'];
$orgID = $_POST['orgID'];

$userID = $bl->getUserIDByUsername($username);

if ($bl->isAdmin($orgID,$_SESSION['id'])) {
    $bl->banUserFromOrg($userID,$orgID);
    header("Location: /Pages/organization/manageOrg.php?orgID=$orgID&tab=USERS");
}
else {
    http_response_code(401);
}