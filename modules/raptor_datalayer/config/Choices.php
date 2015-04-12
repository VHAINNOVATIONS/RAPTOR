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
 * Updated 20140516a
 */

namespace raptor;

if(!defined('__MYFOLDER_CHOICES__')) {
    define('__MYFOLDER_CHOICES__',dirname(__FILE__));
}

//require_once ("../userInfo.php");
require_once (dirname(__FILE__) . '/../core/data_listoptions.php');
require_once ('Choice.php');


/*
 * Configuration
 * @author vhapalfontf
 */
class raptor_datalayer_Choices 
{

    function __construct()
    {
        
    }

    /**
     * These are keywords in higher value groups from left to right.
     * Semicolon between groups, commas in side groups
     * @param text $sScoreTokens
     * @return array of arrays
     */
    private static function getScoreTokens($sScoreTokens)
    {
        $aRawGroup = explode(';',$sScoreTokens);
        $aCleanScoreGroup=array();
        foreach($aRawGroup as $sTokenGroup)
        {
            $aRawTokens = explode(',',$sTokenGroup);
            $aClean = array();
            foreach($aRawTokens as $sToken)
            {
                $sClean = trim($sToken);
                if($sClean > '' && $sClean[0] != '#')
                {
                    $aClean[]=$sClean;
                }
            }
            if(count($aClean)>0)
            {
                $aCleanScoreGroup[]=$aClean;
            }
        }
        return $aCleanScoreGroup;
    }

    /**
     * The score is non-zero if aScoreToken items are found in aCheckToken. 
     * Higher score means better match.  Zero means no match.
     * 
     * @param type $aCheckToken We will check this one
     * @param type $aScoreToken For matches from this one
     * @return int score 
     */
    private static function getProtocolMatchScore($aCheckToken,$aScoreTokenGroup,$sModality)
    {
        $score=0;
        //$sss='Score Stuff:<br>';
        foreach($aScoreTokenGroup as $nGroupPos => $aST)
        {   
            //$sss.=print_r($aST,true)."<br>";
            foreach($aST as $sST)
            {
                if(in_array($sST,$aCheckToken))
                {
                    $score += 20/($nGroupPos+1);
                }
            }
        } 
        //Factor in the modality too
        if(in_array($sModality,$aCheckToken))
        {
            //Position does not matter for modality
            $score += 20;
        }
        //if(FALSE && strpos($sss,'ANKLE'))
        //                    die($score."<hr>$sss<hr>CT:".print_r($aCheckToken,true)."<hr>ST:".print_r($aScoreTokenGroup,true)."<hr>M:".$sModality);

        return ceil($score);
    }
    
    

