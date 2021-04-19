<?php
include_once '../../../../../classes/businessLogic.php';
session_start();
$bl = new businessLogic();

$username = $_POST['username'];

$userCheck = $bl->getUserIDByUsername($username);
if($userCheck == null){
    $_SESSION['userExists'] = "1";
}else{
    $_SESSION['userExists'] = "0";
}
