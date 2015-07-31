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

require_once 'Context.php';
require_once 'IEhrDao.php';
require_once 'RuntimeResultFlexCache.php';
require_once 'WorklistColumnMap.php';

/**
 * This is the primary interface abstraction to EHR
 *
 * @author Frank Font of SAN Business Consultants
 */
class EhrDao implements \raptor\IEhrDao
{
    private $instanceTimestamp = NULL;
    private $m_implclass = NULL;
    
    function __construct()
    {
        try
        {
            $this->instanceTimestamp = microtime();
            error_log("Creating instance of EhrDao ts={$this->instanceTimestamp}");
            $loaded = module_load_include('php', 'raptor_datalayer', 'config/ehr_integration');
            if($loaded === FALSE)
            {
                throw new \Exception("Failed to load config/ehr_integration!");
            }
            defined('EHR_INT_IMPL_DAO_NAMESPACE')
                or define('EHR_INT_IMPL_DAO_NAMESPACE', 'MISSING');
            if(EHR_INT_IMPL_DAO_NAMESPACE == 'MISSING')
            {
                throw new \Exception("Did NOT find definition for EHR_INT_IMPL_DAO_NAMESPACE!");
            }
            $classname = EHR_INT_IMPL_DAO_CLASSNAME;
            $namespace = EHR_INT_IMPL_DAO_NAMESPACE;
            $class = "\\$namespace\\$classname";
            $this->m_implclass = new $class();
            error_log("Construction completed >>> ".$this);
        } catch (\Exception $ex) {
            throw new \Exception("Failed constructor EhrDao because $ex",99876,$ex);
        }
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
            return 'EhrDao instance created at ' 
                    . $this->instanceTimestamp . ' '
                    . $authenticated_info
                    . $ehr_user_info
                    . "\nImplementation DAO=".$this->m_implclass;
        } catch (\Exception $ex) {
            return 'Cannot get toString of EhrDao because ' . $ex->getMessage();
        }
    }
    
    public function getIntegrationInfo() 
    {
        return $this->m_implclass->getIntegrationInfo();
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
        return $this->m_implclass->getDashboardDetailsMap($override_tracking_id);
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

    public function getChemHemLabs()
    {
        return $this->m_implclass->getChemHemLabs();
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        return $this->m_implclass->getEncounterStringFromVisit($vistitTo);
    }

    public function getHospitalLocationsMap($startingitem)
    {
        return $this->m_implclass->getHospitalLocationsMap($startingitem);
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
    
    public function getEGFRDetailMap()
    {
        return $this->m_implclass->getEGFRDetailMap();
    }

    public function getRawVitalSignsMap()
    {
        return $this->m_implclass->getRawVitalSignsMap();
    }
    
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

    public function invalidateCacheForOrder($tid)
    {
        return $this->m_implclass->invalidateCacheForOrder($tid);
    }

    public function invalidateCacheForPatient($pid)
    {
        return $this->m_implclass->invalidateCacheForPatient($pid);
    }
}
