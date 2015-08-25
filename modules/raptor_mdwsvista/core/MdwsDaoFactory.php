<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Joel Mewton, et al
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

namespace raptor_mdwsvista;

module_load_include('php', 'raptor_glue', 'core/config');
require_once 'IMdwsDao.php';
require_once 'MdwsDao.php';

class MdwsDaoFactory 
{
    // for now have just the MDWS DAO - should be sufficient!
    public static function getMdwsDao($facadeName) {
        return new MdwsDao();
    }
    
    public static function getSoapClientByFacade($facadeName) 
    {
        $thispath = dirname(__FILE__);
        $emrsvc_wsdl_filepath = "$thispath/emrsvc.wsdl";
        $querysvc_wsdl_filepath = "$thispath/querysvc.wsdl";

        if ($facadeName === EMRSERVICE_URL) 
        {
            $rslt = new \SoapClient($emrsvc_wsdl_filepath, array("trace" => 1, "exceptions" => 0)); 
            $rslt->__setLocation(EMRSERVICE_URL);
            return $rslt;
        } elseif ($facadeName === QUERYSERVICE_URL) {
            $rslt = new \SoapClient($querysvc_wsdl_filepath, array("trace" => 1, "exceptions" => 0));
            $rslt->__setLocation(QUERYSERVICE_URL);
            return $rslt;
        }
        else {
            throw new \Exception("That MDWS facade has not been implemented");
        }
    }
    
    public static function getSoapClientByFunction($functionName) {
        //error_log('LOOK starting getSoapClientByFunction for function=['.$functionName.']');
        $uri = NULL;
        if ($functionName === "ddrLister") {
            $uri = QUERYSERVICE_URL;
        }
        elseif ($functionName === "ddrGetsEntry") {
            $uri = QUERYSERVICE_URL;
        }
        elseif ($functionName === "getVariableValue") {
            $uri = QUERYSERVICE_URL;
        }
        else {
            $uri = EMRSERVICE_URL;
        }
        
        $oTheClient = NULL;
        $thispath = dirname(__FILE__);
        $emrsvc_wsdl_filepath = "$thispath/emrsvc.wsdl";
        $querysvc_wsdl_filepath = "$thispath/querysvc.wsdl";

        if ($uri == EMRSERVICE_URL) 
        {
            $oTheClient = new \SoapClient($emrsvc_wsdl_filepath, array("trace" => 1, "exceptions" => 0));
            $oTheClient->__setLocation(EMRSERVICE_URL);
        } elseif ($uri == QUERYSERVICE_URL) 
        {
            $oTheClient = new \SoapClient($querysvc_wsdl_filepath, array("trace" => 1, "exceptions" => 0));
            $oTheClient->__setLocation(QUERYSERVICE_URL);
        }
        return $oTheClient;
    }
    
    public static function getFacadeNameByFunction($functionName) {
        if ($functionName === "ddrLister") {
            return QUERYSERVICE_URL;
        }
        elseif ($functionName === "ddrGetsEntry") {
            return QUERYSERVICE_URL;
        }
        elseif ($functionName === "getVariableValue") {
            return QUERYSERVICE_URL;
        }
        else {
            return EMRSERVICE_URL;
        }
    }
}
