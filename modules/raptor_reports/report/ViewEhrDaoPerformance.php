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
    function getFieldValues($report_start_date=NULL,$report_end_date=NULL)
    {
        $rawdetails = array();
        $ticketlist = array('2009','2010');
        $this->m_oEDRM->getPerformanceDetails($ticketlist);
        $bundle['ticketlist'] = $ticketlist;
        $bundle['debug'] = $rawdetails;
        $rowdata = array(); //TODO
        $bundle['rowdata'] = $rowdata;
        return $bundle;
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
                    . '<td>TODO</td>'
                    . '</tr>';
        }

        $form['data_entry_area1']['table_container']['activity'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th title="The modality abbreviation of this metric" >TODO</th>'
                            . '</tr></thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
        //Provide context options to the user
        $form['data_entry_area1']['selections']['tickets_for_test'] 
                = array('#type' => 'textarea',
                    '#title' => t('Tickets for performance test use'),
                    '#disabled' => $disabled,
                    '#default_value' => $myvalues['tickets_for_test'],
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
