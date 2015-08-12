<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
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
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
            throw $ex;
        }        
    }
    
    
    public function testGetNotesDetailMap($mydao)
    {
        try
        {
             $mydao->getNotesDetailMap();
        } catch (Exception $ex) {
            throw $ex;
        }        
    }
    
    
    public function testGetVisits($mydao)
    {
        try
        {
             $mydao->getVisits();
        } catch (Exception $ex) {
            throw $ex;
        }        
    }    
    
}
