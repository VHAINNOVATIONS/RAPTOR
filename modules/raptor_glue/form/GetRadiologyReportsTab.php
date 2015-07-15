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


/**
 * This class returns the VistA Radiology Reports tab content
 *
 * @author Frank Font of SAN Business Consultants
 */
class GetRadiologyReportsTab
{
    private $m_oContext = NULL;
    
     //Call same function as in EditUserPage here!
    function __construct($oContext)
    {
        module_load_include('php', 'raptor_datalayer', 'core/data_context');
        module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
        module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
        //module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');
        //module_load_include('php', 'raptor_datalayer', 'core/data_protocolsupport');
        module_load_include('php', 'raptor_datalayer', 'core/data_protocolsettings');
        
        $this->m_oContext = $oContext;
        if(!$oContext->hasSelectedTrackingID())
        {
            throw new \Exception('Did NOT find a selected Tracking ID.  Go back to the worklist and select a ticket first.');
        }
    }


    /**
     * Get information about an image as HTML.
     * @global type $raptor_context
     * @param type $raptor_context NULL or a context instance
     * @param type $patientDFN
     * @param type $patientICN
     * @param type $reportID
     * @param type $caseNumber
     * @return string HTML link markup to an image
     */
    private static function getImageInfoAsHtml($oContext, $patientDFN, $patientICN, $reportID, $caseNumber)
    {
        try
        {
            if($oContext == NULL || $oContext=='') 
            {
                throw new \Exception('Must provide context instance!!!!');
            }
            $aImageInfo = raptor_imageviewing_getAvailImageMetadata($oContext, $patientDFN, $patientICN, $reportID, $caseNumber);
            if($aImageInfo['imageCount'] > 0)
            {
                //$returnInfo['thumnailImageUri'] = $thumnailImageUri;        
                if($aImageInfo['imageCount'] == 1)
                {
                    $sIC = '1 image';
                } else {
                    $sIC = $aImageInfo['imageCount'] . ' images';
                }
                $sHTML = '<a onclick="jQuery('."'#iframe_a'".').show();" target="iframe_a" href="'.$aImageInfo['viewerUrl'].'">' . $sIC . ' (' . $aImageInfo['description'] . ') <img src="'.$aImageInfo['thumbnailImageUrl'].'"></a>';
            } else {
                if($aImageInfo['imageCount'] == 0)
                {
                    $sHTML = 'No Images Available';
                } else {
                    //Some kind of error.
                    $sHTML = 'No Images Available (check log file)';
                }
            }
            return $sHTML;
        } catch (\Exception $ex) {
            error_log('Trouble getting VIX data: ' . print_r($ex,TRUE) . "\n\tImageInfo>>>".print_r($aImageInfo,TRUE));
            return $ex->getMessage();
        }
    }
    
    private static function raptor_print_details($data)
    {
        //return print_r($text,TRUE);
        $result = "";

        $result .= "<div class=\"hide\"><dl>";

        if (is_array($data)) {

          foreach($data as $key => $value) {
            $result .= "<dt>".$key.":</dt>";
            $result .= "<dd>".$value."</dd>";
          }

        } else {
          $result .= $data;
        }

        $result .= "</dl></div>";

        return $result;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='protocollib-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        //$oPSD = new \raptor\ProtocolSupportingData($this->m_oContext);
        //$radiology_reports_detail = $oPSD->getRadiologyReportsDetail();
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $radiology_reports_detail = $mdwsDao->getRadiologyReportsDetailMap();

        //$oDD = new \raptor\DashboardData($this->m_oContext);
        //$raptor_protocoldashboard = $oDD->getDashboardDetails();
        $raptor_protocoldashboard = $this->m_oContext->getMdwsClient()->getDashboardDetailsMap();
        $sTrackingIDfromDD = $raptor_protocoldashboard['Tracking ID'];
        $patientDFN=$raptor_protocoldashboard['PatientID'];
        $patientICN=$raptor_protocoldashboard['mpiPid'];
        $can_getimages=TRUE;
        if(trim($patientDFN) == '' || trim($patientICN) == '')
        {
            $can_getimages=FALSE;
            if(trim($patientDFN) == '')
            {
                error_log("ERROR on $sTrackingIDfromDD NO $patientDFN(mpiPid) found for TrackingIDfromDD=".$sTrackingIDfromDD);
            }
            if(trim($patientICN) == '')
            {
                error_log("ERROR on $sTrackingIDfromDD NO PATIENTICN(mpiPid) found for PATIENTDFN=".$patientDFN);
            }
            error_log("DEBUG ENTIRE DD for missing PATIENTICN(mpiPid) on $sTrackingIDfromDD >>>".print_r($raptor_protocoldashboard,TRUE));
        }
        
        $rows = '';
        foreach($radiology_reports_detail as $data_row) 
        {
            $reportID=$data_row['ReportID'];
            $caseNumber=$data_row['CaseNumber'];        
            $rows .= "\n".'<tr>'
                  . '<td>'.$data_row['Title'].'</td>'
                  . '<td>'.$data_row['ReportedDate'].'</td>'
                  . '<td><a href="#" class="raptor-details">'
                    .$data_row["Snippet"].'</a>'
                    .GetRadiologyReportsTab::raptor_print_details($data_row['Details']) 
                    .'</td>';
            if($can_getimages)
            {
                $rows .= '<td>' 
                        .GetRadiologyReportsTab::getImageInfoAsHTML($this->m_oContext, $patientDFN, $patientICN, $reportID, $caseNumber)
                        .'</td>';
            } else {
                $rows .= '<td><span title="The current configuration of VistA is missing needed values to associate the patient with images.">'
                        . 'No images available (VistA config issue)'
                        . '</span></td>';
            }
            $rows .= '</tr>';
        }
        
        $form["data_entry_area1"]['table_container']['reports'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table-rad-reports" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Title</th>'
                            . '<th>Date</th>'
                            . '<th>Details</th>'
                            . '<th>Existing Images</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
        return $form;
    }
}
