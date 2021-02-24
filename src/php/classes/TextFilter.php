<?php
require_once ("src\php\classes\FilterDataLayer.php");
class TextFilter
{
    private $dl;

    public function __construct(){
        $this->dl = new FilterDataLayer();
    }

    function getWhitelist(){
        return $this->dl->getWhitelist();
    }

    function getBlacklist(){
        return $this->dl->getBlacklist();
    }

    function checkForProfanityInWords($UserInput){
        $input_array = explode(" ",$UserInput);
        $isClean = True;

        for ($i=0; $i < count($input_array); $i++){
            $formatted_word = preg_replace("/[,.]/ui", "", $input_array[$i]);
            $pattern = "/f(.|[\*ua]+)c*k|b[^uao]+tch|n(.|i+)g{2,}.+[^(ets?)]|s(h[\!\*i])+t+|f@g+/i";
            if(preg_match($pattern, $formatted_word)) {
                $isClean = False;
            }
            if($this->checkBlacklist($formatted_word) == False){
                $isClean = False;
            }
            if($this->checkWhitelist($formatted_word) == True){
                $isClean = True;
            }
        }
        return $isClean;
    }

    function checkWhitelist($word){
        $whitelist = $this->getWhitelist();
        foreach($whitelist as $whitelistEntry){
            if(strtolower($word) == strtolower($whitelistEntry['word'])){
                return True;
            }
        }
        return False;
    }

    function checkBlacklist($word){
        $blacklist = $this->getBlacklist();
        foreach($blacklist as $blacklistEntry){
            if(strtolower($word) == strtolower($blacklistEntry['word'])){
                return False;
            }
        }
        return True;
    }

    function addToBlacklist($userInput) {

    }

    function removeFromBlacklist($userInput) {

    }

    function addToWhiteList($userInput) {

    }

    function removeFromWhiteList($userInput) {

    }
}