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

        global $base_url;
        
        $url = $base_url.'/raptor/editatriskallergycontrast';
        $name = 'Edit Allergy Contrast List';
        $description = 'These keywords are used to detect possible contrast allergies in patient.';
        $listname = 'Allergy Contrast';
        $reqprivs = array('EARM1'=>1);

        $this->setName($name);
        $this->setListName($listname);
        $this->setDescription($description);
        $this->setURL($url);
        $this->setRequiredPrivs($reqprivs);
    }
}
