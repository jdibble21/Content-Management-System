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
    public function testBlockPost(){

    }
}