<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor;

/**
 * Navigation methods
 *
 * @author Frank Font of SAN Business Consultants
 */
interface PageNavigation 
{
    public function setGobacktoURL($url,$url_urlparams_arr);
    public function getGobacktoFullURL();
    public function getGobacktoURL();
    public function getGobacktoURLParams();
}
