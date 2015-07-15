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

/**
 * This is the primary interface abstraction to EHR worklist column positions
 *
 * @author Frank Font of SAN Business Consultants
 */
class WorklistColumnMap
{
    //RAPTOR Worklist Field Index
    const WLIDX_TRACKINGID = 0;
    const WLIDX_PATIENTID = 1;
    const WLIDX_PATIENTNAME = 2;
    const WLIDX_DATETIMEDESIRED = 3;
    const WLIDX_DATEORDERED = 4;
    const WLIDX_MODALITY = 5;
    const WLIDX_STUDY = 6;
    const WLIDX_URGENCY = 7;
    const WLIDX_TRANSPORT = 8;
    const WLIDX_PATIENTCATEGORYLOCATION = 9;
    const WLIDX_ANATOMYIMAGESUBSPEC = 10;
    const WLIDX_WORKFLOWSTATUS = 11;
    const WLIDX_ASSIGNEDUSER = 12;
    const WLIDX_ORDERSTATUS = 13;
    const WLIDX_EDITINGUSER = 14;
    const WLIDX_SCHEDINFO = 15;
    const WLIDX_CPRSCODE = 16;
    const WLIDX_IMAGETYPE = 17;
    const WLIDX_RANKSCORE = 18;
    const WLIDX_COUNTPENDINGORDERSSAMEPATIENT = 19;    
    const WLIDX_MAPPENDINGORDERSSAMEPATIENT = 20;    
    const WLIDX_EXAMLOCATION = 21;
    const WLIDX_REQUESTINGPHYSICIAN = 22;
    const WLIDX_NATUREOFORDERACTIVITY = 23;
    const WLIDX_ORDERFILEIEN = 24;
    const WLIDX_RADIOLOGYORDERSTATUS = 25;
    const WLIDX_ISO8601_DATETIMEDESIRED = 26;
    const WLIDX_ISO8601_DATEORDERED = 27;
}