    /**
     * Return choices of all the users.
     * @param type $sDefaultChoiceOverrideID
     * @param type $sRole
     * @param type $oRemoveUser
     * @return \raptor_datalayer_Choice 
     */
    public static function getUserData($sDefaultChoiceOverrideID=NULL,$sDefaultaChoiceText=NULL,$sRemoveUserName=NULL)
    {
        
        $oUserInfo = new UserInfo();
        $aUserInfo=$oUserInfo->getAll();
        
        $aList=array();
        if($sDefaultChoiceOverrideID!==NULL)
        {
            $oC = new raptor_datalayer_Choice($sDefaultChoiceOverrideID,$sDefaultaChoiceText,"");
            $oC->bIsDefault=true;
            $aList[]=$oC;
        }
        foreach($aUserInfo as $oUserInfo)
        {
            if($oUserInfo->getUserName() != $sRemoveUserName)
            {
                $sLineLabel=$oUserInfo->getRealName();
                $sLineID=$oUserInfo->getUserName();
                $sCategory=$oUserInfo->getRolesText();
                $oC = new raptor_datalayer_Choice($sLineLabel,$sLineID,$sCategory);
                $oC->bIsDefault=($sDefaultChoiceOverrideID == $sLineID);
                $aList[]=$oC;
            }
        }
        
        return $aList;
    }
    
    
    /**
     * Load selection box protocol choices from a text file
     * @param type $sDefaultChoiceOverrideID
     * @param type $sDefaultaChoiceText
     * @param type $procName If not NULL, then is used to select suggested protocols
     * @return \raptor_datalayer_Choice 
     * @deprecated since version July 11 2014
     */
    public static function getProtocolData($sDefaultChoiceOverrideID=NULL,$sDefaultaChoiceText=NULL,$procName=NULL)
    {
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        drupal_set_message('CALLING DEPRECATED getProtocolData FUNCTION','error');
        
        $aProcName=explode(' ',strtoupper($procName));
        
        $sPath = __MYFOLDER_CHOICES__."/list-protocol.cfg";
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        //$sLOOK='LOOK:';
        $sCategory=NULL;
        $aList=array();
        $bFoundDefault=false;
        $aGM=array();           //Array of arrays so we preserve natural option order within groups.
        foreach($aLines as $nLine => $sLine)
        {
            if($sLine[0] != '#' && trim($sLine)!='')
            {
                if($sLine[0] == '[')
                {
                    $sCategory = substr($sLine,1,strlen($sLine)-2);
                } else {
                    $aChoice=explode('|',$sLine);
                    if(count($aChoice)<2)
                    {
                        die("Improperly configured choices file: $sPath<br>CHECK LINE:$nLine<br>TEXT:$sLine<br>RAW:".print_r($aLines,TRUE));
                    }
                    for($n=count($aChoice)+1;$n<=5;$n++)
                    {
                        $aChoice[]="#MISSING#";
                    }
                    $sLineID=$aChoice[0];
                    $sLineLabel=$aChoice[1];
                    $sLineModality=strtoupper(trim($aChoice[2]));
                    $sLineKeywords=strtoupper($aChoice[3]);
                    $oC = new raptor_datalayer_Choice($sLineLabel,$sLineID,$sCategory);
                    if($sDefaultChoiceOverrideID==$sLineID)
                    {
                        $oC->bIsDefault=true;
                        $bFoundDefault=true;
                    }
                    $aScoreToken= raptor_datalayer_Choices::getScoreTokens($sLineKeywords);
                    $oC->nScore = raptor_datalayer_Choices::getProtocolMatchScore($aProcName, $aScoreToken, $sLineModality);
                    $n=intval($oC->nScore); //To avoid strange warning messages
                    if(($n) > 0)
                    {
                        if(!array_key_exists($n,$aGM))
                        {
                            $aGM[$n]=array();
                        }
                        $aGroup=&$aGM[$n];
                        $aGroup[]=$oC;
                    } else {
                        $aList[] = $oC;
                    }
                }
            }
        }

        //Expand into one list.
        $aCombinedList=array();
        if(!$bFoundDefault)
        {
            if ($sDefaultChoiceOverrideID !== NULL)
            {
                if ($sDefaultaChoiceText == NULL)
                {
                    $sDefaultaChoiceText=$sDefaultChoiceOverrideID;
                }
                $oC = new raptor_datalayer_Choice($sDefaultaChoiceText,$sDefaultChoiceOverrideID,NULL,TRUE);
                $aCombinedList[] = $oC;
            }
        }
        krsort($aGM);  //Greater score first
        $nTopCount=0;
        foreach( $aGM as $aGroup )
        {
            foreach ($aGroup as $oChoice)
            {
                //$oChoice->sValue= $oChoice->nScore . ":{$nIndex}%" . $oChoice->sValue;
                $aCombinedList[]=$oChoice;
                $nTopCount+=1;
            }
        }
        if($nTopCount>0)
        {
            $oC2 = new raptor_datalayer_Choice('- ***Other Options*** -','-');
            $aCombinedList[]=$oC2;
        }
        foreach( $aList as $oChoice)
        {
           $aCombinedList[]=$oChoice;
        }
        
        return $aCombinedList;
    }
    

