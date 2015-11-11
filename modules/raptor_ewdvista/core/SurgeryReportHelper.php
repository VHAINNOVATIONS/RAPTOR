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
 * Helper for returning Surgery Report content
 *
 * @author Frank Font of SAN Business Consultants
 */
class SurgeryReportHelper
{
    public function getFormattedSurgeryReportDetail($rawresult_ar)
    {
        try
        {
            if(!is_array($rawresult_ar))
            {
                throw new \Exception("Cannot format a non-array of data!");
            }
            
            $bundle = array();
            foreach($rawresult_ar as $onereport)
            {
                if(isset($onereport['title']) && trim($onereport['title']) > '')
                {
                    $title = trim($onereport['title']);
                } else {
                    $title = "NO TITLE FOUND!";
                }
                $timestamp = $onereport['timestamp'];
                $reportdate = EwdUtils::convertVistaDateTimeToDatetime($timestamp);
                $raw_text = $onereport['text'];
                if(strlen($raw_text) < RAPTOR_DEFAULT_SNIPPET_LEN)
                {
                    $snip = trim($raw_text);
                } else {
                    $snip = substr(trim($raw_text),0,RAPTOR_DEFAULT_SNIPPET_LEN) . '...';
                }
                if($snip == '')
                {
                    //This can happen if there was NO report text
                    $detail = 'No reports available for this case!';
                    $snip = $detail;
                } else {
                    $detail = str_replace("\n",'<br />',$raw_text);
                }
                $onereport = array(
                        'Title' => $title,
                        'ReportDate' => $reportdate,
                        'Snippet' => $snip,
                        'Details' => $detail,
                    );
                $bundle[] = $onereport;
            }

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
