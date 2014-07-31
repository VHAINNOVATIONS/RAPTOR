<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

/**
 * This returns data for graphs.
 * 
 * @author SAN
 */
class GraphData
{
    private $m_oContext = null;
    
    function __construct($oContext)
    {
        $this->m_oContext = $oContext;
    }    
    
    function getThumbnailGraphValues()
    {
        // TODO - this data should probably be cached somewhere so a call to MDWS isn't made every time...
        $soapResult = $this->m_oContext->getMdwsClient()->makeQuery("getVitalSigns", NULL);
        return MdwsUtils::convertSoapVitalsToGraph(array("Temperature"), $soapResult, 5);
        
        //TODO
        return array(
          array (
            "date" => "03-16-2014",
            "temperature" => 98.6
          ),
          array (
            "date" => "03-17-2014",
            "temperature" => 99.8,
            "flag" => "MISSING"
          ),
          array (
            "date" => "03-18-2014",
            "temperature" => 97.6
          ),
          array (
            "date" => "03-19-2014",
            "temperature" => 101.2
          ),
          array (
            "date" => "03-20-2014",
            "temperature" => 98.6
          ),
          array (
            "date" => "03-21-2014",
            "temperature" => 103.4,
            "flag" => "NOCIRCLE"
          ),
          array (
            "date" => "03-22-2014",
            "temperature" => 102.1
          ),
        );
    }
    
    function getVitalsGraphValues()
    {
        // TODO - this data should probably be cached somewhere so a call to MDWS isn't made every time...
        $soapResult = $this->m_oContext->getMdwsClient()->makeQuery("getVitalSigns", NULL);
        return MdwsUtils::convertSoapVitalsToGraph(array("Temperature", "Pulse"), $soapResult, 5);

        //TODO
        return array(
            array(
              "date" => "03-26-2014",
              "temperature" => 98.6,
              "pulse" => 81
            ),
            array(
              "date" => "03-27-2014",
              "temperature" => 99.8,
              "pulse" => 83,
              "tempFlag" => "MISSING"
            ),
            array(
              "date" => "03-28-2014",
              "temperature" => 97.6,
              "pulse" => 82,
              "pulseFlag" => "MISSING",
              "tempFlag" => "NOCIRCLE"
            ),
            array(
              "date" => "03-29-2014",
              "temperature" => 101.,
              "pulse" => 84
            ),
            array(
              "date" => "03-30-2014",
              "temperature" => 98.6,
              "pulse" => 82,
              "tempFlag" => "NOCIRCLE"
            ),
            array(
              "date" => "03-31-2014",
              "temperature" => 101.6,
              "pulse" => 80
            )
          );
    }
    
    function getLabsGraphValues()
    {
       
        $labsResult = MdwsUtils::getChemHemLabs($this->m_oContext->getMdwsClient());
        // TODO - the patient is hardcoded right here! need to get selected patient but where from ???
        $selectedPatient = array("ethnicity"=>"causacian", "gender"=>"M", "age"=>"69");
                
        // TODO - need to pass in selected patient and egfr formula if one is defined 
        $filtered = MdwsUtils::convertSoapLabsToGraph($selectedPatient, NULL, $labsResult, 3);
        
        return $filtered;

        //TODO
        return array(
                    array(
                      "date" => "03-16-2014",
                      "egfr" => 50
                    ),
                    array(
                      "date" => "03-17-2014",
                      "egfr" => 49
                    ),
                    array(
                      "date" => "03-18-2014",
                      "egfr" => 51,
                      "flag" => "MISSING"
                    ),
                    array(
                      "date" => "03-19-2014",
                      "egfr" => 56
                    ),
                    array(
                      "date" => "03-20-2014",
                      "egfr" => 45
                    ),
                    array(
                      "date" => "03-21-2014",
                      "egfr" => 43,
                      "flag" => "NOCIRCLE"
                    ),
                    array(
                      "date" => "03-22-2014",
                      "egfr" => 47
                    ),
                    array(
                      "date" => "03-23-2014",
                      "egfr" => 50
                    )
                );
    }
}
