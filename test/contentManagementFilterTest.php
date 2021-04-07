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

    public function testCheckForBasicProfanity(){
        $testDetect = $this->cms->checkInputForProfanity('fuck this word');
        $this->assertEquals(False,$testDetect);
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