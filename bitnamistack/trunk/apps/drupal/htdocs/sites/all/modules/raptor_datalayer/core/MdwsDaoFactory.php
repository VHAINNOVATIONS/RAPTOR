<?php namespace raptor;

require_once("IMdwsDao.php");
require_once("MdwsDao.php");
require_once("config.php");

class MdwsDaoFactory {
    // for now have just the MDWS DAO - should be sufficient!
    public static function getMdwsDao($facadeName) {
        return new MdwsDao();
    }
    
    public static function getSoapClientByFacade($facadeName) {
        if ($facadeName === MDWS_EMR_FACADE) {
            return new \SoapClient(EMRSERVICE_URL, array("trace" => 1, "exceptions" => 0)); 
        }
        elseif ($facadeName === MDWS_QUERY_FACADE) {
            return new \SoapClient(QUERYSERVICE_URL, array("trace" => 1, "exceptions" => 0));
        }
        else {
            throw new \Exception("That MDWS facade has not been implemented");
        }
    }
    
    public static function getSoapClientByFunction($functionName) {
        if ($functionName === "ddrLister") {
            return new \SoapClient(QUERYSERVICE_URL, array("trace" => 1, "exceptions" => 0));
        }
        elseif ($functionName === "ddrGetsEntry") {
            return new \SoapClient(QUERYSERVICE_URL, array("trace" => 1, "exceptions" => 0));
        }
        elseif ($functionName === "getVariableValue") {
            return new \SoapClient(QUERYSERVICE_URL, array("trace" => 1, "exceptions" => 0));
        }
        else {
            return new \SoapClient(EMRSERVICE_URL, array("trace" => 1, "exceptions" => 0));
        }
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
