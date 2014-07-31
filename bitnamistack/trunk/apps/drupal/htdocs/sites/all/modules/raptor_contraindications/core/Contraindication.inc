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
 * Updated 20140625
 */


namespace raptor;

require_once('ContraSourceItem.inc');

/**
 * One instance of a contraindication.  A contraindication instance can be triggered by more than one contraindication source.  
 * Multiple sources are grouped together if they have same values in common:
 *  identical category name,
 *  identical summary message text,
 *  identical require confirmation value.  
 * For example, if there are two rules with different criteria that both return the text 'Contrast contraindicated because low eGFR' 
 * and both require confirmation and are in same category then both those sources would be associated with one Contraindication instance.
 */
class Contraindication {
    private $msID=NULL;                         //Unique ID for this contraindication
    private $maContraindicationSource=NULL;     //Array of specific cause of the contraindication    
    private $msSummaryMsgOverride=NULL;
    
    /**
     * Create an instance of a Contraindication.
     */
    public function __construct($aContraindicationSource,$sSummaryMsgOverride=NULL,$sID=NULL)
    {
        if(!is_array($aContraindicationSource) || get_class($aContraindicationSource[0]) !== 'raptor\ContraSourceItem')
        {
            $error = 'Must provide an array of ContraSourceItem objects to create a Contraindication instance! INSTEAD GOT>>>' . print_r($aContraindicationSource,TRUE);
            throw new \Exception($error);
        }
        $this->maContraindicationSource=$aContraindicationSource;
        $this->msSummaryMsgOverride=$sSummaryMsgOverride;
        if($sID == NULL)
        {
            //$extrapart = date("Y-m-d_H:i:s", time());
            $extrapart = count($aContraindicationSource);
            $this->msID=count($aContraindicationSource) . '_0_' . $aContraindicationSource[0]->getRuleName() . '__' . $extrapart;
        } else {
            $this->msID=$sID;
        }
    }
    
    /**
     * Each contraindication must have a unique ID so the application can track
     * views and acknowledgements from users. 
     * 
     * These IDs must map back to a mapping of the ID to the requirements that
     * caused the contraindication. 
     * 
     * @return text 
     */
    public function getUniqueID()
    {
        return $this->msID;
    }
    
    /**
     * Medicine, Procedure
     * @return text 
     */
    public function getRuleCategoryName()
    {
        return $this->maContraindicationSource[0]->getRuleCategoryName();
    }

    /**
     * The specific message
     * @return text 
     */
    public function getSummaryMessage()
    {
        if($this->msSummaryMsgOverride !== NULL)
        {
            return $this->msSummaryMsgOverride;
        }
        return $this->maContraindicationSource[0]->getSummaryMessage();
    }

    /**
     * The array of specific items at the root of the contraindication
     * @return array of ContraSourceItem instances 
     */
    public function getContraindicationSource()
    {
        return $this->maContraindicationSource;
    }
    
    /**
     * True or False
     * @return boolean 
     */
    public function isConfirmationRequired()
    {
        return $this->mbRequireAcknowledgement;
    }

    private static function __isMatch($sV1,$sV2,$bVINullIsWildcard=TRUE)
    {
        if($bVINullIsWildcard)
        {
            if($sV1 === NULL)
            {
                return TRUE;
            }
        }
        return ($sV1 === $sV2);
    }
    
    /**
     * Compares two instances of Contraindication
     * @param Contraindication $oCI1
     * @param Contraindication $oCI2 
     * @param boolean $bNullIsWildcard means any null is ignored for match purposes
     * @return boolean True if all checked attributes match, else false
     */
    public function isMatch($oMatchCI,$bMatchNullIsWildcard=TRUE)
    {
        if($oMatchCI===NULL)
        {
            die("Did NOT provide a valid instace of Contraindication for isMatch!");
        }
        
        if(!Contraindication::__isMatch($oMatchCI->getImpactedItemCategory(), $this->getImpactedItemCategory(),$bMatchNullIsWildcard))
        {
            return FALSE;
        }
        if(!Contraindication::__isMatch($oMatchCI->isConfirmationRequired(), $this->isConfirmationRequired(),$bMatchNullIsWildcard))
        {
            return FALSE;
        }
        if(!Contraindication::__isMatch($oMatchCI->getUniqueID(), $this->getUniqueID(),$bMatchNullIsWildcard))
        {
            return FALSE;
        }
        //We are here because we did not reject the match.
        return TRUE;
    }

    /**
     * Is considered a match if ANY of the CSI match the criteria.
     * @param type $oMatchCSI
     * @param type $bMatchNullIsWildcard
     * @return boolean TRUE if match, else FALSE.
     */
    public function isMatchOnCSI($oMatchCSI,$bMatchNullIsWildcard=true)
    {
        foreach($this->maContraindicationSource as $oCSI)
        {
            if($oCSI->isMatch($oMatchCSI,$bMatchNullIsWildcard))
            {
                //At least one of the CSI matched, so return true
                return TRUE;
            }
        }
        //We are here because we did NOT find any matches.
        return FALSE;
    }
}
