<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 *
 * Updated 20130323 FJF
 */

namespace raptor;

require_once("data_worklist.php");

/**
 * The dashboard is key information about the currently selected
 * patient and their procedure.
 *
 * @author SAN
 */
class DashboardData 
{
    /**
     * Get the boilerplate details for the requested row
     * @param type $sTrackingID
     */
    function getDashboardDetails($oContext)
    {
        $wl = new WorklistData($oContext);
        return $wl->getDashboardData();

//        //TODO get the dashboard contents for the row requested
//        return array("TrackingID" => $sTrackingID
//            ,"CaseID" => "123-ABCD"
//            ,"Procedure" => "MAGNETIC IMAGE CHEST (HILAR/MEDIASTINAL/LYMPHADENOPATHY)"
//            ,"ExamCategory"=> "Image"
//            ,"PatientLocation"=> "Rm1234"
//            ,"RequestedBy"=>"Dr. Primaracara (555) 555-5555"
//            ,"RequestedDate"=>"1/5/2014"
//            ,"ScheduledDate"=>"1/5/2014 @ 15:30"
//            ,"PatientCategory"=>"InPatient"
//            ,"ReasonForStudy"=>"Muscle Wasting"
//            ,"ClinicalHistory"=>"Anorexia"
//            ,"PatientSSN"=>"555-55-5555"
//            ,"Urgency"=>"STAT"
//            ,"Transport"=>"TODO"
//            ,"PatientName"=>"Omiah, Jebediah"
//            ,"PatientAge"=>"63"
//            ,"PatientDOB"=>"1/1/1901"
//            ,"PatientEthnicity"=>"White, not of hispanic origin"
//            ,"PatientGender"=>"M"
//            ,"ImageType"=>"CT Scan"
//            );
    }
}
