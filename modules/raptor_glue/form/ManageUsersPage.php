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
require_once 'FormHelper.php';
require_once 'UserPageHelper.php';


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
        global $base_url;
        $oPageHelper = new \raptor\UserPageHelper();
        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        
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
        $sSQL = 'SELECT uid, usernametitle, lastname, firstname, username, role_nm, accountactive_yn '
                . ' FROM raptor_user_profile '
                . ' ORDER BY username';
        $result = db_query($sSQL);
        foreach($result as $item) 
        {
            //Can we view this user profile?
            if($oPageHelper->checkAllowedToViewUser($oContext, $item->uid, FALSE))    
            {
                if($oPageHelper->checkAllowedToDeleteUser($oContext, $item->uid, FALSE))
                {
                    $deleteLink = '<a href="'.$base_url.'/raptor/deleteuser?uid='.$item->uid.'">Delete</a>';
                } else {
                    $deleteLink = '';
                }
                if($oPageHelper->checkAllowedToEditUser($oContext, $item->uid, FALSE))
                {
                    $editlink = '<a href="'.$base_url.'/raptor/edituser?uid='.$item->uid.'">Edit</a>';
                } else {
                    $editlink = '';
                }
                $fullname = trim($item->usernametitle 
                        . ' ' . $item->lastname 
                        . ', ' . $item->firstname);
                
                $maskedusername = UserInfo::getMaskedText($item->username);
                
                $rows   .= "\n".'<tr>'
                        . '<td title="'.$item->username.'">'.$maskedusername.'</td>'
                        . '<td>'.$fullname.'</td>'
                        . '<td>'.$item->role_nm.'</td>'
                        . '<td>'.($item->accountactive_yn==1 ? 'Y' : 'N' )
                        . '</td>'
                        . '<td>'
                        .   '<a href="'.$base_url
                        .     '/raptor/viewuser?uid='.$item->uid.'">View</a></td>'
                        . '<td>'
                        .   '<a href="'.$base_url
                        .     '/raptor/edituser?uid='.$item->uid.'">Edit</a></td>'
                        .'<td>'.$deleteLink.'</td>'
                        .'</tr>';
            }
        }

        $form['data_entry_area1']['table_container']['users'] = array('#type' => 'item',
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

        //Can they Create user accounts?
        if($userprivs['CEUA1'] == 1)
        {
            $form['data_entry_area1']['action_buttons']['createlinkradiologist'] 
                    = array('#type' => 'item'
                    , '#markup' => '<a class="button" href="'
                .$base_url.'/raptor/addstandarduser?role_nm=Radiologist">Add Radiologist User</a>');				
            
            
            
        }
        if($userprivs['CEUA1'] == 1 || $userprivs['LACE1'] == 1)
        {
            $form['data_entry_area1']['action_buttons']['createlinkresident'] 
                    = array('#type' => 'item'
                    , '#markup' => '<a class="button" href="'
                .$base_url.'/raptor/addstandarduser?role_nm=Resident">Add Resident User</a>');				
        }
        if($userprivs['CEUA1'] == 1)
        {
            $form['data_entry_area1']['action_buttons']['createlinktech'] 
                    = array('#type' => 'item'
                    , '#markup' => '<a class="button" href="'
                .$base_url.'/raptor/addstandarduser?role_nm=Technologist">Add Technologist User</a>');				

            $form['data_entry_area1']['action_buttons']['createlinksched'] 
                    = array('#type' => 'item'
                    , '#markup' => '<a class="button" href="'
                .$base_url.'/raptor/addstandarduser?role_nm=Scheduler">Add Scheduler User</a>');				
        }
        if($userprivs['CEUA1'] == 1 && $userinfo->isSiteAdministrator())
        {
            $form['data_entry_area1']['action_buttons']['createlinkadmin'] 
                    = array('#type' => 'item'
                    , '#markup' => '<a class="button" href="'
                .$base_url.'/raptor/addadminuser">Add Site Admin User</a>');				
        }        
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit" />');        
        
        return $form;
    }
}
