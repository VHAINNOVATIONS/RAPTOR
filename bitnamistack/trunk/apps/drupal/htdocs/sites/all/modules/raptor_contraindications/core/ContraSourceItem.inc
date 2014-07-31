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

/**
 * The source reason for a contraindication.  
 * There can be several of these for any one contraindication.
 */
class ContraSourceItem {
    private $msRuleName;            //Unique name of the rule
    private $msCategory;            //Category of the contraindication eg Medicine, Procedure
    private $msSummaryMessage;      //Summary message to show the user.
    private $mbConfirmationRequired;//TRUE if user must acknowledge the contrandication, else FALSE.
    private $msMessage;             //Message to show the user.
    private $msExplanation;         //The rule, if any can be simply articulated
    
    public function __construct($sRuleName,$sCategory,$sSummaryMessage,$bConfirmationRequired,$sMessage,$sExplanation)
    {
        $this->msRuleName=$sRuleName;
        $this->msCategory=$sCategory;
        $this->msSummaryMessage=$sSummaryMessage;
        $this->mbConfirmationRequired=$bConfirmationRequired;
        $this->msMessage=$sMessage;
        $this->msExplanation=$sExplanation;
    }

    public function getRuleName()
    {
        return $this->msRuleName;
    }
    public function getCategory()
    {
        return $this->msCategory;
    }
    public function isConfirmationRequired()
    {
        return $this->mbConfirmationRequired;
    }
    public function getExplanation()
    {
        return $this->msExplanation;
    }
    public function getMessage()
    {
        return $this->msMessage;
    }    

    public function getSummaryMessage()
    {
        return $this->msSummaryMessage;
    }    

    private static function __isMatch($sV1,$sV2,$bV1NullIsWildcard=TRUE)
    {
        if($bV1NullIsWildcard)
        {
            if($sV1 === NULL)
            {
                return TRUE;
            }
        }
        return ($sV1 === $sV2);
    }

    /**
     * Determine if this instance substantially matches another instance.
     * @param type $oMatchCSI
     * @param type $bMatchNullIsWildcard
     * @return boolean TRUE if this is a match.
     */
    public function isMatch($oMatchCSI,$bMatchNullIsWildcard=true)
    {
        if($oMatchCSI===null)
        {
            die("Did NOT provide a valid instace of ContraSourceItem for isMatch!");
        }
        if(!ContraSourceItem::__isMatch($oMatchCSI->getCategory(), $this->getCategory(),$bMatchNullIsWildcard))
        {
            return false;
        }
        if(!ContraSourceItem::__isMatch($oMatchCSI->getRuleName(), $this->getRuleName(),$bMatchNullIsWildcard))
        {
            return false;
        }
        //We are here because we did not reject the match.
        return true;
    }
}
