<?php
/**
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
class EditListAtRiskBloodThinnerPage extends EditListsBasePage
{
    function __construct()
    {
        parent::__construct('raptor_atrisk_bloodthinner');
        
        global $base_url;
        
        $url = $base_url.'/raptor/editatriskbloodthinner';
        $name = 'Edit Blood Thinner List';
        $description = 'These keywords are used to detect possible blood thinner use by patient.';
        $listname = 'Blood Thinner';
        $reqprivs = array('EARM1'=>1);

        $this->setName($name);
        $this->setListName($listname);
        $this->setDescription($description);
        $this->setURL($url);
        $this->setRequiredPrivs($reqprivs);
    }
}
