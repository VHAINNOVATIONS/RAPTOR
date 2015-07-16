<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * EWDJS Integration collaboration: Rob Tweed
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 * The core VistA integration functions that are required by RAPTOR
 * 
 */ 

namespace raptor;

module_load_include('php', 'raptor_datalayer', 'core/IVistaDao');

interface IEwdVistaDao extends IEhrDao{}
