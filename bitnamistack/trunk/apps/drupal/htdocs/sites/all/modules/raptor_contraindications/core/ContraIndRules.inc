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
 * Created 20140725
 */

namespace raptor;

require_once('ContraIndRule.inc');

/**
 * All instances of a rules from the database.
 */
class ContraIndRules 
{
    private $mnSnapshotTime = NULL;
    private $maRules = NULL;
    private $maPatientInfoKeyMap = NULL; //Simple key value pairings
    
    /**
     * @param type $aRuleInfo associative array of one record
     */
    public function __construct($aPatientInfoKeyMap)
    {
        $this->maPatientInfoKeyMap = $aPatientInfoKeyMap;
    }
    
    public function evaluate($aCurrentKeyMap=NULL)
    {
        if($this->maRules == NULL)
        {
            $this->refresh();
        }
        
        //TODO - Refactor to allow rule dependencies on other rules
        
        $aAllVarMap = array_merge($this->maPatientInfoKeyMap,$aCurrentKeyMap);
        //drupal_set_message('TODO evaluate Rules using these vars ' . print_r($aAllVarMap, TRUE));
        
        $key = NULL;
        try
        {
            foreach($this->maRules as $key=>$oRule)
            {
                $aRuleInfo = $oRule->getAllInfo();
                $sExpression = $aRuleInfo['trigger_crit'];
                if(trim($sExpression) == '')
                {
                    drupal_set_message('There is NO trigger criteria for contraindication rule ' . $key,'warning');
                } else {
                    if(!isset($aCurrentKeyMap[$key]))
                    {
                        $oParser = new \raptor_ci\RuleExpressionParser($aAllVarMap);
                        $oRoot = $oParser->parse($sExpression);
                        $aAllVarMap[$key] = $oRoot->getValue();
                        //drupal_set_message('Computed Rule ' . $key . ' as ' . ContraIndUtility::translateThreeStateResultToText($aAllVarMap[$key]));
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Cannot evaluate rule '.$key.' because '.$ex->getMessage());
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
        $this->loadAllRules();
    }
    
    /**
     * Get all the rules, from cache if already available.
     * @return type array of objects
     */
    public function getAllRules()
    {
        if($this->maRules == NULL)
        {
            $this->refresh();
        }
        return $this->maRules;
    }
    
    /**
     * Load cache from database.
     */
    private function loadAllRules()
    {
        $this->mnSnapshotTime=time();
        $this->maRules = array();
        $result = db_select('raptor_contraindication_rule','r')
                ->fields('r')
                ->condition('active_yn',1,'=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $oRule = new \raptor\ContraIndRule($record);
            $sKey = $record['rule_nm'];
            $this->maRules[$sKey] = $oRule;
        }
        if(count($this->maRules) == 0)
        {
            $msg = 'No contraindication rules have been defined';
            drupal_set_message($msg,'warning');
            error_log($msg);
        }
    }
    
}
