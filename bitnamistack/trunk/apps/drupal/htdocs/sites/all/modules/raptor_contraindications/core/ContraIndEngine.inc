<?php

/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Frank Smith
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 * Updated 20140731
 */

namespace raptor;

require_once('ContraIndUtility.inc');
require_once('ContraIndFlags.inc');
require_once('ContraIndRules.inc');
require_once('ContraSourceItem.inc');
require_once('Contraindication.inc');
require_once('Contraindications.inc');
require_once(dirname(__FILE__) . '/../parser/FlagExpressionParser.inc');
require_once(dirname(__FILE__) . '/../parser/RuleExpressionParser.inc');

/**
 * Functions to determine what contraindications are relevant int the current context.
 *
 * @author vaphsfontf
 */
class ContraIndEngine 
{
    
    private $moFlags = NULL;
    private $moRules = NULL;
    private $maPatientInfo = NULL;      //Simple key map array
    private $maCandidateValues = NULL;  //Simple key map array
    
    /**
     *Initialize with all the core information this engine needs. 
     */        
    public function __construct($aPatientInfo)
    {
        ContraIndEngine::validatePatientInfo($aPatientInfo);
        $this->moFlags = new \raptor\ContraIndFlags($aPatientInfo);
        $this->moRules = new \raptor\ContraIndRules($aPatientInfo);
        $this->maPatientInfo=$aPatientInfo;
    }
    
    /**
     * Does not evaluate a value but checks the syntax and names of variables.
     * @param string $sExpression rule criteria
     * $param array $aDisallowedVariables names of variables we will not allow
     * @return array with validation details (isokay=TRUE/FALSE,errors=array)
     */
    public static function validateRuleExpression($sExpression,$aDisallowedVariables=NULL)
    {
        if($aDisallowedVariables == NULL)
        {
            $aDisallowedVariables = array();
        }
        $aResults = array();
        $aErrors = array();
        $bGood = TRUE;

        //Run the compiler
        try
        {
            $aVarMap = ContraIndEngine::getAllCurrentVariableNames();
            $oParser = new \raptor_ci\RuleExpressionParser($aVarMap);
            $aCompilerResults = $oParser->compile($sExpression);
            foreach($aCompilerResults['errors'] as $sErrorMsg)
            {
                $aErrors[] = $sErrorMsg;
            }
            foreach($aCompilerResults['dependencies'] as $sVarName)
            {
                if(in_array($sVarName,$aDisallowedVariables))
                {
                    $aErrors[] = 'Not allowed to reference variable "' . $sVarName . '" in the criteria';
                } else
                if(!in_array($sVarName, $aVarMap))
                {
                    $aErrors[] = 'Variable "' . $sVarName . '" does not exist';
                }
            }
        } catch (\Exception $ex) {
            $aErrors[] = $ex->getMessage();
        }
        
        //Organize the results and return them.
        $aResults['isokay'] = count($aErrors) == 0;
        $aResults['errors'] = $aErrors;
        return $aResults;
    }

    /**
     * Does not evaluate a value but checks the syntax and names of variables.
     * @param string $sExpression flag criteria
     * $param array $aDisallowedVariables names of variables we will not allow
     * @return array with validation details (isokay=TRUE/FALSE,errors=array)
     */
    public static function validateFlagExpression($sExpression,$aDisallowedVariables=NULL)
    {
        if($aDisallowedVariables == NULL)
        {
            $aDisallowedVariables = array();
        }
        $aResults = array();
        $aErrors = array();
        $bGood = TRUE;

        //Run the compiler
        try
        {
            $aVarMap = ContraIndEngine::getAllCurrentVariableNames(FALSE);
            $oParser = new \raptor_ci\FlagExpressionParser($aVarMap);
            $aCompilerResults = $oParser->compile($sExpression);
            foreach($aCompilerResults['errors'] as $sErrorMsg)
            {
                $aErrors[] = $sErrorMsg;
            }
            foreach($aCompilerResults['dependencies'] as $sVarName)
            {
                if(in_array($sVarName,$aDisallowedVariables))
                {
                    $aErrors[] = 'Not allowed to reference variable "' . $sVarName . '" in the criteria';
                } else
                if(!in_array($sVarName, $aVarMap))
                {
                    $aErrors[] = 'Variable "' . $sVarName . '" does not exist';
                }
            }
        } catch (\Exception $ex) {
            $aErrors[] = $ex->getMessage();
        }
        
        //Organize the results and return them.
        $aResults['isokay'] = count($aErrors) == 0;
        $aResults['errors'] = $aErrors;
        return $aResults;
    }

    /**
     * If a name is not in this array then it is not a valid variable for a criteria formula
     * @return array of names
     */
    public static function getAllCurrentVariableNames($bIncludeRuleNames=TRUE)
    {
        $a1 = ContraIndEngine::getRequiredPatientInfoKeys();
        $a2 = ContraIndEngine::getRequiredCandidateDataKeys();
        $a3 = ContraIndEngine::getActiveFlagNames();
        if(!$bIncludeRuleNames)
        {
            $aVarMap = array_merge($a1,$a2,$a3);
        } else {
            $a4 = ContraIndEngine::getActiveRuleNames();
            $aVarMap = array_merge($a1,$a2,$a3,$a4);
        }
        return $aVarMap;
    }
    
