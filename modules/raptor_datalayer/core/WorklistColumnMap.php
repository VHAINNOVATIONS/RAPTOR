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