    /**
     * Get value from the list.
     * @param string $sPath location of the config file
     * @param string $sID to item match
     * @return string The text associated with the id 
     * @deprecated since version number
     */
    public static function getListItem($sPath,$sFindID,$sAltValue='')
    {
        $z="";
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($aLines as $nLine => $sLine)
        {
            if($sLine[0] != '[')
            {
                if(trim($sLine)!='' && $sLine[0] != '#' )
                {
                    $aChoice=explode('|',$sLine);
                    if(count($aChoice)!=2)
                    {
                        die("Improperly configured choices file: $sPath<br>CHECK LINE:$nLine<br>TEXT:$sLine<br>RAW:".print_r($aLines,TRUE));
                    }
                    /*
                    if($sFindID == $aChoice[0])
                    {
                        return $aChoice[1];
                    } 
                    $z.="|".$aChoice[0];
                     * 
                     */
                    if($sFindID == $aChoice[1])
                    {
                        return $aChoice[1];
                    } 
                    $z.="|".$aChoice[1];
                    
                }
            }
        }

        return $sAltValue; //.">>$sFindID<<$z>>";
    }
    
    /**
     * Load selection box choices from a text file
     * @param text $sPath Location of the file to load
     * @param text $sDefaultChoiceOverrideID
     * @return \raptor_datalayer_Choice 
     * @deprecated since version number
     */
    public static function getListData($sPath,$sDefaultChoiceOverrideID=NULL,$sDefaultaChoiceText=NULL)
    {
        
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        //$sConfigSectionName="[DEFAULT_SELECTION]";
        //$bInConfigSection=TRUE; //Assume we start here
        $sCategory=NULL;
        $aList=array();
        if ($sDefaultChoiceOverrideID !== NULL)
        {
            #TODO - refactor so we select in existing list if it exists there
            if ($sDefaultaChoiceText == NULL)
            {
                $sDefaultaChoiceText=$sDefaultChoiceOverrideID;
            }
            $oC = new raptor_datalayer_Choice($sDefaultaChoiceText,$sDefaultChoiceOverrideID,NULL,TRUE);
            $aList[] = $oC;            
        }

        foreach($aLines as $nLine => $sLine)
        {

            if($sLine[0] == '[')
            {
                //We hit the start of a NEW section.
                $sCategory = substr($sLine,1,strlen($sLine)-2);
            } else {
                //Blank or a comment?
                if(trim($sLine)!='' && $sLine[0] != '#' )
                {
                    $aChoice=explode('|',$sLine);
                    if(count($aChoice)!=2)
                    {
                        die("Improperly configured choices file: $sPath<br>CHECK LINE:$nLine<br>TEXT:$sLine<br>RAW:".print_r($aLines,TRUE));
                    }
                    //$oC = new raptor_datalayer_Choice($aChoice[1],$aChoice[0],$sCategory);
                    $oC = new raptor_datalayer_Choice($aChoice[1],$aChoice[1],$sCategory);
                    $aList[] = $oC;
                }
            }

        }

        return $aList;
    }

