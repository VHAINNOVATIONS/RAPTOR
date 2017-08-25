<?php
/**
 * @file
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
require_once ("FormHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ManageUsersPage
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
       
        global $base_url;

        $rows = "\n";
        $sSQL = 'SELECT uid, usernametitle, lastname, firstname, username, role_nm, accountactive_yn '
                . ' FROM raptor_user_profile '
                . ' ORDER BY username';
        $result = db_query($sSQL);
        foreach($result as $item) 
        {
            if($item->uid != 1 )
            {
                $deleteLink = '<a href="'.$base_url.'/raptor/deleteuser?uid='.$item->uid.'">Delete</a>';
            } else {
                $deleteLink = '';
            }
            $fullname = trim($item->usernametitle . ' ' . $item->lastname . ', ' . $item->firstname);
            $rows   .= "\n".'<tr>'
                    . '<td>'.$item->username.'</td>'
                    . '<td>'.$fullname.'</td>'
                    . '<td>'.$item->role_nm.'</td>'
                    . '<td>'.($item->accountactive_yn==1 ? 'Y' : 'N' ).'</td>'
                    .'<td><a href="'.$base_url.'/raptor/viewuser?uid='.$item->uid.'">View</a></td>'
                    .'<td><a href="'.$base_url.'/raptor/edituser?uid='.$item->uid.'">Edit</a></td>'
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
                .'data-redirect="'.$base_url.'/raptor/adduser" />');
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />');        
        
        return $form;
    }
}
