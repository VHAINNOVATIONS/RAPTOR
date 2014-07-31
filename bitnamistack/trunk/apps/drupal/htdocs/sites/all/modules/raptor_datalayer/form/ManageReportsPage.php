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
class ManageReportsPage
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
        
        //CLIN2 1.6
        if($userprivs['VREP1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/viewrepdepact1\'">View Department Activity Report</a></td>'
                      .'<td>Shows activity in the system at a department level.</td>'
                      .'</tr>';
        }
        
        //CLIN2 1.7
        if($userprivs['VREP2'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/viewrepusract1\'">View User Activity Analysis Report</a></td>'
                      .'<td>Shows analysis of user activity in the system.</td>'
                      .'</tr>';
        }

        if($userprivs['SP1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/showroomreservations\'">View Room Reservations Report</a></td>'
                      .'<td>Shows room reservations.</td>'
                      .'</tr>';
        }
        
        if(TRUE)    //TODO check priv
        {
            $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/showuseractivity\'">View User Activity Report</a></td>'
                      .'<td>Shows user activity times.</td>'
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
