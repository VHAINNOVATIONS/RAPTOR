<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

//Include the loader file from the module that integrates with the EHR system
$loaded_mainconfig = module_load_include('php', 'raptor_glue', 'core/config');
if($loaded_mainconfig == FALSE)
{
    error_log("FAILED: did NOT find raptor_glue main config file!  Assuming MDWSVISTA!  This can happen during setup but should never happen in prod system.");
    $loaded = module_load_include('php', 'raptor_mdwsvista', 'core/load_all_modules');
} else {
    //If we are not here, this means the server is being configured.
    $loaded = module_load_include('php', EHR_INT_MODULE_NAME, 'core/load_all_modules');
    if($loaded === FALSE)
    {
        throw new \Exception('Failed to load EHR integration module called "'.EHR_INT_MODULE_NAME.'"');
    }
}

