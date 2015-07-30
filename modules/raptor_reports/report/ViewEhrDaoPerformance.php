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
 * This class returns the configuration details
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewEhrDaoPerformance extends AReport
{
    private static $reqprivs = array();
    private static $menukey = 'raptor/showehrdaoperformance';
    private static $reportname = 'System Tuning EHR DAO Performance Details';

    private $m_oEDRM = NULL;
    
    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname, TRUE);
        
        $loaded1 = module_load_include('php', 'raptor_datalayer', 'core/EhrDaoRuntimeMetrics');
        if(!$loaded1)
        {
            $msg = 'Failed to load EhrDaoRuntimeMetrics';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oEDRM = new \raptor\EhrDaoRuntimeMetrics();
    }
    
    public function getDescription() 
    {
        return 'Shows detailed EHR DAO performance results by making calls at runtime as the logged in user.';
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues($myvalues = NULL)
    {
        $bundle = array();
        $tickets_for_test = isset($myvalues['tickets_for_test']) ? $myvalues['tickets_for_test'] : '';
        if($tickets_for_test > '')
        {
            $ticketlist = explode(',',$tickets_for_test);
        } else {
            $ticketlist = array();
        }
        $rawdetails = $this->m_oEDRM->getPerformanceDetails($ticketlist);
        $bundle['ticketlist'] = $ticketlist;
        $bundle['DAO'] = $rawdetails['DAO'];
        $bundle['metrics'] = $rawdetails['metrics'];
        $rowdata = array();
        foreach($rawdetails['metrics'] as $onetest)
        {
            $rowdata[] = array(
                'tracking_id'=>$onetest['tracking_id'],
                'start_ts'=>$onetest['start_ts'],
                'end_ts'=>$onetest['end_ts'],
                'action'=>$onetest['metadata']['methodname'],
                'duration'=>$onetest['end_ts']-$onetest['start_ts'],
                'resultsize'=>$onetest['resultsize'],
            );
        }
        
        $biggestsize_item = array(); 
        $biggestsize_item['resultsize'] = -1;
        $slowest_item = array(); 
        $slowest_item['duration'] = 0; 
        $total_action_duration = array();
        $total_action_size = array();
        $total_rows = 0;
        $action_names_map = array();
        foreach($rowdata as $onerow)
        {
            $total_rows++;
            $action_name = $onerow['action'];
            if(!isset($action_names_map[$action_name]))
            {
                $action_names_map[$action_name] = 1;
            } else {
                $action_names_map[$action_name] = $action_names_map[$action_name] + 1;
            }
            $duration = $onerow['duration'];
            $resultsize = $onerow['resultsize'];
            if($onerow['resultsize'] > $biggestsize_item['resultsize'])
            {
                $biggestsize_item['resultsize'] = $onerow['resultsize']; 
                $biggestsize_item['itemdetails'] = $onerow; 
            }
            if($onerow['duration'] > $slowest_item['duration'])
            {
                $slowest_item['duration'] = $onerow['duration']; 
                $slowest_item['itemdetails'] = $onerow; 
            }
            if(!isset($total_action_duration[$action_name]))
            {
                $total_action_duration[$action_name] = $duration;
            } else {
                $total_action_duration[$action_name] = $total_action_duration[$action_name] + $duration;
            }
            if(!isset($total_action_size[$action_name]))
            {
                $total_action_size[$action_name] = $resultsize;
            } else {
                $total_action_size[$action_name] = $total_action_size[$action_name] + $resultsize;
            }
        }
        //Compute averages
        $avg_action_duration = array();
        $avg_action_size = array();
        foreach($action_names_map as $action_name=>$occurance_count)
        {
            if($occurance_count > 0)
            {
                $avg_action_duration[$action_name] = $total_action_duration[$action_name] / $occurance_count;
                $avg_action_size[$action_name] = $total_action_size[$action_name] / $occurance_count;
            }
        }
        //Enhance the row content
        $enhancedrows = array();
        foreach($rowdata as $onerow)
        {
            $tid = $onerow['tracking_id'];
            $action_name = $onerow['action'];
            $duration = $onerow['duration'];
            $duration_delta = $duration - $avg_action_duration[$action_name];
            $resultsize = $onerow['resultsize'];
            $resultsize_delta = $resultsize - $avg_action_size[$action_name];
            
            $onerow['duration_delta'] = $duration_delta;
            $onerow['resultsize_delta'] = $resultsize_delta;
            if($resultsize > 0 && $duration > 0)
            {
                if($resultsize < 10000)
                {
                    //Improve numerical accuracy of result
                    $normalized_duration = 1000000 * (10000 * $duration) / (10000 * $resultsize);
                } else {
                    $normalized_duration = 1000000 * ($duration / $resultsize);
                }
                $onerow['duration_per_1MB'] = $normalized_duration;
            } else {
                $onerow['duration_per_1MB'] = '';
            }
            $enhancedrows[] = $onerow;
        }        
        $bundle['stats'] 
                = array('avg_action_duration'=>$avg_action_duration
                       ,'avg_action_size'=>$avg_action_size
                       ,'total_action_duration'=>$total_action_duration
                       ,'biggestsize_item'=>$biggestsize_item
                       ,'slowest_item'=>$slowest_item
                );
        $bundle['rowdata'] = $enhancedrows;
        $values = array('reportdata'=>$bundle);
        return $values;
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
    
    
    private function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }     
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $now_dt = date("Y-m-d H:i:s", time());
        $reportdata = $myvalues['reportdata'];
        $iterations = isset($reportdata['iterations']) ? $reportdata['iterations'] : '1';
        $tickets_for_test = isset($reportdata['tickets_for_test']) ? $reportdata['tickets_for_test'] : '';
        $headertext = array('TrackingID'=>'Ticket tracking number',
            'Start Time'=>'Start time of the action',
            'End Time'=>'End time of the action',
            'Action Name'=>'The action that took place',
            'Duration'=>'Number of seconds duration',
            'Delta from Ave Duration'=>'Difference from average duration',
            'Result Size'=>'Approximate size of action result',
            'Delta from Avg Size'=>'Difference from average size',
            'Normalized Duration'=>'Duration to get result per 1MB');

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $scopetext = 'All available data in the system.';
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

        $form['data_entry_area1']['table_container']['debugstuff'] = array('#type' => 'item',
                '#markup' => '<h1>debug report data details</h1><pre>' 
                    . print_r($reportdata,TRUE) 
                    . '<pre>'
            );
        
        $rows = '';
        $reportstats = $reportdata['stats'];
        $biggestsize_amount = NULL;
        $biggestsize_action = NULL;
        $biggestsize_tid = NULL;
        $slowest_amount = NULL;
        $slowest_action = NULL;
        $slowest_tid = NULL;
        if(isset($reportstats['biggestsize_item']))
        {
            $biggestsize_item = $reportstats['biggestsize_item'];
            if(isset($biggestsize_item['itemdetails']))
            {
                $biggestsize_amount = $this->formatBytes($biggestsize_item['resultsize']);
                $biggestsize_action = $biggestsize_item['itemdetails']['action'];
                $biggestsize_tid = $biggestsize_item['itemdetails']['tracking_id'];
            }
        }
        if(isset($reportstats['slowest_item']))
        {
            $slowest_item = $reportstats['slowest_item'];
            if(isset($slowest_item['itemdetails']))
            {
                $slowest_amount = $slowest_item['duration'];
                $slowest_action = $slowest_item['itemdetails']['action'];
                $slowest_tid = $slowest_item['itemdetails']['tracking_id'];
            }
        }
        
        
        $rowdata = $reportdata['rowdata'];
        foreach($rowdata as $onerow)
        {
            $action_name = $onerow['action'];
            $duration = $onerow['duration'];
            $duration_delta = $onerow['duration_delta'];
            $resultsize = $onerow['resultsize'];
            $resultsize_delta = $onerow['resultsize_delta'];
            $normalized_resultspeed = $onerow['duration_per_1MB'];
            $nicesizetext = $this->formatBytes($resultsize);
            $nicesizedeltatext = $this->formatBytes($resultsize_delta);
            $tid = trim($onerow['tracking_id']);
            $tidmarkup = $tid;
            $extremetitle = '';
            if($tid == $slowest_tid || $tid == $biggestsize_tid)
            {
                $extreme_items = array();
                if($action_name == $slowest_action)
                {
                    $extreme_items[] = 'slowest';
                }
                if($action_name == $biggestsize_action)
                {
                    $extreme_items[] = 'biggest result';
                }
                if(count($extreme_items) > 0)
                {
                    $extremetitle = implode(',',$extreme_items);
                    $tidmarkup = "<b title='$action_name $extremetitle'>$tid</b>";
                }
            }
            $rows .= '<tr>'
                    . "<td>{$tidmarkup}</td>"
                    . "<td>sl=$slowest_tid b=[$biggestsize_tid]{$onerow['start_ts']}</td>"
                    . "<td>{$onerow['end_ts']}</td>"
                    . "<td>$action_name</td>"
                    . "<td>$duration</td>"
                    . "<td>$duration_delta</td>"
                    . "<td title='$nicesizetext'>$resultsize</td>"
                    . "<td title='$nicesizedeltatext'>$resultsize_delta</td>"
                    . "<td>$normalized_resultspeed</td>"
                    . '</tr>';
        }

        $context_markup_ar = array();
        foreach($reportdata['DAO'] as $key=>$value)
        {
            $context_markup_ar[] = "DAO $key: $value";
        }
        if(is_array($reportdata['ticketlist']))
        {
            $ticketlist = $reportdata['ticketlist'];
            $ticketcount = count($ticketlist);
            $context_markup_ar[] = "Total Tickets: " . $ticketcount;
            if($ticketcount > 0)
            {
                $context_markup_ar[] = "Tickets Tested: " . implode(',',$ticketlist);
            }
        }
        if(isset($reportstats['biggestsize_item']))
        {
            $biggestsize_item = $reportstats['biggestsize_item'];
            if(isset($biggestsize_item['itemdetails']))
            {
                $biggestsize_amount = $this->formatBytes($biggestsize_item['resultsize']);
                $context_markup_ar[] = "Biggest result : " 
                        . $biggestsize_amount
                        . " found in " . $biggestsize_item['itemdetails']['action'] 
                        . ' of ' . $biggestsize_item['itemdetails']['tracking_id'];
            }
        }
        if($slowest_action !== NULL)
        {
            $context_markup_ar[] = "Slowest result : " 
                    . $slowest_amount 
                    . " found in " . $slowest_action
                    . ' of ' . $slowest_tid;
        }
        $form['data_entry_area1']['table_container']['daocontext'] 
                = array('#type' => 'item',
                '#markup' => '<h1>Result Context</h1>'
                    . '<ul><li>' . implode('</li><li>',$context_markup_ar) 
                    . '</ul>'
            );
        
        $headermarkup = '';
        foreach($headertext as $label=>$title)
        {
            $headermarkup .= "<th title='$title'>$label</th>";
        }
        $form['data_entry_area1']['table_container']['activity'] = array('#type' => 'item',
                 '#markup' => '<h2>Details</h2>'
                            . '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . $headermarkup
                            . '</tr></thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');

        
        //Provide context options to the user
        $form['data_entry_area1']['selections']['iterations'] 
                = array('#type' => 'textfield',
                    '#title' => t('Number of iterations for the test'),
                    '#disabled' => $disabled,
                    '#size' => 2,
                    '#default_value' => $iterations,
            );
        $form['data_entry_area1']['selections']['tickets_for_test'] 
                = array('#type' => 'textarea',
                    '#title' => t('Tickets for performance test use'),
                    '#disabled' => $disabled,
                    '#default_value' => $tickets_for_test,
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
