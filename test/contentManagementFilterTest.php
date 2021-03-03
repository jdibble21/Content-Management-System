<?php

use PHPUnit\Framework\TestCase;
require_once ("src\php\classes\ContentManagementLogic.php");
set_include_path('C:\xampp\php\PEAR');
class contentManagementFilterTest extends TestCase{

    private $cms;

    public function __construct(){
        parent::__construct();
        $this->cms = new ContentManagementLogic();
    }

    public function testRemoveBlockedPosts(){
        // Simulate Betterflye posts db data
        $keys = ['postID','postText','postImage','postDate','editedDate'];
        $values1 = ['0','some post text','no image','2020-02-21','2020-03-12'];
        $values2 = ['2','some other cool text','no image','2010-02-21','2019-03-12'];
        $postArray = [array_combine($keys,$values1),array_combine($keys,$values2)];
        $blockedPosts = $this->cms->removeBlockedPosts($postArray);
        $this->assertEquals("1",$blockedPosts[0]['blockStatus']);
    }

    public function testCheckForBasicProfanity(){
        $testDetect = $this->cms->checkInputForProfanity('fuck this word');
        $this->assertEquals(False,$testDetect);
    }

    public function testAddBlockMessageFromFilterBlock(){
        $postID = 999;
        $input = "shit input here";
        $filterValue = $this->cms->checkInputForProfanity($input);
        if($filterValue == False){
            $this->cms->createBlockMessage($postID,"TEST profanity detected from user input TEST");
        }
        $selectedBlockMsg = $this->cms->getBlockMessageByPostID($postID);
        $this->assertEquals("TEST profanity detected from user input TEST",$selectedBlockMsg['blockReason']);
    }

    public function testGetBlockMessages(){
        $this->cms->generateBlockList();
    }

    public function testAddWhitelistWord(){
        $this->cms->addWhitelistWord("allowfuckword");
        $testDetect = $this->cms->checkInputForProfanity("allowfuckword should allow this");
        $this->assertEquals(True,$testDetect);
    }
    public function testAddBlacklistWord(){
        $this->cms->addBlacklistWord("bannedwordtest");
        $testDetect = $this->cms->checkInputForProfanity("bannedwordtest block this");
        $this->assertEquals(False,$testDetect);
    }
    public function testRemoveWhitelistWord(){
        $this->cms->removeWhitelistWord("allowfuckword");
        $testDetect = $this->cms->checkInputForProfanity("allowfuckword should not allow this");
        $this->assertEquals(False,$testDetect);
    }
    public function testRemoveBlacklistWord(){
        $this->cms->removeBlacklistWord("bannedwordtest");
        $testDetect = $this->cms->checkInputForProfanity("bannedwordtest should allow this");
        $this->assertEquals(True,$testDetect);
    }
}