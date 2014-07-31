<?php

/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Frank Smith
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 * Updated 20140725
 */

namespace raptor;

/**
 * Helpful utilities.
 *
 * @author FrankWin7VM
 */
class ContraIndUtility 
{
    /**
     * Convert the three state value into something we can print to the user in a message.
     * @param type $bResult one of three states.
     * @return string we can read
     * @throws \Exception
     */
    public static function translateThreeStateResultToText($bResult)
    {
        if($bResult == NULL)
        {
            return 'NULL';
        }
        if($bResult == TRUE)
        {
            return 'TRUE';
        }
        if($bResult == FALSE)
        {
            return 'FALSE';
        }
        throw new \Exception('Cannot translate boolean value [' . $bResult . ']');
    }
}
