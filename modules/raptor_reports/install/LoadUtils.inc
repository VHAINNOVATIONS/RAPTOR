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


namespace raptor_reports;

/**
 * This class is for Ticket database schema content
 *
 * @author SAN
 */
class LoadUtils
{

    public static function getFileAsArray($sFilename)
    {
        $sPath = drupal_get_path('module', 'raptor_reports').'/install/'.$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $aLines;
    }    
    
    public static function getFileAsString($sFilename)
    {
        $sPath = drupal_get_path('module', 'raptor_reports').'/install/'.$sFilename;
        $text = file_get_contents($sPath);
        return $text;
    }    
}

