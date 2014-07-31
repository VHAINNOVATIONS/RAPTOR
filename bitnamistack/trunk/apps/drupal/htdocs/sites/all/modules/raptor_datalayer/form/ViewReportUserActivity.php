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
 * This class returns the user activity report
 *
 * @author FrankWin7VM
 */
class ViewReportUserActivity
{

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        //drupal_add_js('jQuery(document).ready(function () {alert("testing message")});', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
        /*drupal_add_js("jQuery(document).ready(function){
            jQuery('.raptor-dialog-table').DataTable({
                'pageLength' : 25
            });
        });", array('type' => 'inline' , 
        'scope' => 'footer' , 
        'weight' => '5'
        ));
        */
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
        
        
        $query = db_select('raptor_user_profile', 'n');
        $query->join('users', 'u', 'n.uid = u.uid'); 
        $query->fields('n');
        $query->fields('u');
        $query->orderBy('access', 'DESC');
        $result = $query->execute();        
        $now = time();
        $MININ4WEEKS=4*10080;
        foreach($result as $item) 
        {
            $minutessincelastaction = ($item->access > 0 ? round(($now - $item->access ) / 60) : NULL);
            if($minutessincelastaction !== NULL && $minutessincelastaction <= $MININ4WEEKS)   //Only include users that have logged in recently
            {
                $hourssincelastaction = round($minutessincelastaction / 60);
                $fullname = trim($item->usernametitle . ' ' . $item->lastname . ', ' . $item->firstname);
                $username = $item->username;
                $rolename = $item->role_nm;
                $lastactivity = date('m/d/Y H:i:s',$item->access);
                $lastlogin = date('m/d/Y H:i:s',$item->login);
                
                $query = db_select('raptor_user_activity_tracking', 'n');
                $query->fields('n')
                    ->condition('uid',$item->uid,'=')
                    ->condition('action_cd',3,'=')
                    ->orderBy('updated_dt','DESC')
                    ->range(0,1);
                $logoutresut = $query->execute();
                if($logoutresut->rowCount() == 1)
                {
                    $a = $logoutresut->fetchAssoc();
                    $lastlogout = $a['updated_dt'];
                } else {
                    $lastlogout = '*Never*';  
                }
    
                $rows   .= "\n".'<tr>'
                        . '<td>'.$username.'</td>'
                        . '<td>'.$fullname.'</td>'
                        . '<td>'.$rolename.'</td>'
                        . '<td>'.$lastlogin.'</td>'
                        . '<td>'.$lastlogout.'</td>'
                        . '<td>'.$lastactivity.'</td>'
                        . '<td '.($hourssincelastaction > 0 ? 'title="about '.$hourssincelastaction.' hours"' : 'title="less than 1 hour"' ).'>'.$minutessincelastaction.'</td>'
                        .'</tr>';
            }
        }

        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th>Login name</th>'
                            . '<th>Full name</th>'
                            . '<th>Role</th>'
                            . '<th>Last Login</th>'
                            . '<th>Last Logout</th>'
                            . '<th>Last Activity</th>'
                            . '<th>Minutes since Last Activity</th>'
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

        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=viewReports">');
        
        
        return $form;
    }
}
