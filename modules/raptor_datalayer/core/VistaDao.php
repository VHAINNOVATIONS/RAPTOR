<?php
/**
 * @file
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

require_once 'IVistaDao.php';
require_once 'data_context.php';
require_once 'RuntimeResultFlexCache.php';
require_once 'WorklistColumnMap.php';

/**
 * This is the primary interface abstraction to EHR
 *
 * @author Frank Font of SAN Business Consultants
 */
class VistaDao implements IVistaDao
{
    private $m_implclass = NULL;
    private $m_oContext = NULL;
    private $m_oRuntimeResultFlexCache;    //Cache results.
    
    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'config/vista_integration');
        $name = VISTA_INT_IMPL_DAO_CLASSNAME;
        $class = "\\raptor\\$name";
        $this->m_implclass = new $class();
        
        $this->m_oContext = \raptor\Context::getInstance();
        $uid = $this->m_oContext->getUID();
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance("VistaDao[$uid]");
    }
    
    public function getIntegrationInfo() 
    {
        return $this->m_implclass->getIntegrationInfo();
    }
    
    public function initClient()
    {
        return $this->m_implclass->initClient();
    }

    public function connectAndLogin($siteCode, $username, $password) 
    {
        return $this->m_implclass->connectAndLogin($siteCode, $username, $password);
    }

    public function disconnect() 
    {
       return $this->m_implclass->disconnect();
    }

    public function isAuthenticated() 
    {
       return $this->m_implclass->isAuthenticated();
    }

    /**
     * Gets dashboard details for the currently selected ticket of the session
     */
    function getDashboardDetailsMap($override_tracking_id=NULL)
    {
        if($override_tracking_id == NULL)
        {
            $tid = $this->m_oContext->getSelectedTrackingID();
        } else {
            $tid = $override_tracking_id;
        }
        
        //Look in the cache first
        $sThisResultName = "getDashboardDetailsMap[$tid]";
        $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
        if($aCachedResult !== NULL)
        {
            //Found it in the cache
            return $aCachedResult;
        }

        //Get the content and add it to the cache
        $aResult = $this->m_implclass->getDashboardDetailsMap();
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $aResult, CACHE_AGE_SITEVALUES);
        return $aResult;
    }
    
    /**
     * Not intended as a primary public interface
     * @deprecated USE SPECIAL PURPOSE FUNCTION CALLS INSTEAD OF THIS ONE!!!!
     */
    function makeQuery($functionToInvoke, $args) 
    {
        return $this->m_implclass->makeQuery($functionToInvoke, $args);
    }

    public function getWorklistDetailsMap()
    {
        return $this->m_implclass->getWorklistDetailsMap();
    }
    
    public function getVistaAccountKeyProblems() 
    {
        return $this->m_implclass->getVistaAccountKeyProblems();
    }

    public function getPatientIDFromTrackingID($sTrackingID) 
    {
        return $this->m_implclass->getPatientIDFromTrackingID($sTrackingID);
    }
    
    public function createNewRadiologyOrder($orderChecks, $args)
    {
        throw new \Exception("Not implemented yet");
    }

    public function createUnsignedRadiologyOrder($orderChecks, $args)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getOrderableItems($imagingTypeId)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getRadiologyOrderChecks($args)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getRadiologyOrderDialog($imagingTypeId, $patientId)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getProviders($neworderprovider_name)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getUserSecurityKeys($userDuz)
    {
        throw new \Exception("Not implemented yet");
    }

    public function isProvider($myDuz)
    {
        throw new \Exception("Not implemented yet");
    }

    public function userHasKeyOREMAS($myDuz)
    {
        throw new \Exception("Not implemented yet");
    }

    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig)
    {
        throw new \Exception("Not implemented yet");
    }

    public function convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getChemHemLabs()
    {
        throw new \Exception("Not implemented yet");
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getHospitalLocations($startingitem)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getOrderDetails($myIEN)
    {
        throw new \Exception("Not implemented yet");
    }

    public function getRadiologyCancellationReasons()
    {
        throw new \Exception("Not implemented yet");
    }

    public function getVisits()
    {
        throw new \Exception("Not implemented yet");
    }

    public function signNote($newNoteIen, $userDuz, $eSig)
    {
        throw new \Exception("Not implemented yet");
    }

    public function validateEsig($eSig)
    {
        throw new \Exception("Not implemented yet");
    }

    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle)
    {
        throw new \Exception("Not implemented yet");
    }

    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ)
    {
        throw new \Exception("Not implemented yet");
    }

    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ)
    {
        throw new \Exception("Not implemented yet");
    }

    public function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs = 1000)
    {
        throw new \Exception("Not implemented yet");
    }

}
