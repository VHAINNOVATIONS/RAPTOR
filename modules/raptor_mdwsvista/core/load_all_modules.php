<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Joel Mewton, et al
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

defined('EHR_INT_IMPL_DAO_NAMESPACE')
    or define('EHR_INT_IMPL_DAO_NAMESPACE', 'raptor_mdwsvista');

defined('EHR_INT_IMPL_DAO_CLASSNAME')
    or define('EHR_INT_IMPL_DAO_CLASSNAME', 'MdwsDao');

require_once 'config.php';
require_once 'MdwsDao.php';
require_once 'MdwsDaoFactory.php';
require_once 'MdwsNewOrderUtils.php';
require_once 'MdwsUserUtils.php';
require_once 'MdwsUtils.php';
