<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Joel Mewton, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */ 

namespace raptor_mdwsvista;

class MdwsStringUtils {
    
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
    
    /**
     * phpseconds -> Sept 10@15:30
     */
    public static function convertPhpDateTimeToFunnyText($phpDateTime) 
    {
        return date('M d, Y@H:i', $phpDateTime);
    }
    
}
