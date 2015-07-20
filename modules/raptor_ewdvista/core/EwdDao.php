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

namespace raptor_ewdvista;

require_once 'IEwdDao.php';

/**
 * This is the primary interface implementation to VistA using EWDJS
 *
 * @author Frank Font of SAN Business Consultants
 */
class EwdDao implements \raptor_ewdvista\IEwdDao
{
    private $m_createdtimestamp = NULL;
    private $m_authorization = NULL;
    private $m_init_key = NULL;
    private $m_credentials = NULL;
    private $m_oWebServices = NULL;
    
    function __construct()
    {
        module_load_include('php', 'raptor_ewdvista', 'core/WebServices');

        $this->m_createdtimestamp = time();        
        $this->m_oWebServices = new \raptor_ewdvista\WebServices();
    }

    public function getIntegrationInfo()
    {
        return "EWD VISTA EHR Integration"; //TODO get real runtime version number
    }

    public function initClient()
    {
        try
        {
            //TODO make sure method and URL is configurable
            $method = 'initiate';
            $url="http://localhost:8081/RaptorEwdVista/raptor/initiate";
            $json_string = $this->m_oWebServices->callAPI($method, $url);
            $json_array = json_decode($json_string, TRUE);
            $this->m_authorization = $json_array["Authorization"];
            $this->m_init_key = $json_array["key"];
        } catch (\Exception $ex) {
            throw new \Exception("Trouble in initClient because ".$ex,99876,$ex);
        }
    }

    public function connectAndLogin($siteCode, $username, $password) 
    {
        if($this->m_authorization == NULL)
        {
            throw new \Exception("No authorization code has been set!");
        }
        if($this->m_init_key == NULL)
        {
            throw new \Exception("No initialization key has been set!");
        }
        throw new \Exception("Not implemented $siteCode, $username, $password");
    }

    public function getWorklistDetailsMap()
    {
        if($this->m_credentials == NULL)
        {
            throw new \Exception("No credentials have been set!");
        }
        throw new \Exception("Not implemented");
    }
    
    /**
     * Return array of valuse from the indicated action
     * This is good for developers to check results
     */
    public function getPrivateValue($keynames)
    {
        try
        {
            if(!is_array($keynames))
            {
                $keynames_ar = array($keynames);
            } else {
                $keynames_ar = $keynames;
            }
            $result = array();
            foreach($keynames_ar as $keyname)
            {
                $varname = "m_{$keyname}";
                $result[$keyname] = $this->$varname;
            }
            return $result;
        } catch (\Exception $ex) {
            $msg = "Failed getting keynames because ".$ex;
            throw new \Exception($msg,99876,$ex);
        }
    }
    
    public function disconnect() 
    {
        throw new \Exception("Not implemented");
    }

    public function isAuthenticated() 
    {
        throw new \Exception("Not implemented");
    }

    public function __toString()
    {
        return "EwdDao created $this->m_createdtimestamp";
    }

    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig)
    {
        throw new \Exception("Not implemented $patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig");
    }

    public function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs = 1000)
    {
        throw new \Exception("Not implemented $patientInfo, $egfrFormula, $allLabs, $limitMaxLabs");
    }

    public function convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates = 5)
    {
        throw new \Exception("Not implemented $vitalsdata, $soapResult, $max_dates");
    }

    public function createNewRadiologyOrder($orderChecks, $args)
    {
        throw new \Exception("Not implemented $orderChecks, $args");
    }

    public function createUnsignedRadiologyOrder($orderChecks, $args)
    {
        throw new \Exception("Not implemented $orderChecks, $args");
    }

    public function getAllHospitalLocationsMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getAllergiesDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getChemHemLabs()
    {
        throw new \Exception("Not implemented");
    }

    public function getDashboardDetailsMap($override_tracking_id = NULL)
    {
        throw new \Exception("Not implemented");
    }

    public function getDiagnosticLabsDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getEGFRDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getEHRUserID($fail_if_missing = TRUE)
    {
        throw new \Exception("Not implemented");
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        throw new \Exception("Not implemented");
    }

    public function getHospitalLocations($startingitem)
    {
        throw new \Exception("Not implemented");
    }

    public function getImagingTypesMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getImplementationInstance()
    {
        throw new \Exception("Not implemented");
    }

    public function getMedicationsDetailMap($atriskmeds = NULL)
    {
        throw new \Exception("Not implemented");
    }

    public function getNotesDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getOrderDetails($myIEN)
    {
        throw new \Exception("Not implemented");
    }

    public function getOrderOverviewMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getOrderableItems($imagingTypeId)
    {
        throw new \Exception("Not implemented");
    }

    public function getPathologyReportsDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getPatientDashboardMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getPatientIDFromTrackingID($sTrackingID)
    {
        throw new \Exception("Not implemented");
    }

    public function getPendingOrdersMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getProblemsListDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getProcedureLabsDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getProviders($neworderprovider_name)
    {
        throw new \Exception("Not implemented");
    }

    public function getRadiologyCancellationReasons()
    {
        throw new \Exception("Not implemented");
    }

    public function getRadiologyOrderChecks($args)
    {
        throw new \Exception("Not implemented");
    }

    public function getRadiologyOrderDialog($imagingTypeId, $patientId)
    {
        throw new \Exception("Not implemented");
    }

    public function getRadiologyReportsDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getRawVitalSignsMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getSurgeryReportsDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getUserSecurityKeys()
    {
        throw new \Exception("Not implemented");
    }

    public function getVisits()
    {
        throw new \Exception("Not implemented");
    }

    public function getVistaAccountKeyProblems()
    {
        throw new \Exception("Not implemented");
    }

    public function getVitalsDetailMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getVitalsDetailOnlyLatestMap()
    {
        throw new \Exception("Not implemented");
    }

    public function getVitalsSummaryMap()
    {
        throw new \Exception("Not implemented");
    }

    public function isProvider()
    {
        throw new \Exception("Not implemented");
    }

    public function setPatientID($sPatientID)
    {
        throw new \Exception("Not implemented");
    }

    public function signNote($newNoteIen, $eSig)
    {
        throw new \Exception("Not implemented");
    }

    public function userHasKeyOREMAS()
    {
        throw new \Exception("Not implemented");
    }

    public function validateEsig($eSig)
    {
        throw new \Exception("Not implemented");
    }

    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle)
    {
        throw new \Exception("Not implemented");
    }

    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ)
    {
        throw new \Exception("Not implemented");
    }

    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ)
    {
        throw new \Exception("Not implemented");
    }

}