    /**
     * The constructore requires an array wit these keys.
     * @return array of names
     */
    public static function getRequiredPatientInfoKeys()
    {
        $aNames = array('GENDER','AGE','WEIGHT_KG'
            ,'MIN_EGFR_10DAYS','MIN_EGFR_15DAYS','MIN_EGFR_30DAYS','MIN_EGFR_45DAYS','MIN_EGFR_60DAYS','MIN_EGFR_90DAYS'
            );
        foreach($aNames as $sName)
        {
            $aRequiredKeys[$sName] = $sName; 
        }
        return $aRequiredKeys;
    }
    
    /**
     * The evaluator requires an array wit these keys.
     * @return array of names
     */
    public static function getRequiredCandidateDataKeys()
    {
        $aNames = array('IS_DIAGNOSTIC_EXAM','IS_IMG_GUIDED_EXAM','IS_POSSIBLE_DUP_PROC'
            ,'PROC_NM','MODALITY'
            ,'GIVE_HYDRATION_ORAL','GIVE_HYDRATION_IV'
            ,'GIVE_CONTRAST_ORAL','GIVE_CONTRAST_ENTERIC'
            ,'GIVE_SEDATION_ORAL','GIVE_SEDATION_IV'
            ,'GIVE_RADIOISOTOPE_ORAL','GIVE_RADIOISOTOPE_ENTERIC','IS_CLAUSTROPHOBIC'
            );
        foreach($aNames as $sName)
        {
            $aRequiredKeys[$sName] = $sName; 
        }
        return $aRequiredKeys;
    }
    
    /**
     * The names of all available flags.
     * @return array of names
     */
    public static function getActiveFlagNames()
    {
        $aNames = array();
        $result = db_select('raptor_contraindication_flag','f')
                ->fields('f',array('flag_nm'))
                ->condition('active_yn',1,'=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $aNames[] = $record['flag_nm'];
        }
        return $aNames;
    }

    /**
     * The names of all available rules.
     * @return array of names
     */
    public static function getActiveRuleNames()
    {
        $aNames = array();
        $result = db_select('raptor_contraindication_rule','f')
                ->fields('f',array('rule_nm'))
                ->condition('active_yn',1,'=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $aNames[] = $record['rule_nm'];
        }
        return $aNames;
    }

    /**
     * Check for required keys
     * @param key/value array $aPatientInfo 
     * @throws Exception if missing a required key
     */
    private static function validatePatientInfo($aPatientInfo)
    {
        $aRequiredKeys = ContraIndEngine::getRequiredPatientInfoKeys();
        foreach($aRequiredKeys as $key)
        {
            if(!array_key_exists($key, $aPatientInfo))
            {
                throw new \Exception('Missing required patient information value for [' . $key . '] ONLY GOT>>>' . print_r($aPatientInfo, TRUE));
            }
        }
    }

    /**
     * Check for required keys
     * @param key/value array $aPatientInfo 
     * @throws Exception if missing a required key
     */
    private static function validateCandidateValues($aCandidateValues)
    {
        $aRequiredKeys = ContraIndEngine::getRequiredCandidateDataKeys();
        foreach($aRequiredKeys as $key)
        {
            if(!array_key_exists($key, $aCandidateValues))
            {
                throw new \Exception('Missing required exam information value for [' . $key . '] ONLY GOT>>>' . print_r($aCandidateValues, TRUE));
            }
        }
    }

    /**
     * Compute all the contraindications when this is called.
     * @param array $aCandidateValues is an array of exam specific values
     * @return instance of Contraindications 
     */
    public function getContraindications($aCandidateValues=NULL)
    {
        ContraIndEngine::validateCandidateValues($aCandidateValues);
        $this->maCandidateValues=$aCandidateValues;
        
        $aAllVarMap = NULL;
        $aAllVarMap = NULL;
        try
        {
            $aAllVarMap = $this->moFlags->evaluate($aCandidateValues);
        } catch (\Exception $ex) {
            throw new \Exception('Failed evaluation of flags because ' . $ex->getMessage());
        }
        
        try
        {
            $aAllVarMap = $this->moRules->evaluate($aAllVarMap);
        } catch (\Exception $ex) {
            throw new \Exception('Failed evaluation of rules because ' . $ex->getMessage());
        }

        try
        {
            $aContraindications = array();
            $aRules = $this->moRules->getAllRules();
            foreach($aRules as $key=>$oRule)
            {
                if(array_key_exists($key,$aAllVarMap))
                {
                    $bResult = $aAllVarMap[$key];
                    if($bResult == TRUE)
                    {
                        $aRule = $oRule->getAllInfo();
                        $sRuleName = $aRule['rule_nm'];
                        $sCategory = $aRule['category_nm'];
                        $sSummaryMessage = $aRule['summary_msg_tx'];
                        $sMessage = $aRule['msg_tx'];
                        $sExplanation = $aRule['explanation'];
                        $bConfirmationRequired = ($aRule['req_ack_yn'] == 1);
                        $oCS1 = new \raptor\ContraSourceItem($sRuleName,$sCategory,$sSummaryMessage,$bConfirmationRequired,$sMessage,$sExplanation); 
                        $aContraindicationSource = array();
                        $aContraindicationSource[] = $oCS1;
                        $oCI1 = new \raptor\Contraindication($aContraindicationSource); //,'Placeholder test contraindication');
                        $aContraindications[] = $oCI1;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Failed creation of contraindication results because ' . $ex->getMessage());
        }
        
        //Now return all the contraindications we found.
        return new Contraindications($aContraindications);
    }
}

?>
