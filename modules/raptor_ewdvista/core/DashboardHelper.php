<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor_ewdvista;

/**
 * Helper for returning dashboard content
 *
 * @author Frank Font of SAN Business Consultants
 */
class DashboardHelper
{
    public function getFormatted($tid, $pid, $radiologyOrder, $orderFileRec, $therow, $aPatientData)
    {
error_log("LOOK parts radiologyOrder=".print_r($radiologyOrder,TRUE));
error_log("LOOK parts order=".print_r($orderFileRec,TRUE));
error_log("LOOK parts therow=".print_r($therow,TRUE));
error_log("LOOK parts oPatientData=".print_r($aPatientData,TRUE));
        
        try
        {
            $dashboard = array();
            $dashboard['Tracking ID'] = $tid;
            //$dashboard['Procedure'] = $radiologyOrder[2]['E'];
            $dashboard['ImageType'] = $radiologyOrder[3]['E'];
            $dashboard['PatientName'] = $radiologyOrder['.01']['E'];
            //$dashboard['RequestedBy'] = $radiologyOrder[14]['E'];
            $dashboard['PatientCategory'] = $radiologyOrder[4]['E'];
            $dashboard['Urgency']   = $radiologyOrder[6]['E'];
            $dashboard['PatientID'] = $radiologyOrder[7]['E'];       //NOT SURE
            $dashboard['OrderFileIen'] = $radiologyOrder[7]['E'];    //NOT SURE

            $dashboard['orderFileStatus'] = $orderFileRec['5']['E'];
            $dashboard['orderActive'] = !key_exists('63', $orderFileRec);

            //['orderingPhysicianDuz'] = $worklistItemDict['14']['I'];

            $dashboard['Procedure']             = $therow[\raptor\WorklistColumnMap::WLIDX_STUDY];
            $dashboard['Modality']              = $therow[\raptor\WorklistColumnMap::WLIDX_MODALITY];
            $dashboard['ExamCategory']          = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION];
            $dashboard['PatientLocation']       = $therow[\raptor\WorklistColumnMap::WLIDX_EXAMLOCATION]; //DEPRECATED 1/29/2015      
            $dashboard['RequestedBy']           = $therow[\raptor\WorklistColumnMap::WLIDX_REQUESTINGPHYSICIAN];

            $dashboard['RequestedDate']         = $therow[\raptor\WorklistColumnMap::WLIDX_DATEORDERED]; 
            $dashboard['DesiredDate']           = $therow[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED]; 

            $dashboard['PatientCategory']       = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION];
            $dashboard['NatureOfOrderActivity'] = $therow[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY];
            
            $dashboard['PatientID']         = $pid;
            $dashboard['PatientSSN']        = self::formatSSN($aPatientData['ssn']);
            $dashboard['Urgency']           = $therow[\raptor\WorklistColumnMap::WLIDX_URGENCY];
            $dashboard['Transport']         = $therow[\raptor\WorklistColumnMap::WLIDX_TRANSPORT];
            $dashboard['PatientName']       = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTNAME];
            $dashboard['PatientAge']        = $aPatientData['age'];
            $dashboard['PatientDOB']        = $aPatientData['dob'];
            $dashboard['PatientEthnicity']  = $aPatientData['ethnicity'];
            $dashboard['PatientGender']     = $aPatientData['gender'];
            $dashboard['ImageType']         = $therow[\raptor\WorklistColumnMap::WLIDX_IMAGETYPE];
            $dashboard['mpiPid']            = $aPatientData['mpiPid'];
            $dashboard['mpiChecksum']       = $aPatientData['mpiChecksum'];
            
            //$orderFileIen = $worklistItemDict['7']['I'];
            
            $dashboard['orderingPhysicianDuz'] = $radiologyOrder['14']['I']; // get internal value of ordering provider field
            $dashboard['canOrderBeDCd'] = $worklistItemDict['5']['I'] == '5' || $radiologyOrder['5']['I'] == '11';
            $dashboard['RequestingLocation']= trim((isset($radiologyOrder['22']['I']) ? $radiologyOrder['22']['I'] : '') );
            $dashboard['SubmitToLocation']  = trim((isset($radiologyOrder['20']['I']) ? $radiologyOrder['20']['I'] : '') );
            $dashboard['ReasonForStudy']    = trim((isset($radiologyOrder['1.1']['I']) ? $radiologyOrder['1.1']['I'] : '') );
            $dashboard['RequestingLocation'] = trim((isset($radiologyOrder['22']['E']) ? $radiologyOrder['22']['E'] : '') );
            $dashboard['RequestingLocationIen'] = trim((isset($radiologyOrder['22']['I']) ? $radiologyOrder['22']['I'] : '') );
            $dashboard['ClinicalHistory']   = trim((isset($radiologyOrder['400']) ? $radiologyOrder['400'] : '') );
            
            
            return $dashboard;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function formatSSN($digits)
    {
        if($digits != NULL && strlen($digits) == 9)
        {
            return $digits[0] . $digits[1] . $digits[2] 
                    . '-' . $digits[3] . $digits[4] 
                    . '-' . $digits[5] . $digits[6] . $digits[7] . $digits[8];
        }
        return $digits;
    }
}
