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
class PathologyReportHelper
{
    //Declare the field numbers
    private static $FLD_FACILITY = 1;
    private static $FLD_REPORTDATE = 2;
    private static $FLD_SPECIMENS = 3;
    private static $FLD_SPECIMEN_ASCESSION_NUM = 4;
    private static $FLD_EXAM = 5;
    
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

    private function getUserDataFromArrayOfArray($myarray,$offset,$delimiter="<br />")
    {
        if(!isset($myarray[$offset]))
        {
            return '';
        }
        $rows = $myarray[$offset];
        if(!is_array($rows))
        {
            return '';
        }
        $cleanrows = array();
        foreach($rows as $rawline)
        {
            $cleanrows[] = substr($rawline,2);  //Assume first two things are #^
        }
        return implode($delimiter, $cleanrows);
    }
    
    public function getFormattedPathologyReportHelperDetail($rawresult_ar)
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
                    $reportdate = $this->getUserDataFromArray($onereport, self::$FLD_REPORTDATE);
                    $accession = $this->getUserDataFromArray($onereport, self::$FLD_SPECIMEN_ASCESSION_NUM);
                    $detail_tx = trim($this->getUserDataFromArrayOfArray($onereport, self::$FLD_EXAM));
                    $specimenName = trim($this->getUserDataFromArrayOfArray($onereport, self::$FLD_SPECIMENS, ','));
                    if($specimenName > '')
                    {
                        $snippet = $specimenName . '...';
                    } else {
                        $snippet = 'specimen not listed...';
                    }

                    $onereport = array(
                            'Title' => 'Surgical Pathology Report',
                            'ReportDate' => $reportdate,
                            'Snippet' => $snippet,
                            'Details' => $detail_tx,
                            'Accession' => $accession,
                            'Exam' => $detail_tx,  //REDUNDANT WITH DETAIL
                            'Facility' => $facility,
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
