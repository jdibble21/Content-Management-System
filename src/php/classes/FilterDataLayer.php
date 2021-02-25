<?php

class FilterDataLayer
{

    private $conn;

    public function __construct(){
        $hostname = 'localhost';
        $port = 3306;
        $dbName = 'cmsdata';
        $dbUsername = 'root';
        $dbPass = '';
        //change mysql to database type
        $dsn = "mysql:host=$hostname;dbname=$dbName;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->conn = new PDO($dsn, $dbUsername, $dbPass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    //insert
    function insertBlockMessage(array $msgData){
        $query = $this->conn->prepare("insert into `blocks` (blockReason, target, resolution, blockDate, appealMessage) values (?,?,?,NOW(),?)");
        $query->execute($msgData);
    }
    function insertWhitelistWord($word){
        $query = $this->conn->prepare("insert into `whitelist` (word, dateAdded) values (?,NOW())");
        $query->execute([$word]);
    }
    function insertBlacklistWord($word){
        $query = $this->conn->prepare("insert into `blacklist` (word, dateAdded) values (?,NOW())");
        $query->execute([$word]);
    }
    //select
    function getBlockMessageFromPostID($postID){
        $query = $this->conn->prepare("select * from `blocks` where target=?");
        $query->execute([$postID]);
        return $query->fetch();
    }
    function getBlockedPosts(){
        $query = $this->conn->prepare("select * from `'blockedposts`");
        $query->execute();
        return $query->fetchAll();
    }
    function getWhitelist(){
        $query = $this->conn->prepare("select * from `whitelist`");
        $query->execute();
        return $query->fetchAll();
    }
    function getBlacklist(){
        $query = $this->conn->prepare("select * from `blacklist`");
        $query->execute();
        return $query->fetchAll();
    }
}