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
class EditListAtRiskRareRadioisotopePage extends EditListsBasePage
{
    private static $reqprivs = array('EARM1'=>1);
    function __construct()
    {
        parent::__construct(self::$reqprivs,'raptor_atrisk_rare_radioisotope');
        
        global $base_url;
        
        $url = $base_url.'/raptor/editatriskrareradioisotope';
        $name = 'Edit Rare or Controlled Radionuclide List';
        $description = 'These keywords are used to detect selection of a rare or controlled radionuclide which may require advanced procurement or special ordering process.';
        $listname = 'Rare Radionuclide';

        $this->setName($name);
        $this->setListName($listname);
        $this->setDescription($description);
        $this->setURL($url);
    }
}
