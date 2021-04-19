<?php
include_once '/xampp/htdocs/Betterflye/php/classes/businessLogic.php';

session_start();
$bl = new businessLogic();
$postID = $_POST['postID'];
$bl->deletePost($postID);