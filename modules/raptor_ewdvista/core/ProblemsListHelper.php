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
    private static $FLD_ID = 0;
    private static $FLD_STATUS = 1;
    private static $FLD_PROVIDERNAR = 2;
    private static $FLD_ONSET_DT = 4;
    private static $FLD_MOD_DT = 5;
    private static $FLD_NOTE_NAR = 6;
    private static $FLD_OBSERVER = 11;  //5;
    private static $FLD_EXPOSE = 7;
    
    public function getFormattedProblemsDetail($rawresult)
    {
        try
        {
error_log("LOOK problems input stuff raw >>>".print_r($rawresult, TRUE));
            if(!is_array($rawresult) || !isset($rawresult['value']))
            {
                $errmsg = "Expected an array with key 'value' for problems list but instead got $rawresult";
                error_log("$errmsg >>>".print_r($rawresult, TRUE));
                throw new \Exception($errmsg);
            }
            $formatted = array();
            $value_ar = $rawresult['value'];
            $input_raw_rowcount = count($value_ar);
            if($input_raw_rowcount>0)
            {
                $actualdatarowcount = $input_raw_rowcount-1;
                $expecteddatacount = intval($value_ar[0]);  //Declaration is in the array
                if($expecteddatacount != $actualdatarowcount)
                {
                    $errmsg = "Did NOT get expected number of problem rows: Expected $expecteddatacount but got {$actualdatarowcount}";
                    error_log("ERROR >>> $errmsg Input data =>>>".print_r($value_ar, TRUE));
                    throw new \Exception($errmsg);
                }
                for($i=1;$i<$input_raw_rowcount;$i++)
                {
                    $onerawrow = $value_ar[$i];
                    $onerow_ar = explode('^', $onerawrow);
                    $providernar = $onerow_ar[self::$FLD_PROVIDERNAR];
                    $notenar = trim($onerow_ar[self::$FLD_NOTE_NAR]);
                    $onsetdate = EwdUtils::convertVistaDateToYYYYMMDD($onerow_ar[self::$FLD_ONSET_DT]);
                    $observerraw = $onerow_ar[self::$FLD_OBSERVER];
                    $observerparts = explode(';',$observerraw);
                    if(count($observerparts) == 2)
                    {
                        $observername = $observerparts[1];
                    } else {
                        $observername = 'None found';
                    }
                            
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
                                'Status'=>$onerow_ar[self::$FLD_STATUS], 
                                'Observer'=>$observername, 
                                'Comment'=>'',  //MISSING??????????? 
                                'Facility'=>'', //MISSING???????????
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
