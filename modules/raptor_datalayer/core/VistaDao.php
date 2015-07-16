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
    private $instanceTimestamp = NULL;
    private $m_implclass = NULL;
    private $m_oRuntimeResultFlexCache;    //Cache results.
    
    function __construct()
    {
        $this->instanceTimestamp = time();
        $this->errorCount = 0;
        error_log("Creating instance of VistaDao ts={$this->instanceTimestamp}");
        module_load_include('php', 'raptor_datalayer', 'config/vista_integration');
        $name = VISTA_INT_IMPL_DAO_CLASSNAME;
        $class = "\\raptor\\$name";
        $this->m_implclass = new $class();
        
        //$this->m_oContext = \raptor\Context::getInstance();
        global $user;
        $uid = $user->uid;  //$this->m_oContext->getUID();
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance("VistaDao[$uid]");
        error_log("Constructor completed >>> ".$this);
    }
    
    /**
     * Get the implementation instance
     */
    public function getImplementationInstance()
    {
        return $this->m_implclass;
    }
    
    public function __toString()
    {
        try
        {
            $authenticated_info = $this->isAuthenticated() ? 'isAuthenticated=[TRUE] ' : 'isAuthenticated=[FALSE] ';
        } catch (\Exception $ex) {
            $authenticated_info = "isAuthenticated failed because ".$ex->getMessage() . ' ';
        }
        try
        {
            $ehr_user_info = 'EHR User ID=['.$this->getEHRUserID().'] ';
        } catch (\Exception $ex) {
            $ehr_user_info = "getEHRUserID failed because " . $ex->getMessage() . ' ';
        }
        try
        {
            $impl_info = "EHR Implementation details are as follows ...\n".$this->m_implclass.' ';
        } catch (\Exception $ex) {
            $impl_info = "EHR Implementation failed because " . $ex->getMessage() . ' ';
        }
        try 
        {
            return 'VistaDao instance created at ' 
                    . $this->instanceTimestamp . ' '
                    . 'current error count=[' . $this->errorCount . '] '
                    . $authenticated_info
                    . $ehr_user_info
                    . "\nImplementation DAO=".$this->m_implclass;
        } catch (\Exception $ex) {
            return 'Cannot get toString of VistaDao because ' . $ex->getMessage();
        }
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
            $oContext = Context::getInstance();
            $tid = $oContext->getSelectedTrackingID();
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
        return $this->m_implclass->createNewRadiologyOrder($orderChecks, $args);
    }

    public function createUnsignedRadiologyOrder($orderChecks, $args)
    {
        return $this->m_implclass->createUnsignedRadiologyOrder($orderChecks, $args);
    }

    public function getOrderableItems($imagingTypeId)
    {
        return $this->m_implclass->getOrderableItems($imagingTypeId);
    }

    public function getRadiologyOrderChecks($args)
    {
        return $this->m_implclass->getRadiologyOrderChecks($args);
    }

    public function getRadiologyOrderDialog($imagingTypeId, $patientId)
    {
        return $this->m_implclass->getRadiologyOrderDialog($imagingTypeId, $patientId);
    }

    public function getProviders($neworderprovider_name)
    {
        return $this->m_implclass->getProviders($neworderprovider_name);
    }

    public function getUserSecurityKeys()
    {
        return $this->m_implclass->getUserSecurityKeys();
    }

    public function isProvider()
    {
        return $this->m_implclass->isProvider();
    }

    public function userHasKeyOREMAS()
    {
        return $this->m_implclass->userHasKeyOREMAS();
    }

    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig)
    {
        return $this->m_implclass->cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig);
    }

    public function convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates=5)
    {
        return $this->m_implclass->convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates);
    }

    public function getChemHemLabs()
    {
        return $this->m_implclass->getChemHemLabs();
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        return $this->m_implclass->getEncounterStringFromVisit($vistitTo);
    }

    public function getHospitalLocations($startingitem)
    {
        return $this->m_implclass->getHospitalLocations($startingitem);
    }

    public function getOrderDetails($myIEN)
    {
        return $this->m_implclass->getOrderDetails($myIEN);
    }

    public function getRadiologyCancellationReasons()
    {
        return $this->m_implclass->getRadiologyCancellationReasons();
    }

    public function getVisits()
    {
        return $this->m_implclass->getVisits();
    }

    public function signNote($newNoteIen, $eSig)
    {
        return $this->m_implclass->signNote($newNoteIen, $eSig);
    }

    public function validateEsig($eSig)
    {
        return $this->m_implclass->validateEsig($eSig);
    }

    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle)
    {
        return $this->m_implclass->verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle);
    }

    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ)
    {
        return $this->m_implclass->writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ);
    }

    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ)
    {
        return $this->m_implclass->writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ);
    }

    public function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs = 1000)
    {
        return $this->m_implclass->convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs);
    }

    public function getEHRUserID($fail_if_missing=TRUE)
    {
        return $this->m_implclass->getEHRUserID($fail_if_missing);
    }
    
    public function setPatientID($sPatientID)
    {
        return $this->m_implclass->setPatientID($sPatientID);
    }
    
    public function getVitalsDetailOnlyLatestMap()
    {
        return $this->m_implclass->getVitalsDetailOnlyLatestMap();
    }
    
    //xxxxxxxxxxxxxxxxxxxxx
    
    public function getEGFRDetailMap()
    {
        return $this->m_implclass->getEGFRDetailMap();
    }

    public function getPatientDashboardMap()
    {
        return $this->m_implclass->getPatientDashboardMap();
    }

    public function getRareContrastKeywordsMap()
    {
        return $this->m_implclass->getRareContrastKeywordsMap();
    }

    public function getRareRadioisotopeKeywordsMap()
    {
        return $this->m_implclass->getRareRadioisotopeKeywordsMap();
    }

    public function getBloodThinnerKeywordsMap()
    {
        return $this->m_implclass->getBloodThinnerKeywordsMap();
    }

    public function getAllergyContrastKeywordsMap()
    {
        return $this->m_implclass->getAllergyContrastKeywordsMap();
    }

    public function getRawVitalSignsMap()
    {
        return $this->m_implclass->getRawVitalSignsMap();
    }
    
    
