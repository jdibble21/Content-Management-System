<?php
require_once ("FilterDataLayer.php");
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

    function getOrgBlacklist($orgID){
        return $this->dl->getOrgBlacklist($orgID);
    }

    function getOrgBlacklistSettingState($orgID){
        $blSetting = $this->dl->getOrgContentSettings($orgID);
        $blSetting = $blSetting['enableBlacklist'];
        if($blSetting == "0"){
            return true;
        }
        return false;
    }

    function checkForProfanityInWords($UserInput){
        $UserInput = strip_tags($UserInput);
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

    function checkOrgList($word, $orgID){
        $blacklist = $this->getOrgBlacklist($orgID);
        foreach($blacklist as $blacklistEntry){
            if(strtolower($word) == strtolower($blacklistEntry['word'])){
                return False;
            }
        }
        return True;
    }

    function checkForProfanityOrg($UserInput, $orgID){
        $blacklistEnabled = $this->getOrgBlacklistSettingState($orgID);
        $UserInput = strip_tags($UserInput);
        $isClean = $this->checkForProfanityInWords($UserInput);
        $input_array = explode(" ",$UserInput);
        if($blacklistEnabled){
            for ($i=0; $i < count($input_array); $i++){
                if($this->checkOrgList($input_array[$i],$orgID) == False){
                    $isClean = False;
                }
            }
        }
        return $isClean;
    }
}