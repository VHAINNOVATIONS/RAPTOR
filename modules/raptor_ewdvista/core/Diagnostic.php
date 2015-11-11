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

/**
 * This is the primary interface implementation to diagnostic code
 *
 * @author Frank Font of SAN Business Consultants
 */
class Diagnostic
{

    /**
     * Returns a DAO instance if configured properly.
     * If you get non-null result, then success!
     */
    public function testCreateDao()
    {
        try
        {
            $mydao = new \raptor_ewdvista\EwdDao();
            return $mydao;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns a DAO instance if configured properly.
     * If you get non-null result, then success!
     */
    public function testInitDao()
    {
        try
        {
            //TODO
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    /*
     * should instantialte relevantproperties base on the following JSON responce 
     * {
     *  DT: "3150721"
     *  DUZ: "10000000344"
     *  displayName: "SEVEN RADIOLOGIST"
     *  greeting: "Good morning RADIOLOGIST,SEVEN"
     *  username: "RADIOLOGIST,SEVEN"
     *  }
     */
    public function testLogin($mydao,$username,$password)
    {
        try
        {
            $siteCode = "TODOSITECODE";
            $mydao->connectAndLogin($siteCode, $username, $password);
        } catch (\Exception $ex) {
            throw $ex;
        }        
    }
    
    
    public function testGetNotesDetailMap($mydao)
    {
        try
        {
             $mydao->getNotesDetailMap();
        } catch (\Exception $ex) {
            throw $ex;
        }        
    }
    
    
    public function testGetVisits($mydao)
    {
        try
        {
             return $mydao->getVisits();
        } catch (\Exception $ex) {
            throw $ex;
        }        
    }    
    
    public function testGetChemHemLabs($mydao)
    {
        try
        {
             return $mydao->getChemHemLabs();
        } catch (\Exception $ex) {
            throw $ex;
        }        
    }          
}
