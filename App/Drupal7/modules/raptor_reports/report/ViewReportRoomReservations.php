<?php
/**
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

require_once('AReport.php');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportRoomReservations extends AReport
{
    public function getName() 
    {
        return 'Room Reservations';
    }

    public function getDescription() 
    {
        return 'Shows room reservations';
    }

    public function getRequiredPrivileges() 
    {
        $aRequire['SP1'] = 1;
        return $aRequire;
    }
    
    public function getMenuKey() 
    {
        return 'raptor/showroomreservations';
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='raptor-report'>\n",
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
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Exit" data-redirect="/drupal/worklist?dialog=viewReports">');
        
        
        return $form;
    }
}
