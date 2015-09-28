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

/**
 * This is the primary interface to call web services
 *
 * @author Frank Font of SAN Business Consultants
 */
class WebServices
{
    /**
     * Call a web service method.
     * See http://stackoverflow.com/questions/9802788/call-a-rest-api-in-php
     * 
     * NOTE:  $data is an associative array (data[fieldname] = value) 
     *        which holds the data sent to the api method
     */
    public function callAPI($methodtype, $url, $data_ar = FALSE, $headers_ar = FALSE)
    {
        try
        {
            $curl = curl_init();
            switch ($methodtype)
            {
                case 'POST':
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if($data_ar !== FALSE)
                    {   /*
                        error_log("LOOK method=$methodtype url=$url"
                                . "\n\tand data_ar=" . print_r($data_ar,TRUE) 
                                . "\n\tand headers_ar=" . print_r($headers_ar,TRUE));
                         */
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_ar);
                    }
                    break;
                case 'PUT':
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                case 'GET':
                    if($data_ar !== FALSE)
                    {
                        $url = sprintf("%s?%s", $url, http_build_query($data_ar));
                    }
                    break;
                default:
                    throw new \Exception("No support for http method type by name of '$methodtype' for url=$url");
            }
            if($headers_ar !== FALSE)
            {
                $headers = array();
                foreach($headers_ar as $key=>$value)
                {
                    $headers[] = "$key: $value";
                }
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            }
            
            // Optional Authentication:
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "username:password");
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            curl_close($curl);
            return $result;
        } catch (\Exception $ex) {
            error_log("Failed with $ex on method=$methodtype url=$url"
                    . "\n\tand data_ar=" . print_r($data_ar,TRUE) 
                    . "\n\tand headers_ar=" . print_r($headers_ar,TRUE));
            throw new \Exception("Failed callAPI($methodtype, $url, " 
                    . gettype($data_ar) 
                    . ", " 
                    . gettype($headers_ar) . ") because " . $ex ,99888 , $ex);
        }
    }
}
