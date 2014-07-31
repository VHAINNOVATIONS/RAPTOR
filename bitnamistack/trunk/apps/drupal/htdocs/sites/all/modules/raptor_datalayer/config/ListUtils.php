<?php

/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * @author Frank font
 * Updated 20120215
 */

namespace raptor;

if(!defined('__MYFOLDER_CONFIGLIST__')) {
    define('__MYFOLDER_CONFIGLIST__',dirname(__FILE__));
}


/**
 * Helpers for working with these config lists
 */
class ListUtils {
 
    /**
     * Return array of arrays 
     */
    public static function getCategorizedLists($sFilename,$sDefaultCategoryName=null,$nPartitions=2)
    {
        $sPath = __MYFOLDER_CONFIGLIST__."/".$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $aContent=array();
        $sCategory=$sDefaultCategoryName; 
        foreach($aLines as $nLine => $sLine)
        {
            if($sLine[0] != '#' && trim($sLine[0]) != '')
            {
                if($sLine[0] == '[')
                {
                    if(count($aContent)>0)
                    {
                        //Store the content for the completed category.
                        $aCategory[$sCategory] = $aContent;
                    }
                    
                    //The category name is inside the square brackets.
                    $sCategory=substr($sLine, 1, count($sLine[0])-2);
                    $aContent=array();  //Clear the content
                    
                } else {
                    $aChoice=explode('|',$sLine);
                    if(count($aChoice) < $nPartitions)
                    {
                        die("Improperly configured choices file: $sPath<br>CHECK LINE:$nLine<br>TEXT:$sLine<br>RAW:".print_r($aLines,TRUE));
                    }
                    $sItemName=$aChoice[0];
                    $aItem=array();
                    for($i=1;$i<$nPartitions;$i++)
                    {
                        $aItem[]=trim($aChoice[$i]);
                    }
                    $aContent[$sItemName]=$aItem;
                }
            }
        }
        if(count($aContent)>0)
        {
            //Store the content for the completed category.
            $aCategory[$sCategory] = $aContent;
        }        
//die(print_r($aCategory)."<hr>".print_r($aLines,true));
        return $aCategory;
    }
    
    /**
     * Get value from the list.
     * @param string $sPath location of the config file
     * @param string $sID to item match 
     * @return string The text associated with the id 
     */
    public static function getListItem($sFilename,$sFindID,$sAltValue=NULL,$nItemCol=1,$nIDCol=0)
    {
        if($sAltValue===NULL)
        {
            $sAltValue=$sFindID;
        }
        $sFindIDUC = trim(strtoupper($sFindID));
        $sPath = __MYFOLDER_CONFIGLIST__."/".$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($aLines as $nLine => $sLine)
        {
            if($sLine[0] != '[' && $sLine[0] != '#')
            {
                $aChoice=explode('|',$sLine);
                if(count($aChoice) < 2)
                {
                    die("Improperly configured choices file: $sPath<br>CHECK LINE:$nLine<br>TEXT:$sLine<br>RAW:".print_r($aLines,TRUE));
                }
                if($sFindIDUC == trim(strtoupper($aChoice[$nIDCol])))
                {
                    if($nItemCol=="ALL")
                    {
                        return $sLine;
                    } else {
                        return $aChoice[$nItemCol];
                    }
                } 
            }
        }

        return $sAltValue; //.">>$sFindID<<$z>>";
    }    
    
}

?>
