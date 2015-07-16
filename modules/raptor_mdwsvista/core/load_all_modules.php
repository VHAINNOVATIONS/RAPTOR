<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

defined('EHR_INT_IMPL_DAO_CLASSNAME')
    or define('EHR_INT_IMPL_DAO_CLASSNAME', 'MdwsDao');

require_once 'config.php';
require_once 'MdwsDao.php';
require_once 'MdwsDaoFactory.php';
require_once 'MdwsNewOrderUtils.php';
require_once 'MdwsUserUtils.php';
require_once 'MdwsUtils.php';
