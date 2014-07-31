<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * 20140503 r1
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class ManageUsersPage
{

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        //drupal_add_js('jQuery(document).ready(function () {alert("testing message")});', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
        drupal_add_js("jQuery(document).ready(function){
            jQuery('.raptor-dialog-table').DataTable({
                'pageLength' : 25
            });
        });", array('type' => 'inline' , 
        'scope' => 'footer' , 
        'weight' => '5'
        ));
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
        $sSQL = 'SELECT uid, usernametitle, lastname, firstname, username, role_nm, accountactive_yn FROM raptor_user_profile ORDER BY username';
        $result = db_query($sSQL);
        foreach($result as $item) 
        {
            if($item->uid != 1 )
            {
                $deleteLink = '<a href="javascript:window.location.href=\'/drupal/raptor_datalayer/deleteuser?uid='.$item->uid.'\'">Delete</a>';
            } else {
                $deleteLink = '';
            }
            $fullname = trim($item->usernametitle . ' ' . $item->lastname . ', ' . $item->firstname);
            $rows   .= "\n".'<tr>'
                    . '<td>'.$item->username.'</td>'
                    . '<td>'.$fullname.'</td>'
                    . '<td>'.$item->role_nm.'</td>'
                    . '<td>'.($item->accountactive_yn==1 ? 'Y' : 'N' ).'</td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/viewuser?uid='.$item->uid.'\'">View</a></td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/edituser?uid='.$item->uid.'\'">Edit</a></td>'
                    .'<td>'.$deleteLink.'</td>'
                    .'</tr>';
        }

        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead><tr>'
                            . '<th>Login name</th>'
                            . '<th>Full name</th>'
                            . '<th>Role</th>'
                            . '<th>Active</th>'
                            . '<th>View</th>'
                            . '<th>Edit</th>'
                            . '<th>Delete</th>'
                            . '</tr>'
                            . '</thead>'
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
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-submit" type="button" value="Add User" ' 
                .'data-redirect="/drupal/raptor_datalayer/adduser" />');
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />');        
        
        return $form;
    }
}
