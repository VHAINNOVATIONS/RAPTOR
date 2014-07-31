<?php

/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 * Created 20140729
 */

namespace raptor;

require_once('ContraIndFlag.inc');

/**
 * All instances of a rules from the database.
 */
class ContraIndFlags
{
    private $mnSnapshotTime = NULL;
    private $maFlags = NULL;
    private $maPatientInfoKeyMap = NULL; //Simple key value pairings

    /**
     * @param type $aRuleInfo associative array of one record
     */
    public function __construct($aPatientInfoKeyMap)
    {
        $this->maPatientInfoKeyMap = $aPatientInfoKeyMap;
    }
    

    /**
     * Compute values for all the flags and return concatenated with existing key map data
     * @param type $aCurrentKeyMap
     * @return array key value pairs
     */
    public function evaluate($aCurrentKeyMap=NULL)
    {
        if($this->maFlags == NULL)
        {
            $this->refresh();
        }
        
        $aTryLaterStack = array();  //Try to compute these later.
        $aAllActiveFlagNames = ContraIndEngine::getActiveFlagNames();
        
        $aAllVarMap = array_merge($this->maPatientInfoKeyMap,$aCurrentKeyMap);
        $key = NULL;
        $aErrors = array();
        //Take an initial linear pass through the flags.
        try
        {
            foreach($this->maFlags as $key=>$oFlag)
            {
                $aFlagInfo = $oFlag->getAllInfo();
                $sExpression = $aFlagInfo['criteria_tx'];
                if(!isset($aCurrentKeyMap[$key]))
                {
                    $oParser = new \raptor_ci\FlagExpressionParser($aAllVarMap);
                    
                    $aCompiledResult = $oParser->compile($sExpression);
                    //array('haserrors'=>(count($aErrors) > 0),'dependencies'=>$aDependencies, 'errors'=>$aErrors, 'treeroot'=>$oRoot);
                    if($aCompiledResult['haserrors'])
                    {
                       $aErrors[$key] = $aCompiledResult['errors'];
                    } else {
                        //TODO --- check the dependencies!!!!
                        $aDependencies = $aCompiledResult['dependencies'];
                        
                        $aMissingKeys = $oParser->getMissingKeys($aAllVarMap, $aDependencies);
                        if(count($aMissingKeys)>0)
                        {
                            //TODO compute this later!!!!!!
                            $aTryLaterStack[] = $oFlag;
                            drupal_set_message('LOOK FAILED dependencies of Flag ' . $key . ' MISSING=' . print_r($aMissingKeys,TRUE), 'warning');
                        } else {
                            $oRoot = $aCompiledResult['treeroot'];
                            $aAllVarMap[$key] = $oRoot->getValue();
                            drupal_set_message('LOOK Computed Flag ' . $key . ' as ' . ContraIndUtility::translateThreeStateResultToText($aAllVarMap[$key]));
                        }
                    }
                    //drupal_set_message('Computed Flag ' . $key . ' as ' . ContraIndUtility::translateThreeStateResultToText($aAllVarMap[$key]));
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Cannot evaluate flag '.$key.' because '.$ex->getMessage());
        }
        
        if(count($aErrors) > 0)
        {
            //Abort now before going forward anymore.
            $msg = 'Found ' . count($aErrors) . ' flag evaluation errors:<ol>';
            foreach($aErrors as $key=>$value)
            {
                $msg .= '<li>Flag "' . $key . '" fails because ' . $value;
            }
            throw new \Exception($msg);
        }
        
        //Now try to process the flags we put off for later.
        try
        {
            while(count($aTryLaterStack) > 0)
            {
                $oFlag = array_pop($aTryLaterStack);
                drupal_set_message('LOOK later Flag ' . print_r($oFlag,TRUE));
            }
        } catch (\Exception $ex) {
            throw new \Exception('Cannot evaluate flag '.$key.' because '.$ex->getMessage());
        }
        return $aAllVarMap;
    }
    
    public function getSnapshotTime()
    {
        return $this->mnSnapshotTime;
    }
    
    /**
     * Refresh set of rules from the database.
     */
    public function refresh()
    {
        $this->loadAllFlags();
    }
    
    /**
     * Get all the rules, from cache if already available.
     * @return type array of objects
     */
    public function getAllFlags()
    {
        if($this->maFlags == NULL)
        {
            $this->refresh();
        }
        return $this->maFlags;
    }
    
    /**
     * Load cache from database.
     */
    private function loadAllFlags()
    {
        $this->mnSnapshotTime=time();
        $this->maFlags = array();
        $result = db_select('raptor_contraindication_flag','r')
                ->fields('r')
                ->condition('active_yn',1,'=')
                ->orderBy('flag_nm','DESC')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $oRule = new \raptor\ContraIndFlag($record);
            $sKey = $record['flag_nm'];
            $this->maFlags[$sKey] = $oRule;
        }
        if(count($this->maFlags) == 0)
        {
            $msg = 'No contraindication flags have been defined';
            drupal_set_message($msg,'warning');
            error_log($msg);
        }
    }
    
}
