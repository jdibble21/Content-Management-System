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
    function insertBlockPostReference($postID){
        $query = $this->conn->prepare("insert into blockedposts (postID) values (?)");
        $query->execute([$postID]);
    }
    function insertUserPostToOrg(array $postData){
        $query = $this->conn->prepare("insert into userorgposts (postID, userID, orgID) values (?,?,?)");
        $query->execute($postData);
    }
    function insertBlockMessage(array $msgData){
        $query = $this->conn->prepare("insert into `blocks` (blockReason, target, resolution, blockDate, appealMessage) values (?,?,?,NOW(),?)");
        $query->execute($msgData);
        return $this->conn->lastInsertId();
    }
    function insertWhitelistWord($word){
        $query = $this->conn->prepare("insert into `whitelist` (word, dateAdded) values (?,NOW())");
        $query->execute([$word]);
    }
    function insertBlacklistWord($word){
        $query = $this->conn->prepare("insert into `blacklist` (word, dateAdded) values (?,NOW())");
        $query->execute([$word]);
    }
    function insertDeletedPost(array $postData){
        $query = $this->conn->prepare("insert into deletedposts (userID, fullName, postID, originalContent, originalImage) values (?,?,?,?,?)");
        $query->execute($postData);
    }
    function insertBannedOrgMember(array $bannedUser){
        $query = $this->conn->prepare("insert into bannedorgusers (userID, orgID) values (?,?)");
        $query->execute($bannedUser);
    }

    //update

    function updateBlockedMessageAppeal(array $appeal){
        $query = $this->conn->prepare("update blocks set appeal=?,appealMessage=? where target=?");
        $query->execute($appeal);
    }
    function updateResolveBlockMessage(array $resolve){
        $query = $this->conn->prepare("update blocks set resolved=?,resolution=? where messageID=?");
        $query->execute($resolve);
    }

    //select
    function getOrgIDByOrgPoster($userID){
        $query = $this->conn->prepare("select orgID from userorgposts where userID = ? ");
        $query->execute([$userID]);
        return $query->fetch();
    }
    function getOrgBanStatus($userID){
        $query = $this->conn->prepare("select orgID from bannedorgusers where userID = ? ");
        $query->execute([$userID]);
        return $query->fetch();
    }
    function getPostIDFromBlockID($blockID){
        $query = $this->conn->prepare("select target from blocks where messageID = ?");
        $query->execute([$blockID]);
        return $query->fetch();
    }
    function getBlockIDFromPostID($msgID){
        $query = $this->conn->prepare("select messageID from blocks where target = ?");
        $query->execute([$msgID]);
        return $query->fetch();
    }
    function getRecentBlockMessage(){
        $query = $this->conn->prepare("select * from blocks ORDER BY messageID DESC LIMIT 1");
        $query->execute();
        return $query->fetch();
    }
    function getBlockMessageFromPostID($postID){
        $query = $this->conn->prepare("select * from `blocks` where target=?");
        $query->execute([$postID]);
        return $query->fetch();
    }
    function getBlockMessages(){
        $query = $this->conn->prepare("select * from `blocks` where resolved=1");
        $query->execute();
        return $query->fetchAll();
    }
    function getResolvedBlockMessages(){
        $query = $this->conn->prepare("select * from `blocks` where resolved=0");
        $query->execute();
        return $query->fetchAll();
    }
    function getBlockedPosts(){
        $query = $this->conn->prepare("select * from `blockedposts`");
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

    //delete
    function deleteWhitelistWord($word){
        $query = $this->conn->prepare("delete from `whitelist` where word=?");
        $query->execute([$word]);
    }
    function deleteBlacklistWord($word){
        $query = $this->conn->prepare("delete from `blacklist` where word=?");
        $query->execute([$word]);
    }
    function deleteBlockPostReference($postID){
        $query = $this->conn->prepare("delete from `blockedposts` where postID=?");
        $query->execute([$postID]);
    }
    function deleteBlockMessageByID($msgID){
        $query = $this->conn->prepare("delete from `blocks` where messageID=?");
        $query->execute([$msgID]);
    }


}