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


namespace raptor_sched;

/**
 * This class is for Ticket database schema content
 *
 * @author FrankWin7VM
 */
class LoadUtils
{

    public static function getFileAsArray($sFilename)
    {
        $sPath = drupal_get_path('module', 'raptor_scheduling').'/install/'.$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $aLines;
    }    
    
}

