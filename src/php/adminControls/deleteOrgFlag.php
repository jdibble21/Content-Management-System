<?php
include_once '../../../../../classes/businessLogic.php';
session_start();

$bl = new businessLogic();

$flagID = $_POST['flagID'];

$bl->deleteFlag($flagID);