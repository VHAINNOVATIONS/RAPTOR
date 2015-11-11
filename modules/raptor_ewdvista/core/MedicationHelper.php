<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * EWD Integration and VISTA collaboration: Joel Mewton, Rob Tweed
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

namespace raptor_ewdvista;

require_once 'EwdUtils.php';

/**
 * Helper for returning medication content
 *
 * @author Frank Font of SAN Business Consultants
 */
class MedicationHelper
{
    
    //Declare the field numbers
    private static $FLDNM_MEDNAME = 'name';
    private static $FLDNM_STATUS = 'status';
    private static $FLDNM_STARTDT = 'startDate';
    private static $FLDNM_DOCDT = 'dateDocumented';
    private static $FLDNM_COMMENT = 'comment';
    
    private function getFieldTextData($rawfield,$delminiter='^')
    {
        $strpos = strpos($rawfield,$delminiter);
        if($strpos == FALSE)
        {
            return NULL;
        }
        return trim(substr($rawfield,$strpos+1));
    }
    
    private static function findSubstringMatchInArray($needle, $haystackarray)
    {
        try
        {
            $cleanneedle = strtoupper(trim($needle));
            foreach($haystackarray as $check)
            {
                $cleancheck = strtoupper(trim($check));
                if(FALSE !== strpos($cleancheck,$cleanneedle))
                {
                    return $check;
                }
                if(FALSE !== strpos($cleanneedle,$cleancheck))
                {
                    return $check;
                }
            }
            return FALSE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * If atriskmeds is not null, it should be an array of medication names
     */
    public function getFormattedMedicationsDetail($rawresult_ar, $atriskmeds_ar=NULL)
    {
        try
        {
            if(!is_array($rawresult_ar))
            {
                throw new \Exception("Cannot format a non-array of data!");
            }
            if(!is_array($atriskmeds_ar))
            {
                $atriskmeds_ar = array();
                $checkatrisk = FALSE;
            } else {
                $checkatrisk = TRUE;
            }
            $displayMeds = array();
            $displayMedsLast=array();
            $atriskhits = array();
            foreach($rawresult_ar as $onemed)
            {
                if(is_array($onemed))
                {
                    $tempMeds = array();
                    $medname = $onemed[self::$FLDNM_MEDNAME];
                    $status = trim($onemed[self::$FLDNM_STATUS]);
                    $startdt = trim($onemed[self::$FLDNM_STARTDT]);
                    $docdt = trim($onemed[self::$FLDNM_DOCDT]);
                    $comment = trim($onemed[self::$FLDNM_COMMENT]);
                    $cleanstatus = strtoupper(trim($status));
                    $tempMeds['Med'] = $medname;
                    $tempMeds['Status'] = $status;
                    $tempMeds['StartDate'] = $startdt;
                    $tempMeds['DocDate'] = $docdt;
                    $tempMeds['Comment'] = $comment;
                    if(!$checkatrisk)
                    {
                        $tempMeds['AtRisk'] = '';
                        $tempMeds['warn'] = FALSE;
                        $displayMeds[] = $tempMeds;
                    } else {
                        $atriskmatchtext = self::findSubstringMatchInArray($medname, $atriskmeds_ar);
                        $atrisk = $atriskmatchtext !== FALSE;
                        $tempMeds['AtRisk'] = ($atrisk ? 'YES' : 'no');
                        $tempMeds['warn'] = ($atrisk && ($cleanstatus == '' 
                                || $cleanstatus == 'ACTIVE' 
                                || $cleanstatus == 'PENDING')); 
                        if($atrisk)
                        {
                            $atriskhits[$atriskmatchtext] = $atriskmatchtext;   //Set the key and value the same!
                            $displayMeds[] = $tempMeds;
                        } else {
                            $displayMedsLast[] = $tempMeds;
                        }
                    }
                }
            }
            $mergedMeds = array_merge($displayMeds, $displayMedsLast);
            $bundle = array('details' => $mergedMeds, 'atrisk_hits'=>$atriskhits);
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
