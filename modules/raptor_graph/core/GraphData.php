<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 


namespace raptor;

module_load_include('php', 'raptor_datalayer', 'core/data_context');
module_load_include('php', 'raptor_datalayer', 'core/MdwsDao');
module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');

defined('CACHE_AGE_GRAPH_LABS')
    or define('CACHE_AGE_GRAPH_LABS', 60);

/**
 * This returns data for graphs (aka charts).
 * 
 * @author SAN
 */
class GraphData
{
    private $m_oContext = NULL;
    private $m_oRuntimeResultFlexCache = NULL;
    
    function __construct($oContext)
    {
        $this->m_oContext = $oContext;
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance('GraphData');
    }    
    
    /**
     * Leverage caching so only one call is made.
     */
    private function getVitalSigns()
    {
        $sThisResultName = $this->m_oContext->getSelectedTrackingID() . '_raw_getVitalSigns'; //patient specific
        $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
        if($aCachedResult !== NULL)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        $this->m_oRuntimeResultFlexCache->markCacheBuilding($sThisResultName);
        $soapResult = $this->m_oContext->getMdwsClient()->makeQuery('getVitalSigns', NULL);
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $soapResult, CACHE_AGE_GRAPH_LABS);
        $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
        return $soapResult;
    }
    
    function getThumbnailGraphValues()
    {
        //$soapResult = $this->m_oContext->getMdwsClient()->makeQuery('getVitalSigns', NULL);
        $soapResult = $this->getVitalSigns();
        $max_dates = 5;
        $result = MdwsUtils::convertSoapVitalsToGraph(array('Temperature'), $soapResult, $max_dates);
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getVitalsGraphValues()
    {
        //$soapResult = $this->m_oContext->getMdwsClient()->makeQuery('getVitalSigns', NULL);
        $soapResult = $this->getVitalSigns();
        $max_dates = 20;
        $result = MdwsUtils::convertSoapVitalsToGraph(array('Temperature', 'Pulse'), $soapResult, $max_dates);
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
        $result = MdwsUtils::convertSoapLabsToGraph($selectedPatient, NULL, $labsResult);   //Removed 3 hardcoded limit
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
