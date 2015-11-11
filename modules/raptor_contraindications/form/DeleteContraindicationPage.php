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

require_once 'ContraindicationPageHelper.php';

module_load_include('php','simplerulesengine_ui','form/DeleteRulePage');
module_load_include('inc','raptor_contraindications','core/ContraIndEngine');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font
 */
class DeleteContraindicationPage extends \simplerulesengine\DeleteRulePage
{
    public function __construct($rule_nm)
    {
        parent::__construct(
                    $rule_nm
                ,   new \raptor\ContraIndEngine(NULL)
                ,   array('return'=>NULL)
                );
        
        module_load_include('php','raptor_datalayer','core/Context');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(!$oUserInfo->hasPrivilege('ECIR1'))
        {
            throw new \Exception('The user account does not have privileges for this page.');
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        $form = parent::getForm($form, $form_state, $disabled, $myvalues, $html_classname_overrides);
        global $base_url;
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array(
                '#markup' => '<input class="admin-cancel-button" type="button" '
                . ' value="Cancel" '
                . ' data-redirect="'.$base_url.'/raptor/managecontraindications">');
        
         */
        $goback = $base_url.'/raptor/managecontraindications';
        $form['data_entry_area1']['action_buttons']['cancel'] = ContraindicationPageHelper::getExitButtonMarkup($goback);
        return $form;
    }
}
