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

require_once 'RulePageHelper.php';

/**
 * This class creates page to create a rule
 *
 * @author Frank Font of SAN Business Consultants
 */
class AddRulePage
{
    
    protected $m_oSREngine = NULL;
    protected $m_oSREContext = NULL;
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_rule_classname = NULL;
   
    public function __construct($srengine
            , $urls_arr
            , $rule_classname=NULL)
    {
        $this->m_oSREngine = $srengine;
        $this->m_oSREContext = $srengine->getSREContext();
        $this->m_urls_arr = $urls_arr;
        $this->m_rule_classname = $rule_classname;
        $this->m_oPageHelper = 
                new \simplerulesengine\RulePageHelper($srengine
                        , $urls_arr
                        , $rule_classname);
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();

        //create a NULL value for each of the indexes in the myvalues array.
        $myvalues['rule_nm'] = NULL;
        $myvalues['category_nm'] = NULL;
        $myvalues['version'] = NULL;
        $myvalues['active_yn'] = NULL;
        $myvalues['explanation'] = NULL;
        $myvalues['summary_msg_tx'] = NULL;
        $myvalues['msg_tx'] = NULL;
        $myvalues['readonly_yn'] = NULL;
        $myvalues['req_ack_yn'] = NULL;
        $myvalues['trigger_crit'] = NULL;
        return $myvalues;
    }
    
    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->looksValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $aConsolidation = $this->m_oPageHelper->getConsolidatedExpression($myvalues);
        $sExpression = $aConsolidation['expression'];

        $updated_dt = date("Y-m-d H:i", time());
        $rule_tablename = $this->m_oSREContext->getRuleTablename();
        try
        {
          if(!isset($myvalues['readonly_yn']))
          {
              $myvalues['readonly_yn'] = 0;
          }
          db_insert($rule_tablename)
            ->fields(array(
                  'rule_nm' => strtoupper($myvalues['rule_nm']),
                  'category_nm' => $myvalues['category_nm'],
                  'version' => $myvalues['version'],
                  'active_yn' => $myvalues['active_yn'],
                  'explanation' => $myvalues['explanation'],
                  'msg_tx' => $myvalues['msg_tx'],
                  'summary_msg_tx' => $myvalues['summary_msg_tx'],
                  'readonly_yn' => $myvalues['readonly_yn'],
                  'req_ack_yn' => $myvalues['req_ack_yn'],
                  'trigger_crit' => $sExpression,
                  'updated_dt' => $updated_dt,
              ))
                ->execute(); 
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add ' . $myvalues['rule_nm']
                      . ' rule because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
      
        //If we are here then we had success.
        if($this->m_rule_classname !== NULL)
        {
            $msg = 'Added '.$this->m_rule_classname.' ' . $myvalues['rule_nm'];
        } else {
            $msg = 'Added ' . $myvalues['rule_nm'];
        }
        drupal_set_message($msg);
        return 1;

    }
    
    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['action-buttons'] = 'action-buttons';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        $form = $this->m_oPageHelper->getForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['action-buttons'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Add Rule'));
 
        global $base_url;
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $base_url . '/'. $this->m_urls_arr['return'];
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => '<a class="'.$html_classname_overrides['action-button'].'" href="'.$returnURL.'" >'.t('Cancel').'</a>');
        }
        
        return $form;
    }
}
