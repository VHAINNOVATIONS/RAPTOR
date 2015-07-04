<?php
/**
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

use \DateTime;

require_once 'AReport.php';

/**
 * This class returns the User Activity Analysis Report
 *
 * CLIN2 1.7
 * 
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportUserTicketProcessing extends AReport
{
    private static $reqprivs = array('VREP2'=>1);
    private static $menukey = 'raptor/viewrepusract2';
    private static $reportname = 'User Ticket Processing Activity Analysis';

    private $m_oWF = NULL;
    private $m_oTA = NULL;
    
    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
        
        $loaded1 = module_load_include('php', 'raptor_glue', 'analytics/TicketActivity');
        if(!$loaded1)
        {
            $msg = 'Failed to load TicketActivity';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oTA = new \raptor\TicketActivity();
        
        $loaded2 = module_load_include('php', 'raptor_workflow', 'core/Transitions');
        if(!$loaded2)
        {
            $msg = 'Failed to load the Transitions Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oWF = new \raptor\Transitions();
    }
    
    public function getDescription() 
    {
        return 'Shows analysis of user ticket processing activity in the system';
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $rowdata = array();
        //$myvalues['formmode'] = 'V';
        /*
        $result = db_query("Call raptor_user_dept_analysis('user')")
                        ->execute();

        $result = db_select('temp4', 't')
                        ->fields('t')
                        ->orderBy('modality_abbr', 'DESC')
                        ->orderBy('_year', 'DESC')
                        ->orderBy('quarter', 'DESC')
                        ->orderBy('week', 'DESC')
                        ->orderBy('day', 'DESC')
                        ->orderBy('username', 'DESC')
                        ->execute();

        while($res = $result->fetchAssoc())
        {
                $myvalues[] = $res;
        }
        */
        
        
        $allthedetail = $this->m_oTA->getDetailedTrackingHistory(VISTA_SITE);
        
        
        $userdetails = $allthedetail['relevant_users'];
        foreach($userdetails as $uid=>$details)
        {
            $modality_abbr="MTODO";
            $year = "YEARTODO";
            $qtr = "QTRTODO";
            $week = "WKTODO";
            $day = "DayTODO";
            $username="TODO$uid";
            $userrole="RoleTODO";
            $userlogin_ts="LoginTODO";
            $movedIntoApproved="APmoveTODO";
            $movedIntoCollab="CollabTODO";
            $movedIntoAcknowlege="AckTODO";
            $movedIntoCompleted="DoneTODO";
            $movedIntoSuspend="CancelTODO";
            $maxTimeAP2Sched="maxTimeAP2Sched";
            $avgTimeAP2Sched="avgTimeAP2Sched";
            $maxTimeAP2Done="maxTimeAP2Done";
            $avgTimeAP2Done="avgTimeAP2Done";
            $maxTimeAP2Colab="maxTimeAP2Colab";
            $avgTimeAP2Colab="avgTimeAP2Colab";
            $totalScheduled="todoTotShed";
            
            $row = array();
            $row['uid'] = $uid;
            $row['modality_abbr'] = $modality_abbr;
            $row['_year'] = $year;
            $row['quarter'] = $qtr;
            $row['week'] = $week;
            $row['day'] = $day;
            $row['username'] = $username;
            $row['role_nm'] = $userrole;
            $row['most_recent_login_dt'] = $userlogin_ts;
            $row['Total_Approved'] = $movedIntoApproved;
            $row['Count_Collab'] = $movedIntoCollab;
            $row['Total_Acknowledge'] = $movedIntoAcknowlege;
            $row['Total_Complete'] = $movedIntoCompleted;
            $row['Total_Suspend'] = $movedIntoSuspend;
            $row['max_A_S'] = $maxTimeAP2Sched;
            $row['avg_A_S'] = $avgTimeAP2Sched;
            $row['max_A_C'] = $maxTimeAP2Done;
            $row['avg_A_C'] = $avgTimeAP2Done;
            $row['max_collab'] = $maxTimeAP2Colab;
            $row['avg_collab'] = $avgTimeAP2Colab;
            $row['Total_Scheduled'] = $totalScheduled;
            
            $rowdata[] = $row;
        }
        $bundle['raw'] = $allthedetail;
        $bundle['rowdata'] = $rowdata;
        return $bundle;
    }
	
    private function getFormatDuration($seconds)
    {
        /*
        $max_A_S = round((int)$val['Max_Time_A_S']);
        $dtT = new DateTime("@$max_A_S");
        $max_A_S = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
         */
        
        return "$seconds sec";  //TODO
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
	$dtF = new DateTime("@0");

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $rawdata = $myvalues['raw'];
        $form['data_entry_area1']['table_container']['debugstuff'] = array('#type' => 'item',
                '#markup' => '<h1>!!!!222 debug stuff</h1><pre>' 
                    . print_r($rawdata,TRUE) 
                    . '<pre>'
            );
        
        $rows = '';
        $rowdata = $myvalues['rowdata'];
        foreach($rowdata as $val)
        {
            $rows .= '<tr>'
                    . '<td>' . $val['modality_abbr'] . '</td>'
                    . '<td>' . $val['_year'] . '</td>'
                    . '<td>' . $val['quarter'] . '</td>'
                    . '<td>' . $val['week'] . '</td>'
                    . '<td>' . $val['day'] . '</td>'
                    . '<td title="'.$val['uid'].'">' . $val['username'] . '</td>'
                    . '<td>' . $val['role_nm'] . '</td>'
                    . '<td>' . $val['most_recent_login_dt'] . '</td>'
                    . '<td>' . $val['Total_Approved']  . '</td>'
                    . '<td>' . $val['Count_Collab']  . '</td>'
                    . '<td>' . $val['Total_Acknowledge']  . '</td>'
                    . '<td>' . $val['Total_Complete']  . '</td>'
                    . '<td>' . $val['Total_Suspend']  . '</td>'
                    . '<td>' . $val['max_A_S'] . '</td>'
                    . '<td>' . $val['avg_A_S'] . '</td>'
                    . '<td>' . $val['max_A_C'] . '</td>'
                    . '<td>' . $val['avg_A_C'] . '</td>'
                    . '<td>' . $val['max_collab'] . '</td>'
                    . '<td>' . $val['avg_collab'] . '</td>'
                    . '<td>' . $val['Total_Scheduled'] . '</td>'

                    . '</tr>';
        }

        $form['data_entry_area1']['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table">'
                            . '<thead><tr>'
                            . '<th title="The modality abbreviation of this metric" >Modality</th>'
                            . '<th title="The year of this metric" >Year</th>'
                            . '<th title="The quarter number of this metric" >Quarter</th>'
                            . '<th title="The week number of this metric, Jan 1 is week 1" >Week</th>'
                            . '<th title="The day number of this metric" >Day</th>'
                            . '<th title="The name of the user" >User Name</th>'
                            . '<th title="The role of the user in the system" >User Role</th>'
                            . '<th title="The most recent login timestamp" >Most recent login</th>'
                            . '<th title="Total number of tickets moved to Approved state">Total Approved</th>'
                            . '<th title="Total number of tickets moved to Collaboration state">Count Picked for Collab</th>'
                            . '<th title="Total number of tickets moved to Acknowledge state">Total Acknowlege</th>'
                            . '<th title="Total number of tickets moved to Complete state">Total Complete</th>'
                            . '<th title="Total number of tickets moved to Suspend state">Total Suspend</th>'
                            . '<th title="Max time a ticket was in Approved state before it was Scheduled">Max Time between Approved and Sched</th>'
                            . '<th title="Average time tickets were in Approved state before were Scheduled">Avg Time Approved to Sched</th>'
                            . '<th title="Max time a ticket was in Approved state before it moved to Completed state">Max Time Approved to Exam Completed</th>'
                            . '<th title="Average time tickets were in Accepted state moving to Completed state">Avg Time Accepted to Exam Completed</th>'
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
        
        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
    
    
}
