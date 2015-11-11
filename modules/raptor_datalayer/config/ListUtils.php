<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
        
        //TODO --- Switch to database instead of files!!!!!
        
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
        return $aCategory;
    }
    
}

