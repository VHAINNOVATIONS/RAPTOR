<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor;

module_load_include('php', 'raptor_glue', 'core/config');
require_once "IMdwsDao.php";
require_once "MdwsDao.php";

class MdwsDaoFactory {
    // for now have just the MDWS DAO - should be sufficient!
    public static function getMdwsDao($facadeName) {
        return new MdwsDao();
    }
    
    public static function getSoapClientByFacade($facadeName) {
        if ($facadeName === EMRSERVICE_URL) {
            $rslt = new \SoapClient(EMRSERVICE_LOCAL_FILE, array("trace" => 1, "exceptions" => 0)); 
            $rslt->__setLocation(EMRSERVICE_URL);
            return $rslt;
        }
        elseif ($facadeName === QUERYSERVICE_URL) {
            $rslt = new \SoapClient(QUERYSERVICE_LOCAL_FILE, array("trace" => 1, "exceptions" => 0));
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
        //error_log('LOOK finishing getSoapClientByFunction for function=['.$functionName.']>>>uri=['.$uri.']');
        
        $oTheClient = null; // new \SoapClient($uri, array("trace" => 1, "exceptions" => 0));

        if ($uri == EMRSERVICE_URL) {
            $oTheClient = new \SoapClient(EMRSERVICE_LOCAL_FILE, array("trace" => 1, "exceptions" => 0));
            $oTheClient->__setLocation(EMRSERVICE_URL);
        }
        else if ($uri == QUERYSERVICE_URL) {
            $oTheClient = new \SoapClient(QUERYSERVICE_LOCAL_FILE, array("trace" => 1, "exceptions" => 0));
            $oTheClient->__setLocation(QUERYSERVICE_URL);
        }
//error_log('LOOK result of getSoapClientByFunction for function=['.$functionName.']>>>uri=['.$uri.']');
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
