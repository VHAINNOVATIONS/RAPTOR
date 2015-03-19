<?php
/**
 * @file
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


namespace simplerulesengine;

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font
 */
class ReportInputs
{

    protected $m_oSREContext = NULL;
    protected $m_urls_arr = NULL;
    protected $m_rule_classname = NULL;
    
    public function __construct($oSREContext, $urls_arr=NULL, $rule_classname=NULL)
    {
        $this->m_oSREContext = $oSREContext;
        $this->m_urls_arr = $urls_arr;
        $this->m_rule_classname = $rule_classname;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {

        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='simplerulesengine-report'>\n",
            '#suffix' => "\n</section>\n",
        );
        
        $form['data_entry_area1']['top_blurb']['p1'] = array('#type' => 'item'
                , '#markup' => '<p>The measures listed as "database" or "db:measure" are configured in the database.  The items listed as "coded" are implemented by programmers in the codebase.  The "database" configured measures can be customized by changing configuration expressions, the "coded" items cannot.</p>');
        $form['data_entry_area1']['top_blurb']['p2'] = array('#type' => 'item'
                , '#markup' => '<p>The measures listed here can be used in measure formulas, but only boolean measures ("flags") can be used in Rule formulas.  The boolean measures are shown in this report as having value type "boolean".  A boolean measure can have one of three values "True","False", or "Null".  A "Null" value occurs when input criteria for a measure is not available or is unknown at evaluation time.</p>');
        
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="simplerulesengine-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $rows = "\n";
        $aAllInputs = $this->m_oSREContext->getDictionary()->getActiveRuleInputs();
        foreach($aAllInputs as $aInputs)
        {
            $sLocked = isset($aInputs['locked']) && $aInputs['locked'] == 1 ? 'Yes' : 'No';
            $return_type = $aInputs['return'];
            if($return_type > '')
            {
                $rtparts = explode(' ',$return_type);
                $rtclassmarkup = ' class="'.$rtparts[0].'-measure" ';
            } else {
                $rtclassmarkup = '';
            }
            $rows   .= "\n".'<tr>'
                    . '<td>'.$aInputs['source'].'</td>'
                    . '<td>'.$sLocked.'</td>'
                    . '<td><span '.$rtclassmarkup.'>'.$return_type.'</span></td>'
                    . '<td>'.$aInputs['category_nm'].'</td>'
                    . '<td>'.$aInputs['name'].'</td>'
                    . '<td>'.$aInputs['purpose_tx'].'</td>'
                    . '<td>'.$aInputs['criteria_tx'].'</td>'
                    .'</tr>';
        }

        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table id="my-simplerulesengine-dialog-table" class="simplerulesengine-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th>Implementation Location</th>'
                            . '<th>Locked</th>'
                            . '<th>Value Type</th>'
                            . '<th>Category Name</th>'
                            . '<th>Name</th>'
                            . '<th>Purpose</th>'
                            . '<th>Formula</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            . '</tbody>'
                            . '</table>');
        
        
        $form['data_entry_area1']['bottom_blurb'] 
                = array(
            '#markup' => "\n<!-- bottom blurb area -->\n",
        );
        
        if(is_array($this->m_urls_arr))
        {
            $form['data_entry_area1']['action_buttons'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="simplerulesengine-action-buttons">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                    , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                    , '#value' => t('Refresh Report'));

            global $base_url;
            if(isset($this->m_urls_arr['return']))
            {
                $returnURL = $base_url . '/'. $this->m_urls_arr['return'];
                $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                        , '#markup' => '<a class="cancel-button" href="'.$returnURL.'" >Exit</a>');
            }
        }
        
        return $form;
    }
}
