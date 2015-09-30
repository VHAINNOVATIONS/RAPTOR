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


/**
 * This class returns content for the about page
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewAbout
{
    
    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'core/VistaDao');
        module_load_include('inc', 'raptor_glue', 'core/QualityAssuranceDefs');
    }

    private function getGeneralCustomizationItems($wrapperfirst='<ul>',$itemprefix='<li>',$wrapperlast='</ul>')
    {
        $html = '';
        $items = array();
        $items[] = 'Minimum protocol shortlist size is '.PROTOCOL_SHORTLIST_MIN_SIZE;
        if (!REQUIRE_ACKNOWLEDGE_DEFAULTS)
        {
            $items[] = 'Does NOT require acknowledge default values';
        }
        if (DISABLE_TICKET_AGE1_SCORING)
        {
            $items[] = 'Ticket age scoring1 is disabled';
        }
        if (DISABLE_TICKET_AGE2_SCORING)
        {
            $items[] = 'Ticket age scoring2 is disabled';
        }
        $items[] = 'Default visit days='.DEFAULT_GET_VISIT_DAYS;
        if(count($items) > 0)
        {
            $html = $wrapperfirst 
                    . $itemprefix 
                    . implode($itemprefix, $items)
                    . $wrapperlast;
        }

        return $html;
    }
    
    private function getSiteCustimizationItems($wrapperfirst='<ul>',$itemprefix='<li>',$wrapperlast='</ul>')
    {
        try
        {
            $html = '';
            $items = array();
            $minrowscan = min(WORKLIST_MAXROWS_PER_QUERY * WORKLIST_MAX_QUERY_LOOPS, WORKLIST_ENOUGH_ROWS_COUNT);
            $items[] = "Scans $minrowscan rows to produce the worklist";
            if(OLDEST_WORKLIST_TICKET_ID)
            {
                $items[] = 'Does not look for tickets older than ID=' . OLDEST_WORKLIST_TICKET_ID . ' for the worklist';
            }
            if(count($items) > 0)
            {
                $html = $wrapperfirst 
                        . $itemprefix 
                        . implode($itemprefix, $items)
                        . $wrapperlast;
            }
            return $html;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getWorkflowCustomizationItems($wrapperfirst='<ul>',$itemprefix='<li>',$wrapperlast='</ul>')
    {
        $html = '';
        $items = array();
        if (ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_AP)
        {
            $items[] = 'Has shortcut to QA from Approved';
        }
        if (ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_PA)
        {
            $items[] = 'Has shortcut to QA from Acknowledged';
        }
        if (BLOCK_TICKET_STATE_PA)
        {
            $items[] = 'Disabled Acknowledged ticket state';
        }
        if (BLOCK_TICKET_STATE_EC)
        {
            $items[] = 'Disabled Exam Completed ticket state';
        }
        if(count($items) > 0)
        {
            $html = $wrapperfirst 
                    . $itemprefix 
                    . implode($itemprefix, $items)
                    . $wrapperlast;
        }
        return $html;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state)
    {
        global $base_url;

        $ehr_dao = new \raptor\EhrDao();
        
        $ehr_integrationinfo = $ehr_dao->getIntegrationInfo();
        $logomarkup = "<img style='float:right;' alt='RAPTOR Logo' "
                    . " src='$base_url/sites/all/modules/raptor_glue/images/raptor_large_logo.png'>";
        $html = '<div id="about-dialog" style="margin-left:auto;margin-right:auto;">'
                . '<fieldset>'
                . '<table width="100%">'
                . '<tr>'
                . '<td>'
                . $logomarkup
                . '</td>'
                . '<td style="vertical-align:top">'
                . '<p><b style="font-size: 120%">RAPTOR Configuration and Version Information</b></p>'
                . '<table class="about-info">'
                . '<tr><td><b>App Build:</b></td><td><b>'.RAPTOR_BUILD_ID.'</b></td></tr>'
                . '<tr><td>Machine ID: </td><td>'.RAPTOR_CONFIG_ID.'</td></tr>'
                . '<tr><td>VistA Site: </td><td>'.VISTA_SITE.'</td></tr>'
                . '<tr><td>VistA Integration: </td><td>'.$ehr_integrationinfo.'</td></tr>'
                . '</table>'
                . '<br>'
                . '<b>Site Customization Version Information</b>'
                . '<table class="about-info">'
                . '<tr><td>General:</td>'
                . '<td>'.GENERAL_DEFS_VERSION_INFO
                . $this->getGeneralCustomizationItems()
                . '</td></tr>'
                . '<tr><td>Workflow:</td>'
                . '<td>'.WORKFLOW_DEFS_VERSION_INFO
                . $this->getWorkflowCustomizationItems()
                . '</td></tr>'
                . '<tr><td>General Site Specific Tuning:</td>'
                . '<td>'.RAPTOR_CONFIG_ID
                . $this->getSiteCustimizationItems()
                . '</td></tr>'
                . '<tr><td>Time: </td><td>'.TIME_DEFS_VERSION_INFO.'</td></tr>'
                . '<tr><td>VistA: </td><td>'.VISTA_DEFS_VERSION_INFO.'</td></tr>'
                . '<tr><td>Units of Measure: </td><td>'.UOM_VERSION_INFO.'</td></tr>'
                . '<tr><td>QA Evaluations: </td><td>'.QA_DEFS_VERSION_INFO.'</td></tr>'
                . '</table>'
                . '</td>'
                . '</tr>'
                . '</table>'
                . '</fieldset>'
                . '</div> '
                . '<br><form><center><input class="raptor-dialog-cancel" type="button" value="Close"><center></form>'
                . '<!-- End about-dialog div -->';

        $form = array();
        $form[] = array('#markup' => $html );

        return $form;
    }
}
