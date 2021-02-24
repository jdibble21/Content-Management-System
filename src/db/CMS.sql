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

DROP TABLE IF EXISTS `blockedposts`;
CREATE TABLE `'blockedposts`(
                                `postID` int(11) NOT NULL primary key,
                                `blockStatus` int(11) NOT NULL DEFAULT 1

);