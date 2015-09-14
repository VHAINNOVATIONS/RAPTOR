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
 * Helper for returning notes content
 *
 * @author Frank Font of SAN Business Consultants
 */
class NotesHelper
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
    
    public function getFormattedNotes($rawresult)
    {
        try
        {
error_log("LOOK notes stuff raw >>>".print_r($rawresult, TRUE));
            if(!is_array($rawresult))
            {
                $errmsg = "Expected an array for notes result but instead got $rawresult";
                error_log("$errmsg >>>".print_r($rawresult, TRUE));
                throw new \Exception($errmsg);
            }
            $formatted = array();
            foreach($rawresult as $onegroup)
            {
                $onenoteitem = $onegroup['WP'];
error_log("LOOK notes one group stuff >>>".print_r($onegroup, TRUE));

                //foreach($wp as $onenoteitem)
                //{
//error_log("LOOK notes blocks >>>".print_r($blocks, TRUE));
                    //foreach($blocks as $onenoteitem)
                    //{
//error_log("LOOK notes one item >>>".print_r($onenoteitem, TRUE));

                        if(!isset($onenoteitem[self::$FLD_TITLE]))
                        {
                            $localTitle = NULL;
                        } else {
                            $localTitle = $this->getFieldTextData($onenoteitem[self::$FLD_TITLE]);
                        }
                        if(!isset($onenoteitem[self::$FLD_DATETIMESTR]))
                        {
                            $datetimestr = NULL;
                        } else {
                            $datetimestr = $this->getFieldTextData($onenoteitem[self::$FLD_DATETIMESTR]);
                        }
                        if(strlen($localTitle) > RAPTOR_DEFAULT_SNIPPET_LEN)
                        {
                            $snippetText = substr($localTitle, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';
                        } else {
                            $snippetText = $localTitle;
                        }
                        if(!isset($onenoteitem[self::$FLD_AUTHOR]))
                        {
                            $authorName = NULL;
                        } else {
                            $authorName = $this->getFieldTextData($onenoteitem[self::$FLD_AUTHOR]);
                        }
                        if(!isset($onenoteitem[self::$FLD_FACILITY]))
                        {
                            $facility = NULL;
                        } else {
                            $facility = $this->getFieldTextData($onenoteitem[self::$FLD_FACILITY]);
                        }
                        if(!isset($onenoteitem[self::$FLD_DETAILS]))
                        {
                            $raw_notetext_ar = NULL;
                        } else {
                            $raw_notetext_ar = $onenoteitem[self::$FLD_DETAILS];
                        }
                        $clean_notetext_ar = array();
                        if(is_array($raw_notetext_ar))
                        {
                            foreach($raw_notetext_ar as $onerawnotetextrow)
                            {
                                $clean_notetext_ar[] = $this->getFieldTextData($onerawnotetextrow);
                            }
                        }
                        $notetext = implode("\n",$clean_notetext_ar);
                        $formatted[] = array(
                                'Type'=>$localTitle, 
                                'Date'=>$datetimestr,
                                'Snippet' => $snippetText,
                                'Details' => array(
                                        'Type of Note'=>$localTitle, 
                                        'Author'=>$authorName, 
                                        'Note Text'=>$notetext, 
                                        'Facility'=>$facility,
                                    )
                            );
                    //}
                //}
            }
            /*
            $formatted[] = array(
                'Type' => 'RAPTOR SAFETY CHECKLIST',
                'Date' => '07/16/2015 02:51 pm',
                'Snippet' => 'RAPTOR SAFETY CHECKLIST',
                'Details' => array
                    (
                        'Type of Note' => 'RAPTOR SAFETY CHECKLIST',
                        'Author' => NULL,
                        'Note Text' =>  'demo 123 LOCAL TITLE: RAPTOR SAFETY CHECKLIST yadayada',
                    )
                );
             */
error_log("LOOK notes final >>>".print_r($formatted, TRUE));
            
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
