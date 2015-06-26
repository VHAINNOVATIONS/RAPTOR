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
 * This class returns the report
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportRadiationDoseWatch extends AReport
{
    private $m_oFRD = NULL;
    private static $reqprivs = array();
    private static $menukey = 'raptor/showradiationdosewatch';
    private static $reportname = 'Facility Radiation Dose Watch';

    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
        
        module_load_include('php', 'raptor_datalayer', 'core/FacilityRadiationDose');
        module_load_include('php', 'raptor_glue', 'utility/RadiationDoseHelper');
        module_load_include('php', 'raptor_glue', 'form/ProtocolInfoUtility');
        
        $this->m_oFRD = new \raptor\FacilityRadiationDose();
    }

    
    public function getDescription() 
    {
        return 'Shows available facility radiation dose tracking information';
    }

    private function getSimpleProtocolInfoMap()
    {
        $map = array();
        try
        {
            $result = db_select('raptor_protocol_lib', 'p')
                    ->fields('p')
                    ->orderBy('protocol_shortname')
                    ->execute();
            foreach($result as $item) 
            {
                $map[$item->protocol_shortname] = array($item->name,$item->modality_abbr);
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $map;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['intro'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        //Populate the report sections with content.
        $intro[] = 'This report shows the average radiation dose values collected at this facility for each protocol.'
                . '  Values are only factored into the averages after an exam is completed in RAPTOR and the exam has been committed to the VistA system.';
        $form['data_entry_area1']['intro']['main'] = array('#type' => 'item',
                 '#markup' => '<p>'.implode('</p><p>', $intro).'</p>'
            );
        
        
        $protocol_info_map = $this->getSimpleProtocolInfoMap();
        $bundle = $this->m_oFRD->getSiteDoseTracking();
        $details = $bundle['details'];
        $detailrows = array();
        foreach($details as $psn=>$detailcontainer)
        {
            $fullname = $protocol_info_map[$psn][0];
            $modality_abbr = $protocol_info_map[$psn][1];
            foreach($detailcontainer as $dose_source_cd=>$sourcecontainer)
            {
                $sourcedetail = $sourcecontainer['detail'];
                foreach($sourcedetail as $key=>$onedetail)
                {
                    $uom = $onedetail['uom'];
                    $dose_type_cd = $onedetail['dose_type_cd'];
                    $dose_avg = $onedetail['dose_avg'];
                    $sample_ct = $onedetail['sample_ct'];
                    $updated_dt = $onedetail['updated_dt'];
                    $dose_source_tx = \raptor\RadiationDoseHelper::getDefaultTermForDoseSource($dose_source_cd);
                    $dose_quality_tx = \raptor\RadiationDoseHelper::getDoseTypeTermForTypeCode($dose_type_cd,FALSE);
                    $onerowmarkup = "<td>$psn</td>"
                            . "<td>$fullname</td>"
                            . "<td>$modality_abbr</td>"
                            . "<td>$dose_source_tx</td>"
                            . "<td>$dose_avg</td>"
                            . "<td>$uom</td>"
                            . "<td>$sample_ct</td>"
                            . "<td>$dose_quality_tx</td>"
                            . "<td>$updated_dt</td>";
                    $detailrows[] = $onerowmarkup;    //= '<b>'.$psn.'</b>:'.print_r($onedetail,TRUE);
                }
            }
        }
        if(count($detailrows) < 1)
        {
            $form['data_entry_area1']['table_container']['detailinfo'] = array('#type' => 'item',
                    '#markup' => "<h3>No radiation dose information is currently available for this facility</h3>",
                );
        } else {
            $tableheader = "<th title='Unique identifier for this protocol'>Short Name</th>"
                        . "<th title='Long descriptive name for this protocol'>Long Name</th>"
                        . "<th title='Equipement or technology used'>Modality</th>"
                        . "<th title='Radiation source'>Radiation Source</th>"
                        . "<th title='Average dose recorded for this facility'>Dose Average</th>"
                        . "<th title='The normalized unit of measure for the dose average shown'>Unit of Measure</th>"
                        . "<th title='Sample size for the average dose shown here'>Sample Count</th>"
                        . "<th title='Quality of the averaged dose'>Dose Quality</th>"
                        . "<th title='When this information was last updated'>Updated</th>";

            $form['data_entry_area1']['table_container']['detailinfo'] = array('#type' => 'item',
                    '#markup' => "<table id='my-raptor-dialog-table' class='raptor-dialog-table dataTable'>"
                    . "<thead><tr>$tableheader</tr></thead>"
                    . "<tbody><tr>".implode('</tr><tr>',$detailrows)."</tr></tbody>"
                    . "</table>",
                );
        }
        
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
