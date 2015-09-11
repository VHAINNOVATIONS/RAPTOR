<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * EWD Integration and VISTA collaboration: Joel Mewton, Rob Tweed
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */ 

namespace raptor_ewdvista;

require_once 'EwdUtils.php';

/**
 * Helper for returning dashboard content
 *
 * @author Frank Font of SAN Business Consultants
 */
class DashboardHelper
{
    public function getFormatted($tid, $pid, $radiologyOrder, $orderFileRec, $therow, $aPatientData)
    {
/*
error_log("LOOK parts radiologyOrder=".print_r($radiologyOrder,TRUE));
error_log("LOOK parts order=".print_r($orderFileRec,TRUE));
error_log("LOOK parts therow=".print_r($therow,TRUE));
error_log("LOOK parts oPatientData=".print_r($aPatientData,TRUE));
*/        
        try
        {
            $dashboard = array();
            $dashboard['Tracking ID'] = $tid;
            $dashboard['PatientID']   = $pid;
            
            $dashboard['Urgency']   = $radiologyOrder[6]['E'];
            $dashboard['OrderFileIen'] = $radiologyOrder[7]['I'];

            $dashboard['orderFileStatus'] = $orderFileRec['5']['E'];
            $dashboard['orderActive'] = !key_exists('63', $orderFileRec);

            $dashboard['Procedure']             = $therow[\raptor\WorklistColumnMap::WLIDX_STUDY];
            $dashboard['Modality']              = $therow[\raptor\WorklistColumnMap::WLIDX_MODALITY];
            $dashboard['ExamCategory']          = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION];
            $dashboard['PatientLocation']       = $therow[\raptor\WorklistColumnMap::WLIDX_EXAMLOCATION]; //DEPRECATED 1/29/2015      
            $dashboard['RequestedBy']           = $therow[\raptor\WorklistColumnMap::WLIDX_REQUESTINGPHYSICIAN];
            $dashboard['RequestedDate']         = $therow[\raptor\WorklistColumnMap::WLIDX_DATEORDERED]; 
            $dashboard['DesiredDate']           = $therow[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED]; 

            $dashboard['PatientCategory']       = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION];
            $dashboard['NatureOfOrderActivity'] = $therow[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY];
            
            $dashboard['Urgency']           = $therow[\raptor\WorklistColumnMap::WLIDX_URGENCY];
            $dashboard['Transport']         = $therow[\raptor\WorklistColumnMap::WLIDX_TRANSPORT];
            $dashboard['PatientName']       = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTNAME];
            $dashboard['ImageType']         = $therow[\raptor\WorklistColumnMap::WLIDX_IMAGETYPE];
            
            $dashboard['MapPendingOrders']     = $therow[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT];
            
            $dashboard['RadiologyOrderStatus']     = $therow[\raptor\WorklistColumnMap::WLIDX_RADIOLOGYORDERSTATUS];

            $aSchedInfo                     = $therow[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO];
            $dashboard['SchedInfo']         = $aSchedInfo;
            $dashboard['ScheduledDate']     = $aSchedInfo['EventDT'];
            
            $dashboard['PatientSSN']        = \raptor_ewdvista\EwdUtils::convertFromVistaSSN($aPatientData['ssn']);
            $dashboard['PatientAge']        = $aPatientData['age'];
            $dashboard['PatientDOB']        = $aPatientData['dob'];
            $dashboard['PatientEthnicity']  = $aPatientData['ethnicity'];
            $dashboard['PatientGender']     = $aPatientData['gender'];
            $dashboard['mpiPid']            = $aPatientData['mpiPid'];
            $dashboard['mpiChecksum']       = $aPatientData['mpiChecksum'];
            
            $dashboard['orderingPhysicianDuz']  = $radiologyOrder['14']['I']; // get internal value of ordering provider field
            $dashboard['canOrderBeDCd']         = $radiologyOrder['5']['I'] == '5' || $radiologyOrder['5']['I'] == '11';
            $dashboard['RequestingLocation']    = trim((isset($radiologyOrder['22']['I']) ? $radiologyOrder['22']['I'] : '') );
            $dashboard['SubmitToLocation']      = trim((isset($radiologyOrder['20']['I']) ? $radiologyOrder['20']['I'] : '') );
            $dashboard['ReasonForStudy']        = trim((isset($radiologyOrder['1.1']['I']) ? $radiologyOrder['1.1']['I'] : '') );
            $dashboard['RequestingLocation']    = trim((isset($radiologyOrder['22']['E']) ? $radiologyOrder['22']['E'] : '') );
            $dashboard['RequestingLocationIen'] = trim((isset($radiologyOrder['22']['I']) ? $radiologyOrder['22']['I'] : '') );
            $dashboard['ClinicalHistory']       = isset($radiologyOrder['400']) ? $radiologyOrder['400'] : '';
            
            return $dashboard;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
