drop database if exists cmsdata;
create database cmsdata;
USE cmsdata;

DROP TABLE IF EXISTS `blocks`;
CREATE TABLE `blocks` (
                          `messageID` int(11) NOT NULL auto_increment primary key,
                          `blockReason` varchar(255) NOT NULL,
                          `target` int(11) NOT NULL,
                          `resolved` tinyint(1) NOT NULL DEFAULT 1,
                          `resolution` varchar(255) NOT NULL,
                          `blockDate` datetime NOT NULL,
                          `appeal` int(11) NOT NULL DEFAULT 1,
                          `appealMessage` text NOT NULL
);
DROP TABLE IF EXISTS `blacklist`;
CREATE TABLE `blacklist` (
                             `wordID` int(11) NOT NULL auto_increment primary key,
                             `word` varchar(255) NOT NULL,
                             `dateAdded` datetime NOT NULL DEFAULT current_timestamp()
);
DROP TABLE IF EXISTS `whitelist`;
CREATE TABLE `whitelist` (
                             `wordID` int(11) NOT NULL auto_increment primary key,
                             `word` varchar(255) NOT NULL,
                             `dateAdded` datetime NOT NULL DEFAULT current_timestamp()
);
DROP TABLE IF EXISTS `blockedposts`;
CREATE TABLE `blockedposts`(
                                `postID` int(11) NOT NULL primary key,
                                `blockStatus` int(11) NOT NULL DEFAULT 1

);
DROP TABLE IF EXISTS `deletedposts`;
CREATE TABLE `deletedposts` (
                                `deleteID` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                `userID` varchar(255) NOT NULL,
                                `fullName` text NOT NULL,
                                `postID` int(11) NOT NULL,
                                `originalContent` text NOT NULL,
                                `originalImage` varchar(255) NOT NULL
);
DROP TABLE IF EXISTS `bannedorgusers`;
CREATE TABLE `bannedorgusers` (
                                  `orgID` int(11) NOT NULL,
                                  `userID` int(11) NOT NULL,
                                  UNIQUE KEY `uniqueness2` (`userID`,`orgID`),
                                  KEY `orgID` (`orgID`),
                                  KEY `userID` (`userID`)
);
DROP TABLE IF EXISTS `userorgposts`;
CREATE TABLE `userorgposts` (
                                `postID` INT NOT NULL ,
                                `userID` INT NOT NULL ,
                                `orgID` INT NOT NULL,
                                `approved` TINYINT NOT NULL DEFAULT '1'
);
DROP TABLE IF EXISTS `blacklist`;
CREATE TABLE `blacklist` (
                             `wordID` int(11) NOT NULL auto_increment primary key,
                             `word` varchar(255) NOT NULL,
                             `dateAdded` datetime NOT NULL DEFAULT current_timestamp()
);

DROP TABLE IF EXISTS `orgblacklist`;
CREATE TABLE `orgblacklist` (
                             `wordID` int(11) NOT NULL auto_increment primary key,
                             `word` varchar(255) NOT NULL,
                             `dateAdded` datetime NOT NULL DEFAULT current_timestamp(),
    						 `orgID` INT NOT NULL
);
DROP TABLE IF EXISTS `blockedimages`;
CREATE TABLE `blockedimages` (
                                 `postID` int(11) NOT NULL,
                                 `blockReason` varchar(255) NOT NULL,
                                 `blockValue` int(11) NOT NULL
);
