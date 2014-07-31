<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * 20140503 r1
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once('FormHelper.php');

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class ManageListsPage
{

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();

        
        $rows = "\n";
        if($userprivs['ELHO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/edithydrationoptions\'">Edit Hydration Options</a></td>'
                      .'<td>Hydration options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['ELSO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/editsedationoptions\'">Edit Sedation Options</a></td>'
                      .'<td>Sedation options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['ELCO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/editcontrastoptions\'">Edit Contrast Options</a></td>'
                      .'<td>Contrast options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['ELRO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/editradioisotopeoptions\'">Edit Radioisotope Options</a></td>'
                      .'<td>Radioisotope options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['EERL1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/editexamroomoptions\'">Edit Examination Room Options</a></td>'
                      .'<td>Exam room options are selectable during the scheduling process.</td>'
                      .'</tr>';
        }
        if($userprivs['EARM1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/editatriskmeds\'">Edit At Risk Medications List</a></td>'
                      .'<td>These keywords are used to highlight medical history of a patient.</td>'
                      .'</tr>';
        }
        $form["data_entry_area1"]['table_container']['lists'] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table">'
                            . '<thead><tr><th>Action</th><th>Description</th></tr></thead>'
                            . '<tbody>'
                            . $rows
                            . '</tbody>'
                            . '</table>');
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
       
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />');        

        return $form;
    }
}
