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
 * Helper for returning Pathology Report content
 *
 * @author Frank Font of SAN Business Consultants
 */
class RadiologyReportHelper
{
    //Declare the field numbers
    private static $FLD_FACILITY = 1;
    private static $FLD_REPORTDATE = 2;
    private static $FLD_TITLE = 3;
    private static $FLD_STATUS = 4;
    //private static $FLD_ACCESSION_NUM = 0;
    private static $FLD_CASE_NUM = 5;
    private static $FLD_EXAM = 6;
    private static $FLD_REPORT_ID = 9;
    
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

    private function getUserDataFromArrayOfArray2($myarray,$offset,$delimiter="<br />")
    {
        if(!isset($myarray[$offset]))
        {
            return '';
        }
        $arrays = $myarray[$offset];
        if(!is_array($arrays))
        {
            return '';
        }
        $cleanrows = array();
        foreach($arrays as $rows)
        {
            foreach($rows as $rawline)
            {
                if($rawline > '')
                {
                    $cleanrows[] = substr($rawline,2);  //Assume first two things are #^
                }
            }
        }
        return implode($delimiter, $cleanrows);
    }
    
    public function getFormattedRadiologyReportHelperDetail($rawresult_ar)
    {
        try
        {
            if(!is_array($rawresult_ar))
            {
                throw new \Exception("Cannot format a non-array of data!");
            }
            
            $bundle = array();
            foreach($rawresult_ar as $oneWP)
            {
                foreach($oneWP as $onereport)
                {
                    $facility = $this->getUserDataFromArray($onereport, self::$FLD_FACILITY);
                    $title = $this->getUserDataFromArray($onereport, self::$FLD_TITLE);
                    $reportdate = $this->getUserDataFromArray($onereport, self::$FLD_REPORTDATE);
                    $status = $this->getUserDataFromArray($onereport, self::$FLD_STATUS);
                    $reportid = $this->getUserDataFromArray($onereport, self::$FLD_REPORT_ID);
                    $casenum = $this->getUserDataFromArray($onereport, self::$FLD_CASE_NUM);
                    $detail_tx = trim($this->getUserDataFromArrayOfArray2($onereport, self::$FLD_EXAM));
                    $snippet = substr($title, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';

                    $accession = '';    //TODO $this->getUserDataFromArray($onereport, self::$FLD_SPECIMEN_ASCESSION_NUM);
                    $cptcode = '';  //TODO
                    $clinicalHx = '';    //TODO
                    $reasonforstudy = '';   //TODO
                    $impression = '';   //TODO

                    $onereport = 
                            array( 
                                'Title' => $title,
                                'ReportedDate' => $reportdate,
                                'Snippet' => $snippet,
                                'Details' => array(
                                    'Procedure Name' =>$title,
                                    'Report Status' => $status,
                                    'CPT Code' => $cptcode,
                                    'Reason For Study' => $reasonforstudy,
                                    'Clinical HX' => $clinicalHx,
                                    'Impression' => $impression,
                                    'Report' => $detail_tx,
                                    'Facility' => $facility,
                                        ),
                                'AccessionNumber' => $accession,
                                'CaseNumber' => $casenum,
                                'ReportID' =>$reportid,
                            );                    
                    
                    $bundle[] = $onereport;
                }
            }

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
