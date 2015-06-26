<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *  
 */

namespace raptor;

require_once 'AReport.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportRoomReservations extends AReport
{
    private static $reqprivs = array('SP1'=>1);
    private static $menukey = 'raptor/showroomreservations';
    private static $reportname = 'Room Reservations';

    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
    }

    public function getDescription() 
    {
        return 'Shows room reservations';
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
        $query->fields('n')
           ->orderBy('scheduled_dt', 'DESC');
        $result = $query->execute();        
        
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

        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
}
