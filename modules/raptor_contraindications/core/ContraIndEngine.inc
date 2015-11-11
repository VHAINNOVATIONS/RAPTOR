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
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 */

namespace raptor;

require_once ('SREContext.inc');
$loaded = module_load_include('inc','simplerulesengine_core','core/SREngine');
if(!$loaded)
{
    drupal_set_message('Failed to load the SREngine class', 'error');
}

/**
 * Functions to determine what contraindications are relevant int the current context.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ContraIndEngine extends \simplerulesengine\SREngine
{
    /**
     *Initialize with all the core information this engine needs. 
     */        
    public function __construct($aPatientInfo=NULL)
    {
        try
        {
            parent::__construct(new \raptor_ci\SREContext(),$aPatientInfo);
        } catch (\Exception $ex) {
            throw new \Exception('Failed to create an instance of ContraIndEngine because '.$ex->getMessage(),99901,$ex);
        }
    }
}

