<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once("data_utility.php");

/**
 * This checks operational status of RAPTOR elements.
 *
 * @author SAN
 */
class DiagnosticCheck 
{
    function getMySQLStatus()
    {
        //TODO
        return array("Status" => "OK", "Code" => 0, "Message" => "");
    }
    
    function getMDWSStatus()
    {
        //TODO
        return array("Status" => "OK", "Code" => 0, "Message" => "");
    }
    
    function getVIXStatus()
    {
        //TODO
        return array("Status" => "OK", "Code" => 0, "Message" => "");
    }
}