//yyyyyyyyyyyyy
    
    public function getAllHospitalLocationsMap()
    {
        return $this->m_implclass->getAllHospitalLocationsMap();
    }

    public function getAllergiesDetailMap()
    {
        return $this->m_implclass->getAllergiesDetailMap();
    }

    public function getOrderOverviewMap()
    {
        return $this->m_implclass->getOrderOverviewMap();
    }

    public function getVitalsSummaryMap()
    {
        return $this->m_implclass->getVitalsSummaryMap();
    }

    public function getVitalsDetailMap()
    {
        return $this->m_implclass->getVitalsDetailMap();
    }

    public function getProcedureLabsDetailMap()
    {
        return $this->m_implclass->getProcedureLabsDetailMap();
    }

    public function getDiagnosticLabsDetailMap()
    {
        return $this->m_implclass->getDiagnosticLabsDetailMap();
    }

    public function getPathologyReportsDetailMap()
    {
        return $this->m_implclass->getPathologyReportsDetailMap();
    }

    public function getSurgeryReportsDetailMap()
    {
        return $this->m_implclass->getSurgeryReportsDetailMap();
    }

    public function getProblemsListDetailMap()
    {
        return $this->m_implclass->getProblemsListDetailMap();
    }

    public function getRadiologyReportsDetailMap()
    {
        return $this->m_implclass->getRadiologyReportsDetailMap();
    }

    public function getMedicationsDetailMap($atriskmeds = NULL)
    {
        return $this->m_implclass->getMedicationsDetailMap($atriskmeds);
    }
    
//zzzzzzzzzzzzzzzzzz
    
    public function getNotesDetailMap()
    {
        return $this->m_implclass->getNotesDetailMap();
    }

    public function getPendingOrdersMap()
    {
        return $this->m_implclass->getPendingOrdersMap();
    }

    public function getImagingTypesMap()
    {
        return $this->m_implclass->getImagingTypesMap();
    }
    
    
}
