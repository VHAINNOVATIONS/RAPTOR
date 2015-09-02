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
 * Helper for returning problems list content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ProblemsListHelper
{
    
    //Declare the field numbers
    private static $FLD_FACILITY = 1;
    private static $FLD_STATUS = 2;
    private static $FLD_PROVIDERNAR = 3;
    private static $FLD_ONSET_DT = 4;
    private static $FLD_MOD_DT = 5;
    private static $FLD_OBSERVER = 6;
    private static $FLD_NOTE_NAR = 7;
    private static $FLD_EXPOSE = 8;
    
    private function getUserDataFromArray($myarray,$offset)
    {
        if(!isset($myarray[$offset]))
        {
            return '';
        }
        
        //Get the field and return just the user data part
        $rawline = $myarray[$offset];
        return substr($rawline,2);  //Assume first two things are #^
    }
    
    public function getFormattedProblemsDetail($value_ar)
    {
        try
        {
            if(!is_array($value_ar))
            {
                $errmsg = "Expected an array with key 'value' for problems list but instead got $value_ar";
                error_log("$errmsg >>>".print_r($value_ar, TRUE));
                throw new \Exception($errmsg);
            }
            $formatted = array();
            foreach($value_ar as $oneblock)
            {
                foreach($oneblock as $key=>$onerow_ar)
                {
                    if($key != 'WP')
                    {
                        //Log this and continue
                        error_log("WARNING expected key for problem list to be WP but instead got $key >>>" 
                                . print_r($oneblock,TRUE));
                    }

                    $facility = $this->getUserDataFromArray($onerow_ar, self::$FLD_FACILITY);
                    $status = $this->getUserDataFromArray($onerow_ar, self::$FLD_STATUS);
                    $providernar = $this->getUserDataFromArray($onerow_ar, self::$FLD_PROVIDERNAR);
                    $notenar = trim($this->getUserDataFromArray($onerow_ar, self::$FLD_NOTE_NAR));
                    $onsetdate = $this->getUserDataFromArray($onerow_ar, self::$FLD_ONSET_DT);
                    $observername = $this->getUserDataFromArray($onerow_ar, self::$FLD_OBSERVER);
                    $exposures = $this->getUserDataFromArray($onerow_ar, self::$FLD_EXPOSE);
                            
                    if($notenar > '')
                    {
                        $snipfull = $notenar;
                    } else {
                        $snipfull = $providernar;
                    }
                    if(strlen($snipfull) < RAPTOR_DEFAULT_SNIPPET_LEN)
                    {
                        $snippet = $snipfull;
                    } else {
                        $snippet = substr($snipfull,0,RAPTOR_DEFAULT_SNIPPET_LEN) . '...'; 
                    }
                    $formatted[] = array(
                        'Title' => $providernar,
                        'OnsetDate' => $onsetdate,
                        'Snippet' => $snippet,
                        'Details' => array
                            (
                                'Type of Note' => 'Problem',
                                'Provider Narrative'=>$providernar, 
                                'Note Narrative'=>$notenar,
                                'Status'=>$status,
                                'Observer'=>$observername, 
                                'Comment'=>$exposures,
                                'Facility'=>$facility,
                            )
                        );
                }
            }
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
