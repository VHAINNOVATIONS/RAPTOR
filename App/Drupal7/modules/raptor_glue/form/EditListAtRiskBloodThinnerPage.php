<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 


namespace raptor;

require_once ("AtRiskBasePage.php");

/**
 * This class returns the page to edit keyword list
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditListAtRiskBloodThinnerPage extends AtRiskBasePage
{
    function __construct()
    {
        parent::__construct('raptor_atrisk_bloodthinner');
    }
}
