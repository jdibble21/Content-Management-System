<?php


class BlockHandler
{
    function createBlockMessage($postID,$blockReason){

        $bl = new businessLogic();
        $resolution = "No resolution yet";
        $bl->insertBlock($postID,$blockReason,$resolution);
    }
}