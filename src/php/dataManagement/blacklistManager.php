<?php
require("../classes/TextFilter.php");
session_start();

$tf = new TextFilter();
$word = $_POST['word'];

$tf->addToBlacklist($word);