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

require_once 'EditListsBasePage.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditListBoilerplateProtocolPage extends EditListsBasePage
{

    private static $reqprivs = array('EBO1'=>1);
    
    function __construct()
    {
        parent::__construct(self::$reqprivs,'raptor_boilerplate_protocol'
            ,array('category_tx','title_tx','content_tx')
            ,array(TRUE, TRUE, TRUE)
            ,array('t','t','t')
            ,array(50,40,250)
            ,array('Category','Title','Snippet')
            ,array('category_tx','title_tx')
            ,array('Prompt users for entry using <b>[&lt;prompt text here&gt;]</b> markers in the Snippet text.')    
            );

        global $base_url;
        $url = $base_url.'/raptor/editboilerplateprotocoloptions';
        $name = 'Edit Protocol Boilerplate Text Options';
        $description = 'These are snippets of boilerplate text that can be selected by button click during protocol workflow phase.';
        $listname = 'Protocol Boilerplate Text';
        
        $this->setName($name);
        $this->setListName($listname);
        $this->setDescription($description);
        $this->setURL($url);
    }
}
