<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 


namespace raptor;

module_load_include('php', 'raptor_datalayer', 'core/data_context');
module_load_include('php', 'raptor_datalayer', 'core/MdwsDao');


/**
 * This returns data for graphs (aka charts).
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
        $max_dates = 5;
        $result = MdwsUtils::convertSoapVitalsToGraph(array("Temperature"), $soapResult, $max_dates);
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getVitalsGraphValues()
    {
        // TODO - this data should probably be cached somewhere so a call to MDWS isn't made every time...
        $soapResult = $this->m_oContext->getMdwsClient()->makeQuery("getVitalSigns", NULL);
        $max_dates = 20;
        $result = MdwsUtils::convertSoapVitalsToGraph(array("Temperature", "Pulse"), $soapResult, $max_dates);
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getLabsGraphValues()
    {
       
        $oDD = new \raptor\DashboardData($this->m_oContext);
        $aDD = $oDD->getDashboardDetails();
        $selectedPatient = array(
                  'ethnicity'=>$aDD['PatientEthnicity']
                , 'gender'=>$aDD['PatientGender']
                , 'age'=>$aDD['PatientAge']);
        $labsResult = MdwsUtils::getChemHemLabs($this->m_oContext->getMdwsClient());
            
        //Pass in selected patient and egfr formula if one is defined 
        $result = MdwsUtils::convertSoapLabsToGraph($selectedPatient, NULL, $labsResult, 3);
        error_log('getLabsGraphValues patient>>>'.print_r($selectedPatient,TRUE));
        error_log('getLabsGraphValues labs>>>'.print_r($labsResult,TRUE));
        error_log('getLabsGraphValues filtered>>>'.print_r($result,TRUE));
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
}
