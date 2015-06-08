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
 */ 


namespace raptor;

require_once 'EditListsBasePage.php';

/**
 * This class returns the page to edit keyword list
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditListAtRiskAllergyContrastPage extends EditListsBasePage
{
    function __construct()
    {
        parent::__construct('raptor_atrisk_allergy_contrast');
        $this->setListName('Allergy Contrast');
    }
}
