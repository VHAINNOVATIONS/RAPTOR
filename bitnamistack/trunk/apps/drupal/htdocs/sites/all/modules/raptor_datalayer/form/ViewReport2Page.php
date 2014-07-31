<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * 20140516
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");
require_once("ReportPageHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class ViewReport2Page
{

     //Call same function as in EditUserPage here!
    function __construct()
    {
        $this->m_oPageHelper = new \raptor\ReportPageHelper();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();
        $myvalues['formmode'] = 'V';
        //TODO
        return $myvalues;
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        //$form = $this->m_oPageHelper->getForm($form, $form_state, $disabled, $myvalues, $this->m_aHelpText);

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

        $rows = "\n";
        /* TODO
        $sSQL = 'SELECT uid, username, role_nm, accountactive_yn FROM raptor_user_profile ORDER BY username';
        $result = db_query($sSQL);
        foreach($result as $item) 
        {
            if($item->uid != 1)
            {
                $deleteLink = '<a href="javascript:window.location.href=\'/drupal/raptor_datalayer/deleteuser?uid='.$item->uid.'\'">Delete</a>';
            } else {
                $deleteLink = '';
            }
            $rows   .= "\n".'<tr><td>'.$item->username.'</td><td>'.$item->role_nm.'</td><td>'.($item->accountactive_yn==1 ? 'Y' : 'N' ).'</td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/viewuser?uid='.$item->uid.'\'">View</a></td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/edituser?uid='.$item->uid.'\'">Edit</a></td>'
                    .'<td>'.$deleteLink.'</td>'
                    .'</tr>';
        }
         * 
         */

        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table">'
                            . '<thead><tr>'
                            . '<th title="The year of this metric" >Year</th>'
                            . '<th title="The week number of this metric, Jan 1 is week 1" >Week</th>'
                            . '<th title="The name of the user" >User Name</th>'
                            . '<th title="The role of the user in the system" >User Role</th>'
                            . '<th title="The most recent login timestamp" >Most recent login</th>'
                            . '<th title="Total number of tickets moved to Approved state">Total Approved</th>'
                            . '<th title="Total number of tickets moved to Collaboration state">Count Collab</th>'
                            . '<th title="Total number of tickets moved to Acknowledge state">Total Acknowlege</th>'
                            . '<th title="Total number of tickets moved to Complete state">Total Complete</th>'
                            . '<th title="Total number of tickets moved to Suspend state">Total Suspend</th>'
                            . '<th title="Max time a ticket was in Approved state before it was Scheduled">Max Time A no S</th>'
                            . '<th title="Average time tickets were in Approved state before were Scheduled">Avg Time A no S</th>'
                            . '<th title="Max time a ticket was in Approved state before it moved to Completed state">Max Time A to C</th>'
                            . '<th title="Average time tickets were in Accepted state moving to Completed state">Avg Time A to C</th>'
                            . '<th title="Max time a ticket was in Collaboration state">Max Time Collab</th>'
                            . '<th title="Avg time tickets were in Collaboration state">Avg Time Collab</th>'
                            . '<th title="Total number of tickets scheduled">Total Scheduled</th>'
                            . '</tr></thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
       
        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=viewReports">');

        return $form;
    }
    
    
}
