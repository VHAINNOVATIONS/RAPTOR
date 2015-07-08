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
    private $m_oUA = NULL;
    
    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
        
        $loaded1 = module_load_include('php', 'raptor_glue', 'analytics/UserActivity');
        if(!$loaded1)
        {
            $msg = 'Failed to load UserActivity';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oUA = new \raptor\UserActivity();
        
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

    private function getArrayValueIfExistsElseAlt($array,$index,$altvalue=NULL)
    {
        $check = $array;
        foreach($index as $key)
        {
            if(!key_exists($key, $check))
            {
                return $altvalue;
            }
            $check = $check[$key];
        }
        return $check;
    }

    private function getArrayDurValueIfExistsElseAlt($array,$index,$altvalue=NULL)
    {
        $seconds = $this->getArrayValueIfExistsElseAlt($array, $index, $altvalue);
        if($seconds == $altvalue)
        {
            return $altvalue;
        }
        try
        {
            $wholeseconds = ceil($seconds);
            $dtF = new \DateTime("@0");
            $dtT = new \DateTime("@$wholeseconds");
            $dateinstance = $dtF->diff($dtT);
            $portioned = $dateinstance->format('%a;%h;%i;%s');
            $parts = explode(';',$portioned);
            if($wholeseconds >= 86400)  //Days
            {
                $formatted = $dateinstance->format('%a days %h hours %i minutes and %s seconds');
            } else 
            if($wholeseconds >= 3600)   //Hours
            {
                $formatted = $dateinstance->format('%h hours %i minutes and %s seconds');
            } else 
            if($wholeseconds >= 60)    //Minutes
            {
                $formatted = $dateinstance->format('%i minutes and %s seconds');
            } else {
                $formatted = $dateinstance->format('%s seconds');
            }
            //$formatted = $dateinstance->format('%a days %h hours, %i minutes and %s seconds');
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues($report_start_date=NULL,$report_end_date=NULL)
    {
        $rowdata = array();
        $startdateoptions = $this->getStartDateOptions();
        if($report_start_date>'' && $report_start_date[0] == '-')
        {
            //Special flag
            $command = $report_start_date;
            $now_ts = time();
            $lastXdays_ts = strtotime($command,$now_ts);
            $lastXdays_dt = date("Y-m-d", $lastXdays_ts);
            $report_start_date = $lastXdays_dt;  
        }
        
        $allthedetail = $this->m_oUA->getActivityByModalityAndDay(VISTA_SITE,$report_start_date,$report_end_date);
        
        $userdetails = $allthedetail['user_activity'];
        foreach($userdetails as $uid=>$userdetails)
        {
            foreach($userdetails['rowdetail'] as $key=>$rowdetail)
            {
                $modality_abbr=$rowdetail['modality_abbr'];
                $year = $rowdetail['dateparts']['year'];
                $qtr = $rowdetail['dateparts']['qtr'];
                $week = $rowdetail['dateparts']['week'];
                $day = $rowdetail['dateparts']['dow'];
                $username=$userdetails['username'];
                $userrole=$userdetails['role_nm'];
                $userlogin_ts=$userdetails['most_recent_login_dt'];
                $movedIntoApproved=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','into_states','AP'),0);
                $movedIntoCollab=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','collaboration_initiation'),0);
                $collabTarget=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','collaboration_target'),0);
                $movedIntoAcknowlege=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','into_states','PA'),0);
                $movedIntoCompleted=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','into_states','EC'),0);
                $movedIntoSuspend=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','into_states','IA'),0);
                $maxTimeAP2Sched=$this->getArrayDurValueIfExistsElseAlt($rowdetail,array('durations','max_approved_to_scheduled'),'');
                $avgTimeAP2Sched=$this->getArrayDurValueIfExistsElseAlt($rowdetail,array('durations','avg_approved_to_scheduled'),'');
                $maxTimeAP2Done=$this->getArrayDurValueIfExistsElseAlt($rowdetail,array('durations','max_approved_to_examcompleted'),'');
                $avgTimeAP2Done=$this->getArrayDurValueIfExistsElseAlt($rowdetail,array('durations','avg_approved_to_examcompleted'),'');
                $maxTimeAP2Colab=$this->getArrayDurValueIfExistsElseAlt($rowdetail,array('durations','max_collaboration_initiation'),'');
                $avgTimeAP2Colab=$this->getArrayDurValueIfExistsElseAlt($rowdetail,array('durations','avg_collaboration_initiation'),'');
                
                $rawmaxTimeAP2Sched=$this->getArrayValueIfExistsElseAlt($rowdetail,array('durations','max_approved_to_scheduled'),'');
                $rawavgTimeAP2Sched=$this->getArrayValueIfExistsElseAlt($rowdetail,array('durations','avg_approved_to_scheduled'),'');
                $rawmaxTimeAP2Done=$this->getArrayValueIfExistsElseAlt($rowdetail,array('durations','max_approved_to_examcompleted'),'');
                $rawavgTimeAP2Done=$this->getArrayValueIfExistsElseAlt($rowdetail,array('durations','avg_approved_to_examcompleted'),'');
                $rawmaxTimeAP2Colab=$this->getArrayValueIfExistsElseAlt($rowdetail,array('durations','max_collaboration_initiation'),'');
                $rawavgTimeAP2Colab=$this->getArrayValueIfExistsElseAlt($rowdetail,array('durations','avg_collaboration_initiation'),'');
                
                $totalScheduled=$this->getArrayValueIfExistsElseAlt($rowdetail,array('count_events','scheduled'),0);

                $row = array();
                $modality_help = ($modality_abbr == '' || $modality_abbr == '--' ) ? 'No protocol has been selected' : '';
                $row['uid'] = $uid;
                $row['modality_abbr'] = $modality_abbr;
                $row['modality_help'] = $modality_help;
                $row['year'] = $year;
                $row['quarter'] = $qtr;
                $row['week'] = $week;
                $row['day'] = $day;
                $row['day_name'] = $rowdetail['dateparts']['dow_tx'];
                $row['onlydate'] =  $rowdetail['dateparts']['onlydate'];
                $row['username'] = $username;
                $row['role_nm'] = $userrole;
                $row['most_recent_login_dt'] = $userlogin_ts;
                $row['Total_Approved'] = $movedIntoApproved;
                $row['Count_Collab_Init'] = $movedIntoCollab;
                $row['Count_Collab_Target'] = $collabTarget;
                $row['Total_Acknowledge'] = $movedIntoAcknowlege;
                $row['Total_Complete'] = $movedIntoCompleted;
                $row['Total_Suspend'] = $movedIntoSuspend;
                $row['Total_Scheduled'] = $totalScheduled;
                
                $row['max_A_S'] = $maxTimeAP2Sched;
                $row['avg_A_S'] = $avgTimeAP2Sched;
                $row['max_A_C'] = $maxTimeAP2Done;
                $row['avg_A_C'] = $avgTimeAP2Done;
                $row['max_collab'] = $maxTimeAP2Colab;
                $row['avg_collab'] = $avgTimeAP2Colab;
                
                $row['raw_max_A_S'] = $rawmaxTimeAP2Sched;
                $row['raw_avg_A_S'] = $rawavgTimeAP2Sched;
                $row['raw_max_A_C'] = $rawmaxTimeAP2Done;
                $row['raw_avg_A_C'] = $rawavgTimeAP2Done;
                $row['raw_max_collab'] = $rawmaxTimeAP2Colab;
                $row['raw_avg_collab'] = $rawavgTimeAP2Colab;

                $uniquesortkey = "$key:$uid";
                //drupal_set_message("LOOK sortkey==$uniquesortkey");
                $rowdata[$uniquesortkey] = $row;
            }
        }
        krsort($rowdata);
        //$bundle['debug'] = $allthedetail;
        $bundle['start_date_options'] = $startdateoptions;
        $bundle['report_start_date'] = $report_start_date;
        $bundle['rowdata'] = $rowdata;
        return $bundle;
    }
	
    private function getStartDateOptions()
    {
        //Provide context options to the user
        $now_ts = time();
        $last7days_ts = strtotime('-7 days',$now_ts);
        $last7days_dt = date("Y-m-d", $last7days_ts);
        $last30days_ts = strtotime('-30 days',$now_ts);
        $last30days_dt = date("Y-m-d", $last30days_ts);
        $thisyear_dt = date("Y-1-1", $now_ts);
        $lastyear_ts = strtotime('-1 years',$now_ts);
        $lastyear_dt = date("Y-1-1", $lastyear_ts);
        
        return array(
            $last7days_dt => t("Last 7 days (since $last7days_dt)"),
            $last30days_dt => t("Last 30 days (since $last30days_dt)"),
            $thisyear_dt => t('Since start of year'),
            0 => t('All available data'),
            );
    }
    
    function getDownloadTypes()
    {
        $supported = array();
        $supported['CSV'] = array();
        $supported['CSV']['helptext'] = 'CSV files can be opened and analyzed in Excel';
        $supported['CSV']['downloadurl'] = $this->getDownloadURL('CSV');
        $supported['CSV']['linktext'] = 'Download detail to a CSV file';
        $supported['CSV']['delimiter'] = ",";

        $supported['TXT'] = array();
        $supported['TXT']['helptext'] = 'Tab delimited text files can be opened and analyzed in Excel';
        $supported['TXT']['downloadurl'] = $this->getDownloadURL('TXT');
        $supported['TXT']['linktext'] = 'Download detail to a tab delimited text file';
        $supported['TXT']['delimiter'] = "\t";
        
        return $supported;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $now_dt = date("Y-m-d H:i:s", time());

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        
        if($myvalues['report_start_date'] > '' && $myvalues['report_start_date'] > 0)
        {
            $scopetext = 'All available data since '.$myvalues['report_start_date'];
        } else {
            $scopetext = 'All available data in the system.';
        }
        $form['data_entry_area1']['context']['blurb'] = array('#type' => 'item',
                '#markup' => '<p>Raptor Site '.VISTA_SITE.' as of '.$now_dt." ($scopetext)</p>", 
            );

        $downloadlinks = $this->getDownloadLinksMarkup();
        if(count($downloadlinks) > 0)
        {
            $markup = implode(' | ',$downloadlinks);
            $form['data_entry_area1']['context']['exportlink'][] = array(
                '#markup' => "<p>$markup</p>"
                );
        }
        
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        if(isset($myvalues['debug']))
        {
            $rawdata = $myvalues['debug'];
            $form['data_entry_area1']['table_container']['debugstuff'] = array('#type' => 'item',
                    '#markup' => '<h1>debug details</h1><pre>' 
                        . print_r($rawdata,TRUE) 
                        . '<pre>'
                );
        }

        $rows = '';
        $rowdata = $myvalues['rowdata'];
        foreach($rowdata as $coldata)
        {
            $rows .= '<tr>'
                    . '<td title="'.$coldata['modality_help'].'">' . $coldata['modality_abbr'] . '</td>'
                    . '<td>' . $coldata['year'] . '</td>'
                    . '<td>' . $coldata['quarter'] . '</td>'
                    . '<td>' . $coldata['week'] . '</td>'
                    . '<td title="'.$coldata['onlydate'].' ('.$coldata['day_name'].')">' . $coldata['day'] . '</td>'
                    . '<td title="'.$coldata['uid'].'">' . $coldata['username'] . '</td>'
                    . '<td>' . $coldata['role_nm'] . '</td>'
                    . '<td>' . $coldata['most_recent_login_dt'] . '</td>'
                    . '<td>' . $coldata['Total_Approved']  . '</td>'
                    . '<td>' . $coldata['Count_Collab_Init']  . '</td>'
                    . '<td>' . $coldata['Count_Collab_Target']  . '</td>'
                    . '<td>' . $coldata['Total_Acknowledge']  . '</td>'
                    . '<td>' . $coldata['Total_Complete']  . '</td>'
                    . '<td>' . $coldata['Total_Suspend']  . '</td>'
                    . '<td>' . $coldata['max_A_S'] . '</td>'
                    . '<td>' . $coldata['avg_A_S'] . '</td>'
                    . '<td>' . $coldata['max_A_C'] . '</td>'
                    . '<td>' . $coldata['avg_A_C'] . '</td>'
                    . '<td>' . $coldata['max_collab'] . '</td>'
                    . '<td>' . $coldata['avg_collab'] . '</td>'
                    . '<td>' . $coldata['Total_Scheduled'] . '</td>'

                    . '</tr>';
        }

        $form['data_entry_area1']['table_container']['activity'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
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
                            . '<th title="Total number of tickets where user initiated Collaboration">Count Collab Init</th>'
                            . '<th title="Total number of tickets where user was selected as the Collaboration target">Count Collab Target</th>'
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
        
        //Provide context options to the user
        $form['data_entry_area1']['selections']['report_start_date'] 
                = array('#type' => 'select',
                    '#title' => t('Scope'),
                    '#options' => $myvalues['start_date_options'],
                    '#disabled' => $disabled,
                    '#default_value' => $myvalues['report_start_date'],
            );
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
       
        $form['data_entry_area1']['action_buttons']['refresh'] 
                = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
    
    
}
