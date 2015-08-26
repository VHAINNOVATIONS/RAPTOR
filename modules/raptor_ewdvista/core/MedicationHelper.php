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
    private static $FLD_FACILITY = 1;
    private static $FLD_UNKNOWN2 = 2;
    private static $FLD_DATETIMESTR = 3;
    private static $FLD_TITLE = 4;
    private static $FLD_AUTHOR = 5;
    private static $FLD_DETAILS = 6;
    
    private function getFieldTextData($rawfield,$delminiter='^')
    {
        $strpos = strpos($rawfield,$delminiter);
        if($strpos == FALSE)
        {
            return NULL;
        }
        return trim(substr($rawfield,$strpos+1));
    }
    
    public function getFormattedMedicationsDetail($rawresult, $atriskmeds)
    {
        try
        {
            
            error_log("LOOK getFormattedMedicationDetail rawresult input>>>".print_r($rawresult,TRUE));
            error_log("LOOK getFormattedMedicationDetail atriskmeds input>>>".print_r($atriskmeds,TRUE));
            
            /*
            
//error_log("LOOK notes stuff raw >>>".print_r($rawresult, TRUE));
            if(!is_array($rawresult))
            {
                $errmsg = "Expected an array for notes result but instead got $rawresult";
                error_log("$errmsg >>>".print_r($rawresult, TRUE));
                throw new \Exception($errmsg);
            }
            $formatted = array();
            foreach($rawresult as $onegroup)
            {
//error_log("LOOK notes one group stuff >>>".print_r($onegroup, TRUE));
                foreach($onegroup as $blocks)
                {
//error_log("LOOK notes blocks >>>".print_r($blocks, TRUE));
                    foreach($blocks as $onenoteitem)
                    {
    //error_log("LOOK notes one item >>>".print_r($onenoteitem, TRUE));

                        $localTitle = $this->getFieldTextData($onenoteitem[self::$FLD_TITLE]);
                        $datetimestr = $this->getFieldTextData($onenoteitem[self::$FLD_DATETIMESTR]);
                        if(strlen($localTitle) > RAPTOR_DEFAULT_SNIPPET_LEN)
                        {
                            $snippetText = substr($localTitle, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';
                        } else {
                            $snippetText = $localTitle;
                        }
                        $authorName = $this->getFieldTextData($onenoteitem[self::$FLD_AUTHOR]);
                        $facility = $this->getFieldTextData($onenoteitem[self::$FLD_FACILITY]);
                        $raw_notetext_ar = $onenoteitem[self::$FLD_DETAILS];
                        $clean_notetext_ar = array();
                        foreach($raw_notetext_ar as $onerawnotetextrow)
                        {
                            $clean_notetext_ar[] = $this->getFieldTextData($onerawnotetextrow);
                        }
                        $notetext = implode("\n",$clean_notetext_ar);
                        $formatted[] = array(
                                            "Type"=>$localTitle, 
                                            "Date"=>$datetimestr,
                                            "Snippet" => $snippetText,
                                            "Details" => array('Type of Note'=>$localTitle, 
                                                            'Author'=>$authorName, 
                                                            'Note Text'=>$notetext, 
                                                            'Facility'=>$facility,
                                                )
                            );
                    }
                }
            }
             * 
             *     [details] => Array
        (
            [0] => Array
                (
                    [Med] => ASPIRIN TAB,EC
                    [Status] => Active
                    [AtRisk] => no
                    [warn] => 
                )

        )

    [atrisk_hits] => Array
        (
        )

             * 
 */
            $formatted = array();
            $formatted['details'] = array();
            $formatted['details'][] = array(
                    'Med' => 'ASPIRIN TAB,EC',
                    'Status' => 'Active',
                    'AtRisk' => 'no',
                    'warn' => NULL
                );
            $formatted['atrisk_hits'] = array();
            
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
