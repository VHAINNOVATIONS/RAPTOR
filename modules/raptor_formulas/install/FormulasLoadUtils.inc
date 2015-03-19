<?php
/**
 * @file
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

defined('RAPTOR_FORMULAS_MODULE_PATH')
    or define('RAPTOR_FORMULAS_MODULE_PATH', drupal_get_path('module', 'raptor_formulas'));  

/**
 * File loading utilities
 *
 * @author Frank Font of SAN Business Consultants
 */
class FormulasLoadUtils
{

    public static function getFileAsArray($sFilename)
    {
        $sPath = RAPTOR_FORMULAS_MODULE_PATH.'/install/'.$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $aLines;
    }    
   
}

