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
 *
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 */

namespace raptor_ci;

require_once ('SREDictionary.inc');

$loaded = module_load_include('inc','simplerulesengine_core','core/ASREContext');
if(!$loaded)
{
    drupal_set_message('Failed to load the ASREContext class', 'error');
}

/**
 * Context information for simple rules engine execution instance
 *
 * @author Frank Font of SAN Business Consultants
 */
class SREContext extends \simplerulesengine\ASREContext
{
    private $m_tablename_rule = 'raptor_contraindication_rule';
    private $m_tablename_measure = 'raptor_contraindication_measure';
    private $m_oDictionary = NULL;
    
    public function __construct()
    {
        $this->m_oDictionary = new \raptor_ci\SREDictionary($this);
    }
    
    /**
     * @return name of rule database table
     */
    public function getRuleTablename()
    {
        return $this->m_tablename_rule;
    }
    
    /**
     * @return name of flag database table
     */
    public function getMeasureTablename()
    {
        return $this->m_tablename_measure;
    }

    /**
     * The constructor requires an array with these keys.
     * @return array of names
     */
    public function getRequiredBaselineInfoKeys()
    {
        $aNames = array(
             'GENDER'
            ,'AGE'
            ,'WEIGHT_KG'
            ,'LATEST_EGFR'  //Could have been years ago just the latest number we have to work with
            ,'MIN_EGFR_10DAYS'
            ,'MIN_EGFR_15DAYS'
            ,'MIN_EGFR_30DAYS'
            ,'MIN_EGFR_45DAYS'
            ,'MIN_EGFR_60DAYS'
            ,'MIN_EGFR_90DAYS'
            );
        foreach($aNames as $sName)
        {
            $aRequiredKeys[$sName] = $sName; 
        }
        return $aRequiredKeys;
    }
    
    /**
     * The evaluator requires an array with these keys.
     * @return array of names
     */
    public function getRequiredCandidateDataKeys()
    {
        $aNames = array(
             'IS_DIAGNOSTIC_EXAM'
            ,'IS_IMG_GUIDED_EXAM'
            ,'IS_POSSIBLE_DUP_PROC'
            ,'PROC_NM','MODALITY'
            ,'GIVE_HYDRATION_ORAL'
            ,'GIVE_HYDRATION_IV'
            ,'GIVE_CONTRAST_ENTERIC'
            ,'GIVE_CONTRAST_IV'
            ,'GIVE_SEDATION_ORAL'
            ,'GIVE_SEDATION_IV'
            ,'GIVE_RADIOISOTOPE_ENTERIC'
            ,'GIVE_RADIOISOTOPE_IV'
            ,'IS_CLAUSTROPHOBIC'
            ,'HAS_ALLERGY'
            ,'CURRENT_ALLERGIES'
            ,'CURRENT_MEDS'
            ,'CURRENT_RADIOISOTOPES'
            ,'CURRENT_CONTRASTS'
            ,'KWL_RARE_CONTRAST'
            ,'KWL_RARE_RADIOISOTOPE'
            ,'KWL_BLOOD_THINNER'
            ,'KWL_CONTRAST_ALLERGY_INDICATOR'
            );
        foreach($aNames as $sName)
        {
            $aRequiredKeys[$sName] = $sName; 
        }
        return $aRequiredKeys;
    }
    
    /**
     * Return the dictionary instance that explains all of the coded inputs.
     */
    public function getDictionary()
    {
        return $this->m_oDictionary;
    }
}
