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
    
    function __construct($oContext)
    {
        module_load_include('php', 'raptor_datalayer', 'core/Context');
        module_load_include('php', 'raptor_datalayer', 'core/VistaDao');
        $this->m_oContext = $oContext;
    }    
    
    function getThumbnailGraphValues()
    {
        $ehrDao = $this->m_oContext->getEhrDao();
        $soapResult = $ehrDao->getRawVitalSignsMap();
        $max_dates = 5;
        $result = $ehrDao->convertSoapVitalsToGraph(array('Temperature'), $soapResult, $max_dates);
        //error_log("LOOK thumb soap data>>>".print_r($soapResult,TRUE));
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getVitalsGraphValues()
    {
        $ehrDao = $this->m_oContext->getEhrDao();
        $soapResult = $ehrDao->getRawVitalSignsMap();
        $max_dates = 20;
        $result = $ehrDao->convertSoapVitalsToGraph(array('Temperature', 'Pulse'), $soapResult, $max_dates);
        //error_log("LOOK vitals soap data>>>".print_r($soapResult,TRUE));
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
        $ehrDao = $this->m_oContext->getEhrDao();
        $aDD = $ehrDao->getDashboardDetailsMap();
        $selectedPatient = array(
                  'ethnicity'=>$aDD['PatientEthnicity']
                , 'gender'=>$aDD['PatientGender']
                , 'age'=>$aDD['PatientAge']);
        $labsResult = $ehrDao->getChemHemLabs();
            
        //Pass in selected patient and egfr formula if one is defined 
        $result = $ehrDao->convertSoapLabsToGraph($selectedPatient, NULL, $labsResult);
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
