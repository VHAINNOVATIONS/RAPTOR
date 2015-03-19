<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor;

class StringUtils {
    
    public static function concat($string1, $string2) {
        return $string1.$string2;
    }
    
    public static function joinStrings($stringAry, $delimiter) {
        if (!isset($stringAry)) {
            return '';
        }
        if (!is_array($stringAry)) {
            throw new \Exception('Invalid argument. Must pass array >>'.print_r($stringAry,TRUE));
        }
        
        $formatted = '';
        $lineCount = count($stringAry);
        for ($i = 0; $i < $lineCount; $i++) {
            if ($i == 0) { // don't insert | for new line first time through'
                $formatted = $stringAry[$i];
            }
            else {
                $formatted = $formatted.$delimiter.$stringAry[$i];
            }
        }
        
        return $formatted;
    } 
    
    
    public static function convertPhpDateTimeToISO($phpDateTime) {
        return date('Ymd.Hi', $phpDateTime);
    }
}
