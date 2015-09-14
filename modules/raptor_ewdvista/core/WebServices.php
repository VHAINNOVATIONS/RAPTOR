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
//error_log("LOOK callAPI about to issue $methodtype@$url with header=".print_r($headers_ar,TRUE));            
            
            switch ($methodtype)
            {
                case 'POST':
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if($data_ar)
                    {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_ar);
                    }
                    break;
                case 'PUT':
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                case 'GET':
                    if($data_ar)
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

            //TODO: Note at the next step curl_exec() migh return error message listed below, 
            //      while the same API call through Advanced Rest Client works just fine. 
            //      Here is the error message:
            //      {"code":"RESTError","message":"An error occurred while executing raptor/parse: TypeError: Cannot call method 'substr' of undefined"}
            $result = curl_exec($curl);
            //error_log("LOOK (0000004) callAPI result: " . print_r($result,TRUE));
            //error_log("LOOK (0000005) callAPI executes following curl: " . print_r($curl,TRUE));

            curl_close($curl);
            /*
$debug_result_text = print_r($result,TRUE);
$debug_rawlen_result_text = strlen($debug_result_text);
$debug_maxtolog = 4000;
if($debug_rawlen_result_text > $debug_maxtolog)
{
    $debug_result_text = substr($debug_result_text,0,$debug_maxtolog) 
            . " ... ONLY LOGGED $debug_maxtolog chars (Original size $debug_rawlen_result_text chars)";
}
error_log("LOOK callAPI result from $methodtype@$url is =".$debug_result_text);            
      */      
            return $result;
        } catch (\Exception $ex) {
            throw new \Exception("Failed callAPI($methodtype, $url, $data_ar) because ".$ex,99888,$ex);
        }
    }
}
