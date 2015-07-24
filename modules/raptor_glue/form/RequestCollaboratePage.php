<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 


namespace raptor;

require_once 'FormHelper.php';

/**
 * Choose a collaboration target
 *
 * @author Frank Font of SAN Business Consultants
 */
class RequestCollaboratePage
{
    private $m_oContext = null;
    private $m_oTT = null;

    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'config/Choices');
        //module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
        module_load_include('php', 'raptor_datalayer', 'core/UserInfo');
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $tid = $this->m_oContext->getSelectedTrackingID();
        
        //$oWL = new \raptor\WorklistData($this->m_oContext);
        //$aOneRow = $oWL->getDashboardMap();    //$tid);
        $ehrDao = $this->m_oContext->getEhrDao();
        $aOneRow = $ehrDao->getDashboardDetailsMap();
        $nSiteID = $this->m_oContext->getSiteID();
        
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        
        $myvalues = array();
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        
        $result = db_select('raptor_ticket_collaboration','c')
                ->fields('c')
                ->condition('siteid',$nSiteID,'=')
                ->condition('IEN',$nIEN,'=')
                ->condition('active_yn',1,'=')
                ->execute();
        if($result->rowCount() == 0)
        {
            //Initialize all values as empty.
            $myvalues['prev_collaborator_uid'] = '';
            $myvalues['collaborator_uid'] = '';     //This gets filled in by javascript
            $myvalues['requester_prev_notes_tx'] = '';
            $myvalues['requester_notes_tx'] = '';
        } else {
            //Initialize all values with what we found.
            $record = $result->fetchAssoc();
            $myvalues['prev_collaborator_uid'] = $record['collaborator_uid'];
            $myvalues['collaborator_uid'] = $record['collaborator_uid'];
            $myvalues['requester_prev_notes_tx'] = $record['requester_notes_tx'];
            $myvalues['requester_notes_tx'] = '';
        }

        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        if(!isset($myvalues['collaborator_uid']) || !is_numeric($myvalues['collaborator_uid']))
        {
            $msg = 'ERROR look collaboration form failed because no collaborator_uid was provided>>>'.print_r($myvalues,TRUE);
            error_log($msg);
            throw new \Exception($msg);
        }
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     * @deprecated since we are submitting the parent form instead!
     */
    function updateDatabase($form, $myvalues)
    {
        die('Submit the parent form instead of just the values on this collaborate form!');
    }
    
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    private function getTable($form, &$form_state, $disabled, $myvalues)
    {
        $rows = "\n";
        $sSQL = 'SELECT'
                . ' uid, username, usernametitle, firstname, lastname, suffix, prefemail, prefphone, role_nm'
                . ' FROM raptor_user_profile'
                . ' WHERE accountactive_yn=1'
                . ' ORDER BY role_nm, username';
        $result = db_query($sSQL);
        $nSpecialistCount = 0;

        foreach($result as $item) 
        {
            $uid = $item->uid;
            $fullname = $item->usernametitle . ' ' . $item->firstname . ' ' . $item->lastname . ' ' . $item->suffix;
            $bSelected = ($myvalues['collaborator_uid'] == $uid);
            if($bSelected)
            {
                $sSelected = ' checked="checked" ';
            } else {
                $sSelected = ' ';
            }
            
            //Get the modalities.
            $sSQL = 'SELECT modality_abbr'
                    . ' FROM raptor_user_modality'
                    . ' WHERE specialist_yn=1 and uid='.$uid
                    . ' ORDER BY modality_abbr';
            $modalities = '';
            $modalityResult = db_query($sSQL);

            foreach($modalityResult as $localitem) 
            {
                $modalities .= ' ' . $localitem->modality_abbr;
            }
            
            //Get the anatomy keywords.
            $sSQL = 'SELECT keyword, weightgroup'
                    . ' FROM raptor_user_anatomy'
                    . ' WHERE specialist_yn=1 and uid='.$uid
                    . ' ORDER BY keyword';
            $anatomyItems = '';
            $anatomyResult = db_query($sSQL);

            foreach($anatomyResult as $localitem) 
            {
                if($localitem->weightgroup == 1)
                {
                    $expertLevel = 'high significance';
                    $anatomyItems .= ' <span title="'.$expertLevel.'"><strong>' . $localitem->keyword.'</strong></span>';
                } else
                if($localitem->weightgroup == 1)
                {
                    $expertLevel = 'moderate significance';
                    $anatomyItems .= ' <span title="'.$expertLevel.'">' . $localitem->keyword.'</span>';
                } else {
                    $expertLevel = 'low significance';
                    $anatomyItems .= ' <span title="'.$expertLevel.'">' . $localitem->keyword.'</span>';
                }
            }

            if($anatomyItems != '')
            {
                if($bSelected)
                {
                    $fullname = '<strong>'.$fullname.'</strong>';
                }
                $rows   .= "\n".'<tr>'
                        . '<td><input type="radio" name="group_collaborator_uid" value="'.$item->uid.'" '
                        . $sSelected
                        . ' onclick="copyValueFromSourceToTarget(this,collaborator_uid)" ></td>'
                        . '<td title="'. \raptor\UserInfo::getMaskedText($item->username).'">' 
                            . $fullname
                        .'</td>'
                        . '<td>'.$item->role_nm.'</td>'
                        . '<td>'.$modalities.'</td>'
                        . '<td>'.$anatomyItems.'</td>'
                        . '<td>'.$item->prefphone.'</td>'
                        . '<td>'.$item->prefemail.'</td>'
                        . '</tr>';
                $nSpecialistCount++;
            }
        }

        $elements[] = array('#type' => 'item',
                 '#markup' => '<p>Total of ' . $nSpecialistCount . ' users are available for collaboration</p>');
        $elements[] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th></th>'
                            . '<th>Name</th>'
                            . '<th>Role</th>'
                            . '<th>Modality Specialties</th>'
                            . '<th>Anatomy Specialties</th>'
                            . '<th>Phone</th><th>Email</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            . '</tbody>'
                            . '</table>');
        return $elements;
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $javascript = 'copyValueFromSourceToTarget(prev_collaborator_uid,collaborator_uid);';
        drupal_add_js('jQuery(document).ready(function () {'.$javascript.'});', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));

        $form['data_entry_area1']['toppart']['availableusers'] = $this->getTable($form, $form_state, $disabled, $myvalues);

        $form['hidden_constant_things']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hidden_constant_things']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);
        $form['hidden_constant_things']['prev_collaborator_uid'] = array('#type' => 'hidden', '#value' => $myvalues['prev_collaborator_uid']);
        $form['hidden_volatile_things']['collaborator_uid'] = array('#type' => 'hidden'); //IMPORTANT DO NOT SET A DEFAULT VALUE!!!, '#default_value' => 'NOBODY');

        if($myvalues['requester_prev_notes_tx'] > '')
        {
            $prevnote = trim($myvalues['requester_prev_notes_tx']);
            $form['data_entry_area1']['toppart']['requester_prev_notes_tx']['intro'] 
                    = array(
                        '#markup' => 
                          '<div id="requester-prev-notes-intro" class="previous-notes-intro">Previous Collaboration Note</div>',
                        );
            $form['data_entry_area1']['toppart']['requester_prev_notes_tx']['text'] 
                    = array(
                        '#markup' => 
                          '<div id="requester-prev-notes-tx" class="previous-notes-tx">'.$prevnote.'</div>',
                        );
        }
        
        $form['data_entry_area1']['toppart']['requester_notes_tx'] = array(
            '#type'          => 'textarea',
            '#title'         => t('Collaboration Request Notes'),
            //'#required'      => TRUE,
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['requester_notes_tx'],
        );

       $form["data_entry_area1"]['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
        $collaborateTip = 'Save the current protocol ticket marked as requesting collaboration';
        $form['data_entry_area1']['action_buttons']['collaborate_button'] = 
                array('#markup' 
                    => '<input id="request-raptor-protocol-collaboration" type="button" value="Request Collaboration with Selected RAPTOR User" title="'.$collaborateTip.'">');
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel">');
        
        return $form;
    }
}

