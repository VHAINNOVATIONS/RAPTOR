<?php
/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Frank Smith
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * @author Frank Font
 * Updated 20120307a 
 */

//We define constants so typos are easier to catch in code.
//The defines must ONLY contain the LABEL in the config file.
//The actual text to display in RAPTOR is from the config file, not the define.

//GENERAL
define('__MAX_GREEN_EGFR',"MAX_GREEN_EGFR");
define('__MAX_YELLOW_EGFR',"MAX_YELLOW_EGFR");
define('__RAPTOR_URL',"RAPTOR_URL");
define('__PROTOCOL_LIBRARY_URL',"PROTOCOL_LIBRARY_URL");
define('__PROTOCOL_LIBRARY_FETCHER',"PROTOCOL_LIBRARY_FETCHER");

define('__NOTES_HISTORY_DAYS',"NOTES_HISTORY_DAYS");

require_once('choices.php');

if(!defined('__MYFOLDER_CONFIG__')) {
    define('__MYFOLDER_CONFIG__',dirname(__FILE__));
}


/**
 * Functions to help with configuration.
 */
class ConfigHelper {

    
    /**
     * Perform a lookup to return the site configurable value
     * @param string $sName 
     */
    public static function getValue($sName,$sResultIfFail="__DIE__",$sPath=null)
    {
        if($sPath==null)
        {
            //Figure out where to look for the value.
            $sPath = __MYFOLDER_CONFIG__."/general.cfg";
            $sFound=ConfigHelper::getValue($sName,"__MISSING__",$sPath);
            if($sFound!=="__MISSING__")
            {
                return $sFound;
            }
        } else {
            //Look through the file.
            $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            //$s="";
            foreach($aLines as $sLine)
            {
                if($sLine[0] != '#' && trim($sLine)!='')
                {
                    $nPos=strpos($sLine,"=");
                    if($nPos !== FALSE && strlen($sLine)>$nPos)
                    {
                        $aLine=explode('=',$sLine);
                        if($aLine[0]==$sName)
                        {
                            return substr($sLine,$nPos+1); //$aLine[1];
                        }
                    }
                    //$s.="<li>[".$aLine[0]."]vs[".$sName."]";
                }
            }
        }
        //If we are here, then we did not find the value.
        if($sResultIfFail=="__DIE__")
        {
            $sMsg="FAILED becauase did NOT find config entry named '$sName' in $sPath";
            error_log($sMsg);
            die($sMsg);
        }
        $sMsg="Looking elsewhere because did NOT find config entry named '$sName' in $sPath";
        error_log($sMsg);
        return $sResultIfFail;
    }   
    
    private static function showValue($aValue,$nIndex=null,$blank="")
    {
        if ($nIndex == null)
        {
            $sValue=trim($aValue);
        } else {
            if(!isset($aValue))
            {
                return $blank;
            } else
            if(!isset($aValue[$nIndex]))
            {
                return $blank;
            }
            
            $sValue=trim($aValue[$nIndex]);
        }
        if(!isset($sValue) || substr($sValue,0,1) == "#" )
        {
            if(isset($sValue) && $blank === NULL)
            {
                return $sValue;
            }
            return $blank; //  "11".$sValue."@".$nIndex.">>>".print_r($aValue,true);
        }
        return $sValue;
    }
    
    /**
     * Get array of all the configured protocols 
     */
    public static function getAllProtocolInfo()
    {
        $sPath = __MYFOLDER_CONFIG__."/list-protocol.cfg";
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $aList=array();
        $sCategory="ALL";
        foreach($aLines as $nLine => $sLine)
        {
            if($sLine[0] != '#' && trim($sLine)!='')
            {
                if($sLine[0] == '[')
                {
                    $sCategory = substr($sLine,1,strlen($sLine)-2);
                } else {
                    //$aLine = array_merge(array($sCategory),explode('|',$sLine));
                    $aLine = explode('|',$sLine);
                
                    if(count($aLine)<2)
                    {
                        die("Wrong format in line $nLine of $sPath<br>$sLine");
                    }
                    $sProtcolID=$aLine[0];
                    if(count(trim($sProtcolID))==0)
                    {
                        die("Missing ID value ($sProtocolID): Wrong format in line $nLine of $sPath<br>$sLine");
                    } else {
                        $sProtcolName=$aLine[1];
                        $sModality=ConfigHelper::showValue($aLine,2);
                        $sRawKeywords=ConfigHelper::showValue($aLine,3,NULL);
                        $sCTDI=ConfigHelper::showValue($aLine,4);
                        $sDLP=ConfigHelper::showValue($aLine,5);
                        $sRTD=ConfigHelper::showValue($aLine,6);
                        $sEED=ConfigHelper::showValue($aLine,7);
                        
                        $aList[] = new ProcolItem($sCategory,$sProtcolID,$sProtcolName,$sModality,$sRawKeywords,$sCTDI,$sDLP, $sRTD, $sEED);
                    }
                }
            }
        }
        
        return $aList;
    }
    
    
}


class ProcolItem
{
    public $sGrouping;
    public $sID;
    public $sName;
    public $sRawModality;
    public $sRawKeywords;
    public $sCTDI;
    public $sDLP;
    public $sRTD;   //Radiotracer Dose
    public $sEED;   //Estimated Effective Dose
    
    public function __construct($sGrouping,$sID,$sName,$sRawModality,$sRawKeywords,$sCTDI,$sDLP,$sRTD,$sEED)
    {
        $this->sGrouping=$sGrouping;
        $this->sID=$sID;
        $this->sName=$sName;
        $this->sRawModality=$sRawModality;
        $this->sRawKeywords=$sRawKeywords;
        $this->sCTDI=$sCTDI;
        $this->sDLP=$sDLP;
        $this->sRTD=$sRTD;
        $this->sEED=$sEED;
        
        //print_r(var_dump($this));
    }
}


?>
