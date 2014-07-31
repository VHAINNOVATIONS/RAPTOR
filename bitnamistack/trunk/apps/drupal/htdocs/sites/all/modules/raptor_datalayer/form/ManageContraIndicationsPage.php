<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class ManageContraIndicationsPage
{

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        
        drupal_add_js("jQuery(document).ready(function){
            jQuery('.dataTable').DataTable({
                'pageLength' : 25
            });
        });", array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));

        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='user-admin'>\n",
            '#suffix' => "\n</section>\n",
        );

        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $rows = "\n";
        $sSQL = "SELECT `category_nm`, `rule_nm`, `version`, `explanation`, `msg_tx`, `req_ack_yn`, `active_yn`, `trigger_crit`, `updated_dt` FROM `raptor_contraindication_rule` ORDER BY rule_nm";
        $result = db_query($sSQL);
        foreach($result as $item) 
        {
            $rows   .= "\n".'<tr><td>'.$item->rule_nm.'</td><td>'.$item->category_nm.'</td><td>'.$item->active_yn.'</td><td>'.$item->updated_dt.'</td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/viewcontraindication?rn='.$item->rule_nm.'\'">View</a></td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/editcontraindication?rn='.$item->rule_nm.'\'">Edit</a></td>'
                    .'<td><a href="javascript:window.location.href=\'/drupal/raptor_datalayer/deletecontraindication?rn='.$item->rule_nm.'\'">Delete</a>'
                    .'</td></tr>';
        }

        $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead><tr><th>Rule Name</th><th>Category</th>'
                            . '<th>Active</th><th>Updated</th>'
                            . '<th>View</th>'
                            . '<th>Edit</th>'
                            . '<th>Delete</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
        
       $form["data_entry_area1"]['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-submit" type="button" value="Add Rule" ' 
                .'data-redirect="/drupal/raptor_datalayer/addcontraindication" />');
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />');        

        return $form;
    }
}
