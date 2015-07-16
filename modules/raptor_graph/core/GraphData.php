<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Graph Integration and data format collaboration: Daiwei Lu
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 


namespace raptor;

/**
 * This returns data for graphs (aka charts).
 * 
 * @author Frank Font of SAN Business Consultants
 */
class GraphData
{
    private $m_oContext = NULL;
    private $m_oRuntimeResultFlexCache = NULL;
    
    function __construct($oContext)
    {
        module_load_include('php', 'raptor_datalayer', 'core/data_context');
        //module_load_include('php', 'raptor_datalayer', 'core/data_protocolsupport');
        module_load_include('php', 'raptor_datalayer', 'core/VistaDao');
        module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');
        
        $this->m_oContext = $oContext;
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance('GraphData');
    }    
    
    function getThumbnailGraphValues()
    {
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $soapResult = $mdwsDao->getRawVitalSignsMap();
        $max_dates = 5;
        //$result = MdwsUtils::convertSoapVitalsToGraph(array('Temperature'), $soapResult, $max_dates);
        $result = $mdwsDao->convertSoapVitalsToGraph(array('Temperature'), $soapResult, $max_dates);
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getVitalsGraphValues()
    {
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $soapResult = $mdwsDao->getRawVitalSignsMap();
        $max_dates = 20;
        //$result = MdwsUtils::convertSoapVitalsToGraph(array('Temperature', 'Pulse'), $soapResult, $max_dates);
        $result = $mdwsDao->convertSoapVitalsToGraph(array('Temperature', 'Pulse'), $soapResult, $max_dates);
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getLabsGraphValues()
    {
       
        //$oDD = new \raptor\DashboardData($this->m_oContext);
        //$aDD = $oDD->getDashboardDetails();
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $aDD = $mdwsDao->getDashboardDetailsMap();
        $selectedPatient = array(
                  'ethnicity'=>$aDD['PatientEthnicity']
                , 'gender'=>$aDD['PatientGender']
                , 'age'=>$aDD['PatientAge']);
        //$labsResult = MdwsUtils::getChemHemLabs($this->m_oContext->getMdwsClient());
        $labsResult = $mdwsDao->getChemHemLabs();
            
        //Pass in selected patient and egfr formula if one is defined 
        //$result = MdwsUtils::convertSoapLabsToGraph($selectedPatient, NULL, $labsResult);   //Removed 3 hardcoded limit
        $result = $mdwsDao->convertSoapLabsToGraph($selectedPatient, NULL, $labsResult);
        //error_log('getLabsGraphValues patient>>>'.print_r($selectedPatient,TRUE));
        //error_log('getLabsGraphValues labs>>>'.print_r($labsResult,TRUE));
        //error_log('getLabsGraphValues filtered>>>'.print_r($result,TRUE));
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
}