    public static function getListDataFromArray($aValues,$sDefaultChoiceOverrideID=NULL,$sDefaultaChoiceText=NULL)
    {
        $aList=array();
        if ($sDefaultChoiceOverrideID !== NULL)
        {
            #TODO - refactor so we select in existing list if it exists there
            if ($sDefaultaChoiceText == NULL)
            {
                $sDefaultaChoiceText=$sDefaultChoiceOverrideID;
            }
            $oC = new raptor_datalayer_Choice($sDefaultaChoiceText,$sDefaultChoiceOverrideID,NULL,TRUE);
            $aList[] = $oC;            
        }
        foreach($aValues as $sValue)
        {
            $oC = new raptor_datalayer_Choice($sValue,$sValue,'');
            $aList[] = $oC;
        }
        return $aList;
    }
    
    
    public static function getListItemFromArray($aValues,$sFindID,$sAltValue='')
    {
        foreach($aValues as $sValue)
        {
            if($sFindID == $sValue)
            {
                return $$sValue;
            } 
        }
        return $sAltValue; //.">>$sFindID<<$z>>";
    }
    
    
    public static function getEntericContrastData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }
        //TODO -- Cache the instance!!!!!!
        $oLO = new ListOptions();
        $aValues = $oLO->getContrastOptions('ENTERIC', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
    }
    
    public static function getIVContrastData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }
        //TODO -- Cache the instance!!!!!!
        $oLO = new ListOptions();
        $aValues = $oLO->getContrastOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
    }
    
    public static function getEntericRadioisotopeData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }

        //TODO -- Cache the instance!!!!!!
        $oLO = new ListOptions();
        $aValues = $oLO->getRadioisotopeOptions('ENTERIC', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
    }
    
    public static function getIVRadioisotopeData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }

        //TODO -- Cache the instance!!!!!!
        $oLO = new ListOptions();
        $aValues = $oLO->getRadioisotopeOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
    }

    public static function getEntericRadioisotopeMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-enteric-radioisotope.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getRadioisotopeOptions('ENTERIC', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
    }
    
    public static function getOralHydrationData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }

        //$sPath = __MYFOLDER_CHOICES__."/list-oral-hydration.cfg";
        //return raptor_datalayer_Choices::getListData($sPath,$sDefaultChoiceOverride);

        //TODO -- Cache the instance!!!!!!
        $oLO = new ListOptions();
        $aValues = $oLO->getHydrationOptions('ORAL', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
    }

    public static function getIVHydrationData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }

        //$sPath = __MYFOLDER_CHOICES__."/list-iv-hydration.cfg";
        //return raptor_datalayer_Choices::getListData($sPath,$sDefaultChoiceOverride);

        //TODO -- Cache the instance!!!!!!
        $oLO = new ListOptions();
        $aValues = $oLO->getHydrationOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
        
    }
    
    public static function getOralSedationData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }

        //$sPath = __MYFOLDER_CHOICES__."/list-oral-sedation.cfg";
        //return raptor_datalayer_Choices::getListData($sPath,$sDefaultChoiceOverride);
        
        $oLO = new ListOptions();
        $aValues = $oLO->getSedationOptions('ORAL', 'ANY');
        $aValues[''] = '';  //Add empty option
        $bFoundInList = FALSE;
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
        
    }

    public static function getIVSedationData($sDefaultChoiceOverride, &$bFoundInList, $modality_filter=NULL)
    {
        if($modality_filter == NULL)
        {
            $modality_filter = array();
        }

        //$sPath = __MYFOLDER_CHOICES__."/list-iv-sedation.cfg";
        //return raptor_datalayer_Choices::getListData($sPath,$sDefaultChoiceOverride);
        
        $oLO = new ListOptions();
        $aValues = $oLO->getSedationOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        if($sDefaultChoiceOverride != NULL)
        {
            $bFoundInList = in_array($sDefaultChoiceOverride, $aValues);
        }
        return raptor_datalayer_Choices::getListDataFromArray($aValues,$sDefaultChoiceOverride);
        
    }    

    public static function getIVRadioisotopeMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-iv-radioisotope.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getRadioisotopeOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
        
    }    
    
    public static function getEntericContrastMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-enteric-contrast.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getContrastOptions('ENTERIC', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
        
    }
    
    public static function getIVContrastMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-iv-contrast.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getContrastOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
        
    }
    
    public static function getOralHydrationMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-oral-hydration.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getHydrationOptions('ORAL', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
    }
    
    public static function getIVHydrationMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-iv-hydration.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getHydrationOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
    }
    
    public static function getOralSedationMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-oral-sedation.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getSedationOptions('ORAL', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
    }
    
    public static function getIVSedationMatch($sID)
    {
        //$sPath = __MYFOLDER_CHOICES__."/list-iv-sedation.cfg";
        //return raptor_datalayer_Choices::getListItem($sPath,$sID);
        
        $oLO = new ListOptions();   //TODO -- Cache the instance!!!!!!
        $aValues = $oLO->getSedationOptions('IV', 'ANY');
        $aValues[''] = '';  //Add empty option
        return raptor_datalayer_Choices::getListItemFromArray($aValues, $sID);
    }

    
    public static function getServicesData($sDefaultChoiceOverride=NULL)
    {
        return array(); //Return an empty result for now.
    }    
    
}

