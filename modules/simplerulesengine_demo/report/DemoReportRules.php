<?php
/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
 * Copyright 2014 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 *  
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.gnu.org/copyleft/gpl.html
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 *  
 * This is a simple decision support engine module for Drupal.
 */


namespace simplerulesengine_demo;

module_load_include('php','simplerulesengine_ui','report/ReportRules');
module_load_include('inc','simplerulesengine_demo','core/SREContext');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font
 */
class DemoReportRules extends \simplerulesengine\ReportRules
{

    public function __construct()
    {
        parent::__construct(new \simplerulesengine_demo\SREContext()
                ,   array('return'=>'simplerulesengine_demo/evaluate')
                );
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form = parent::getForm($form, $form_state, $disabled, $myvalues);
        return $form;
    }
    
}
