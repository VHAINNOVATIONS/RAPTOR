<?php
/**
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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'core/data_user');

require_once ("FormHelper.php");
require_once ("UserPageHelper.php");

/**
 * This page allows the user to override their values for ranking purposes.
 * 
 * URL: raptor/editworklistranking
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditWorklistRankingPage
{

    private $m_oContext = NULL;
    private $m_oPageHelper = null;
    private $m_nUID = null;
    
     //Call same function as in EditUserPage here!
    function __construct($nUID)
    {
        if(!isset($nUID) || !is_numeric($nUID))
        {
            die("Missing or invalid uid value = " . $nUID);
        }
        $this->m_nUID = $nUID;
        $this->m_oPageHelper = new \raptor\UserPageHelper();
        $this->m_oContext = \raptor\Context::getInstance();
    }
    
    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    public function getFieldValues()
    {
        $filter = array(":uid" => $this->m_nUID);
        $oUserInfo = $this->m_oContext->getUserInfo();

        $myvalues['resetvalues']['modality'] = $oUserInfo->getModalityPreferences();

        if($oUserInfo->hasModalityPreferencesOverrides())
        {
            $myvalues['userpref_modality'] = $oUserInfo->getModalityPreferencesOverrides();
        } else {
            $myvalues['userpref_modality'] = $oUserInfo->getModalityPreferences();
        }
        
        if($oUserInfo->hasWeightedAnatomyPreferencesOverrides())
        {
            $a = $oUserInfo->getWeightedAnatomyPreferencesOverrides();
            $myvalues['userpref_keywords1'] = $a[0];
            $myvalues['userpref_keywords2'] = $a[1];
            $myvalues['userpref_keywords3'] = $a[2];
        } else {
            $a = $oUserInfo->getWeightedAnatomyPreferences();
            $myvalues['userpref_keywords1'] = $a[0];
            $myvalues['userpref_keywords2'] = $a[1];
            $myvalues['userpref_keywords3'] = $a[2];
        }
        return $myvalues;    
    }
    
    /**
     * Get a clean array of keywords
     * @param string $sArrayKeyName key in the myvalues array
     * @param array $myvalues returned by the form 
     * @return array of uppercase keywords
     */
    private function getCleanKeywordArray($sArrayKeyName, $myvalues)
    {
        $aKeywords = explode(',',$myvalues[$sArrayKeyName]);
        $aClean = array();
        foreach($aKeywords as $sKeyword)
        {
            $sCleanKeyword = strtoupper(trim($sKeyword));
            if($sCleanKeyword > '')
            {
                $aClean[$sCleanKeyword] = $sCleanKeyword;   //This way we remove duplicates from the group.
            }
        }
        return $aClean;
    }
    
    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;  //Make this false if we find something unhappy.
        
        //Special checks here
        $aWGK1 = $this->getCleanKeywordArray('userpref_keywords1',$myvalues);
        $aWGK2 = $this->getCleanKeywordArray('userpref_keywords2',$myvalues);
        $aWGK3 = $this->getCleanKeywordArray('userpref_keywords3',$myvalues);

        //Dont allow 2 if 1 is blank, not allow 3 if 2 is blank.
        if(count($aWGK1) == 0) 
        {
            if(count($aWGK2) > 0) 
            {
                form_set_error('userpref_keywords2','Cannot have keywords in second group when first group is empty');
                $bGood = FALSE;
            } else
            if(count($aWGK3) > 0) 
            {
                form_set_error('userpref_keywords3','Cannot have keywords in third group when second group is empty');
                $bGood = FALSE;
            } 
        } else
        if(count($aWGK2) == 0) 
        {
            if(count($aWGK3) > 0) 
            {
                form_set_error('userpref_keywords3','Cannot have keywords in third group when second group is empty');
                $bGood = FALSE;
            } 
        } 
        foreach($aWGK1 as $kw1)
        {
            if(isset($aWGK2[$kw1]))
            {
                form_set_error('userpref_keywords2','Duplicate keyword "'.$kw1.'" in second group');
                $bGood = FALSE;
            }
            if(isset($aWGK3[$kw1]))
            {
                form_set_error('userpref_keywords3','Duplicate keyword "'.$kw1.'" in third group');
                $bGood = FALSE;
            }
        }
        foreach($aWGK2 as $kw2)
        {
            if(isset($aWGK3[$kw2]))
            {
                form_set_error('userpref_keywords3','Duplicate keyword "'.$kw2.'" in third group');
                $bGood = FALSE;
            }
        }
        
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    public function updateDatabase($form, $form_state)
    {
      
        $myvalues = $form_state['values'];
        $clickedbutton = $form_state['clicked_button'];
        $clickedvalue = $clickedbutton['#value'];

        //use the db_delete then db_insert function to write to raptor_user_anatomy_override and raptor_user_modality_override tables.
        $updated_dt = date("Y-m-d H:i:s", time());

        //First delete the existing values.
        $num_deleted = db_delete('raptor_user_anatomy_override')
          ->condition('uid', $this->m_nUID)
          ->execute();
        $num_deleted = db_delete('raptor_user_modality_override')
          ->condition('uid', $this->m_nUID)
          ->execute();

        //Will we write back any override values?
        if(substr($clickedvalue,0,7) == 'Restore')
        {
            drupal_set_message('Ranking formula restored to default settings');
        } else {
            //Now insert the new override values.
            $aWGK1 = $this->getCleanKeywordArray('userpref_keywords1',$myvalues);
            foreach($aWGK1 as $sKW)
            {
                if($sKW != '')
                {
                      db_insert('raptor_user_anatomy_override')
                      ->fields(array(
                            'uid' =>$this->m_nUID,
                            'weightgroup' => 1,
                            'keyword' => $sKW,
                            'updated_dt' => $updated_dt
                      ))
                      ->execute();
                }
            }
            $aWGK1 = $this->getCleanKeywordArray('userpref_keywords2',$myvalues);
            foreach($aWGK1 as $sKW)
            {
                if($sKW != '')
                {
                      db_insert('raptor_user_anatomy_override')
                      ->fields(array(
                            'uid' =>$this->m_nUID,
                            'weightgroup' => 2,
                            'keyword' => $sKW,
                            'updated_dt' => $updated_dt
                      ))
                      ->execute();
                }
            }
            $aWGK1 = $this->getCleanKeywordArray('userpref_keywords3',$myvalues);
            foreach($aWGK1 as $sKW)
            {
                if($sKW != '')
                {
                      db_insert('raptor_user_anatomy_override')
                      ->fields(array(
                            'uid' =>$this->m_nUID,
                            'weightgroup' => 3,
                            'keyword' => $sKW,
                            'updated_dt' => $updated_dt
                      ))
                      ->execute();
                }
            }

            //--- Insert values into raptor_user_modality_override 
            foreach($myvalues['userpref_modality'] as $key => $value)
            {
              if(isset($value) && $value !== 0)
              {
                db_insert('raptor_user_modality_override')
                          ->fields(array(
                                'uid' =>$this->m_nUID,
                                'modality_abbr' => $value,
                                'updated_dt' => $updated_dt
                          ))
                          ->execute();
              }
            }
            drupal_set_message('Ranking formula changes have now been saved');
        }

        return 1;
    }

    /**
     * @return array of all option values for the form
     */
    private function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $aOptions = $this->m_oPageHelper->getAllOptions();
        $aFormattedKeywordText = $this->m_oPageHelper->formatKeywordText($myvalues);

        //Write the reset values as a javascript accessable json object.
        $form['hiddenstuff'][] = array(
            '#type' => 'hidden',
            '#value' => json_encode($myvalues['resetvalues']),
        );
        $form['hiddenstuff'][] = array(
            '#markup' => "\n<script>\nvar resetvalues=".json_encode($myvalues['resetvalues'])."\n</script>",
        );
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='login-dataentry'>\n",
            '#suffix' => "\n</section>\n",
        );
        
        $form['data_entry_area1']['introblurb'] = array(
            '#markup' => "\n<p>"
                . 'Your worklist preferences affect how entries in the worklist are presented to you.'
                . ' The worklist preferences you identify here cause worklist rows with matching values'
                . ' to have a higher ranking score and thus appear higher in your worklist.'
                . "\n</p>"
                . "\n<p>"
                . 'Every user has their own worklist preferences criteria.  Edit yours as you see fit.'
                . "\n</p>",
        );

        $form['data_entry_area1']['worklistpref'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Worklist Preferences'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $form['data_entry_area1']['worklistpref']['modality'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Modalities'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['worklistpref']['modality']['userpref_modality'] = array(
            '#type' => 'checkboxes',
            '#options' => $aOptions['modality'],
            '#default_value' => $myvalues['userpref_modality'],
            '#title' => t('Modalities'),
            '#description' => t('The modalites for this user'),
            '#disabled' => $disabled,
        );
        
        /*
        //TODO use the 'userpref_modality' member of the resetvalues JSON array to set the controls via javascript 
        $form['data_entry_area1']['worklistpref']['modality']['reset'] = array('#type' => 'item'
                , '#markup' => '<input class="" type="button" value="Reset Modality" >');
        */
        
        $form['data_entry_area1']['worklistpref']['keywords'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Keywords'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['worklistpref']['keywords']['userpref_keywords1'] = array(
          '#type' => 'textfield', 
          '#title' => t('Most Significant'), 
          '#default_value' => $aFormattedKeywordText['userpref_keywords1'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of most significant keywords'),
          '#disabled' => $disabled,
        );        
        $form['data_entry_area1']['worklistpref']['keywords']['userpref_keywords2'] = array(
          '#type' => 'textfield', 
          '#title' => t('Moderately Significant'), 
          '#default_value' => $aFormattedKeywordText['userpref_keywords2'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of moderately significant keywords'),
          '#disabled' => $disabled,
        );        
        $form['data_entry_area1']['worklistpref']['keywords']['userpref_keywords3'] = array(
          '#type' => 'textfield', 
          '#title' => t('Least Significant'), 
          '#default_value' => $aFormattedKeywordText['userpref_keywords3'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of least significant keywords'),
          '#disabled' => $disabled,
        );  
        
        /*
        //TODO use the resetvalues json array to set the controls via javascript
        $form['data_entry_area1']['worklistpref']['keywords']['reset'] = array('#type' => 'item'
                , '#markup' => '<input class="" type="button" value="Reset Keywords" >');
        */
        
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save Ranking Formula Changes')
                //, '#disabled' => $disabled - Should be enabled
            );
        $form['data_entry_area1']['action_buttons']['resetall'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Restore All Default Settings')
                //, '#disabled' => $disabled - Should be enabled
            );
        
        global $base_url;
        $worklist_url = $base_url . '/worklist';
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button"'
                . ' value="Cancel"'
                . ' data-redirect="'.$worklist_url.'">');


        return $form;
    }
}
