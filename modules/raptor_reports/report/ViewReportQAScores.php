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
 * This class returns the QA scores report
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportQAScores extends AReport
{
    private static $reqprivs = array('QA2'=>1);
    private static $menukey = 'raptor/viewrepqascores';
    private static $reportname = 'Ticket QA Scores';

    private $m_oWF = NULL;
    
    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
        $loaded2 = module_load_include('php', 'raptor_workflow', 'core/Transitions');
        if(!$loaded2)
        {
            $msg = 'Failed to load the Transitions Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oWF = new \raptor\Transitions();
        module_load_include('php', 'raptor_datalayer', 'core/user_data');
    }
    
    public function getDescription() 
    {
        return 'Shows available ticket QA scores in the system';
    }

    private function getProtocolLibMap()
    {
        try
        {
            $map = array();
            $query = db_select('raptor_protocol_lib','p')
                    ->fields('p');
            $query->orderBy('protocol_shortname');
            $result = $query->execute();
            while($record = $result->fetchAssoc())
            {
                $key = $record['protocol_shortname'];
                $map[$key] = $record;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getQACriteriaMap()
    {
        try
        {
            $map = array();
            $query = db_select('raptor_qa_criteria','p')
                    ->fields('p');
            $query->condition('context_cd','T','=');
            $query->orderBy('shortname');
            $result = $query->execute();
            while($record = $result->fetchAssoc())
            {
                $key = $record['shortname'];
                $map[$key] = $record;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getTicketWorkflowHistoryMap($report_start_date)
    {
        try
        {
            $map = array();
            $query = db_select('raptor_ticket_workflow_history','p')
                    ->fields('p');
            if($report_start_date > '')
            {
                $query->condition('created_dt', $report_start_date, '>=');
            }
            $query->orderBy('siteid');
            $query->orderBy('IEN');
            $query->orderBy('new_workflow_state');
            $query->orderBy('created_dt','DESC');
            $result = $query->execute();
            while($record = $result->fetchAssoc())
            {
                $key = $record['IEN'];
                if(!isset($map[$key]))
                {
                    $map[$key] = array();
                }
                $wfs = $record['new_workflow_state'];
                if($wfs == 'QA')
                {
                    $from = $record['old_workflow_state'];
                    if($from == 'PA')
                    {
                        $wfs = 'EC';    //exam complete
                    }
                }
                if(!isset($map[$key][$wfs]))
                {
                    $map[$key][$wfs] = array();
                } else {
                    //Prevent duplicates
                    foreach($map[$key][$wfs] as $existing)
                    {
                        if($existing['created_dt'] == $record['created_dt'])
                        {
                            //Duplicate record, ignore it.
                            $record = NULL;
                        }
                    }
                }
                if($record !== NULL)
                {
                    $map[$key][$wfs][] = $record;
                }
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getNamesText($workflowdetails,$wfs,$upmap)
    {
        $nametext = 1;
        if(!isset($workflowdetails[$wfs]))
        {
            $nametext = NULL;
        } else {
            $approvers = $workflowdetails[$wfs];
            $names = array();
            foreach($approvers as $approver)
            {
                $uid = $approver['initiating_uid'];
                $names[] = \raptor\UserInfo::getComposedFullName($upmap[$uid]);
            }
            $nametext = implode('; ',$names);
        }
        return $nametext;
    }
    
    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues($report_start_date='-7 days',$report_end_date=NULL)
    {
        $headercols = array();
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
        
        $upmap = \raptor\UserInfo::getUserInfoMap();
        $plmap = $this->getProtocolLibMap();
        $wfhmap = $this->getTicketWorkflowHistoryMap($report_start_date);
        $qacmap = $this->getQACriteriaMap();
        
        $query = db_select('raptor_ticket_qa_evaluation', 'n');
        $query->join('raptor_ticket_protocol_settings', 'u', 'n.IEN = u.IEN'); 
        $query->fields('n');
        $query->fields('u',array('primary_protocol_shortname','secondary_protocol_shortname'));
        $query->orderBy('n.IEN');
        $result = $query->execute();        
        $rownum=0;
        while($record = $result->fetchAssoc()) 
        {
            $rownum++;
            $psn = $record['primary_protocol_shortname'];
            $evaluator_uid = $record['author_uid'];
            $ien = $record['IEN'];
            if(isset($wfhmap[$ien]))
            {
                $workflowdetails = $wfhmap[$ien];
                $approver_nm = $this->getNamesText($workflowdetails,'AP',$upmap);
                $acknowledger_nm = $this->getNamesText($workflowdetails,'PA',$upmap);
                $examiner_nm = $this->getNamesText($workflowdetails,'EC',$upmap);
            } else {
                $approver_nm = '';
                $acknowledger_nm = '';
                $examiner_nm = '';
            }
            $record['modality_abbr'] = $plmap[$psn]['modality_abbr'];
            $record['approver_nm'] = $approver_nm;
            $record['acknowledger_nm'] = $acknowledger_nm;
            $record['examiner_nm'] = $examiner_nm;
            $record['evaluator_nm'] = \raptor\UserInfo::getComposedFullName($upmap[$evaluator_uid]);
            unset($record['author_uid']);
            if($rownum == 1)
            {
                foreach($record as $k=>$v)
                {
                    $headercols[] = $k;
                }
            }
            $rowdata[] = $record;
        }
        //krsort($rowdata);
        $bundle['start_date_options'] = $startdateoptions;
        $bundle['report_start_date'] = $report_start_date;
        $bundle['report_end_date'] = $report_end_date;
        $bundle['headercols'] = $headercols;
        $bundle['rowdata'] = $rowdata;
        $bundle['qacmap'] = $qacmap;
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

        $form['data_entry_area1']['context']['qacrit'] = array('#type' => 'item',
                '#prefix' => "\n<fieldset>\n",
                '#suffix' => "\n</fieldset>\n",
            );
        
        $qacmap = $myvalues['qacmap'];
        foreach($qacmap as $key=>$details)
        {
            $detailmarkup_ar = array();
            $detailmarkup_ar[] = "<td>Question</td><td>{$details['question']}</td>";
            $detailmarkup_ar[] = "<td>Explanation</td><td>{$details['explanation']}</td>";
            $detailmarkup_ar[] = "<td>Last Updated</td><td>{$details['updated_dt']}</td>";
            $rowmarkup = implode('</tr><tr>',$detailmarkup_ar);
            $allmarkup = "<b>$key version {$details['version']} "
                . "last updated {$details['updated_dt']}</b><br>"
                . "<table><tr>" . $rowmarkup . '</tr></table>';
            $form['data_entry_area1']['context']['qacrit'][$key][] = array('#type' => 'item',
                    '#markup' => $allmarkup, 
                );
        }

        $keyparams = array();
        $keyparams['report_start_date'] = $myvalues['report_start_date'];
        $downloadlinks = $this->getDownloadLinksMarkup($keyparams);
        if(count($downloadlinks) > 0)
        {
            $markup = implode(' | ',$downloadlinks);
            $form['data_entry_area1']['context']['exportlink'][] = array(
                '#markup' => "<p>$markup</p>",
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
        
        $headercols = $myvalues['headercols'];
        $rowdata = $myvalues['rowdata'];
        $headermarkup = "<tr><th>".implode('</th><th>',$headercols).'</th></tr>';
        $rowmarkup_ar = array();
        foreach($rowdata as $onerow)
        {
            $rowmarkup_ar[] = "<tr><td>".implode('</td><td>',$onerow).'</td></tr>';
        }
        $rowsmarkup = implode("\n",$rowmarkup_ar);
        
        $form['data_entry_area1']['table_container']['activity'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead>' 
                            . $headermarkup
                            . '</thead>'
                            . '<tbody>'
                            . $rowsmarkup
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
       
        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
}
