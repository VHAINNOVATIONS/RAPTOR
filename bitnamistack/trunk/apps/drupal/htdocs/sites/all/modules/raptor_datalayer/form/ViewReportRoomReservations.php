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
class ViewReportRoomReservations
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
        
        
        $query = db_select('raptor_schedule_track', 'n');
        //$query->groupBy('n.location_tx');
        $query->fields('n')
           ->orderBy('scheduled_dt', 'DESC');
        $result = $query->execute();        
        
        /*
        $sSQL = 'SELECT '
                .'`siteid`, `IEN`, `scheduled_dt`, `duration_am`, `notes_tx`, `notes_critical_yn`, `location_tx`, `confirmed_by_patient_dt`, `canceled_reason_tx`, `canceled_dt`, `author_uid`, `created_dt` '
                .' FROM raptor_schedule_track ORDER BY scheduled_dt GROUP BY IEN'
        $result = db_query($sSQL);
        */
        
        foreach($result as $item) 
        {
            $rows   .= "\n".'<tr>'
                    . '<td>'.$item->location_tx.'</td>'
                    . '<td>'.$item->scheduled_dt.'</td>'
                    . '<td>'.$item->duration_am.'</td>'
                    . '<td>'.($item->confirmed_by_patient_dt==NULL ? 'No' : 'Yes '.$item->confirmed_by_patient_dt ).'</td>'
                    .'</tr>';
        }

        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th>Room</th>'
                            . '<th>Scheduled Date</th>'
                            . '<th>Duration</th>'
                            . '<th>Confirmed by Patient</th>'
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
