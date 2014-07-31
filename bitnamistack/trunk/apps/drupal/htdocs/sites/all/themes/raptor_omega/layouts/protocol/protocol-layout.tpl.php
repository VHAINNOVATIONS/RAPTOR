<?php
global $raptor_context;
global $raptor_protocoldashboard;
global $raptor_protocol_content;
$protocol_input = $raptor_protocol_content["Input"]["Protocol"];
$medications_detail = $raptor_protocol_content["Reference"]["MedicationsDetail"];
$allergies_detail = $raptor_protocol_content["Reference"]["AllergiesDetail"];
$pathology_reports_detail = $raptor_protocol_content["Reference"]["PathologyReportsDetail"];
$surgery_reports_detail = $raptor_protocol_content["Reference"]["SurgeryReportsDetail"];
$problems_list_detail = $raptor_protocol_content["Reference"]["ProblemsListDetail"];
$notes_detail = $raptor_protocol_content["Reference"]["NotesDetail"];
//$radiology_reports_detail = $raptor_protocol_content["Reference"]["RadiologyReportsDetail"][0];
$order_overview = $raptor_protocol_content["Reference"]["OrderOverview"];
$medications_detail = $raptor_protocol_content["Reference"]["MedicationsDetail"];
$vitals_summary = $raptor_protocol_content["Reference"]["VitalsSummary"];
$vitals_detail = $raptor_protocol_content["Reference"]["VitalsDetail"];
$allergies_detail = $raptor_protocol_content["Reference"]["AllergiesDetail"];
$diagnostic_labs_detail = $raptor_protocol_content["Reference"]["DiagnosticLabsDetail"];
$radiology_reports_detail = $raptor_protocol_content["Reference"]["RadiologyReportsDetail"];
$aAtRiskMeds = $raptor_protocol_content['AtRiskMeds'];
$modality = $raptor_protocoldashboard["Modality"];

function raptor_print_details($data) 
{
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

class ArrayValue implements JsonSerializable 
{
    public function __construct(array $array) 
    {
        $this->array = $array;
    }

    public function jsonSerialize() 
    {
        return $this->array;
    }
}

$labsJSON = json_encode(new ArrayValue($raptor_protocol_content["Reference"]["Graph"]["Labs"]), JSON_PRETTY_PRINT);
$vitalsJSON = json_encode(new ArrayValue($raptor_protocol_content["Reference"]["Graph"]["Vitals"]), JSON_PRETTY_PRINT);
$thumbnailJSON = json_encode(new ArrayValue($raptor_protocol_content["Reference"]["Graph"]["Thumbnail"]), JSON_PRETTY_PRINT);
$modalityJSON = json_encode($modality, JSON_PRETTY_PRINT);

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
function getImageInfoAsHtml($raptor_context, $patientDFN, $patientICN, $reportID, $caseNumber)
{
    //TODO - Ajax these calls!
    try
    {
        if($raptor_context == NULL || $raptor_context=='') //20140714
        {
            global $raptor_context;
        }
        $aImageInfo = raptor_datalayer_getAvailImageMetadata($raptor_context, $patientDFN, $patientICN, $reportID, $caseNumber);
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
    } catch (Exception $ex) {
        error_log('Trouble getting VIX data: ' . print_r($ex,TRUE) . "\n\tImageInfo>>>".print_r($aImageInfo,TRUE));
        return $ex->getMessage();
    }
}

/*
echo '<code style="display: none;">'.chr(10);
var_dump($raptor_protocol_content["Reference"]["Graph"]["Labs"]);
var_dump($labsJSON);
echo '</code>';
 * 
 */
?>
<script>
var chartThumbnail = <?php echo $thumbnailJSON ?>;

var chartVitals = <?php echo $vitalsJSON ?>;

var chartLabs = <?php echo $labsJSON ?>;

Drupal.pageData = {
  modality : <?php echo $modalityJSON ?>
};
</script>

<div id="protocol_container">
  <?php 
  $folder = realpath(dirname(__FILE__));
  include("$folder/../user-check.php");
  include("$folder/../render-header.php");
  ?>

  <div class="wrapper cf">

    <!-- <div class="clear_fix"></div> -->
    
    <div class="main-content">
      
      <div class="side-nav">
      
        <div id="user-icon"></div>
        
        <nav>
          <ul>
              <li class="overview"><a href="#">Overview</a></li>
              <li class="medications"><a href="#">Medications</a></li>
              <li class="vitals"><a href="#">Vitals</a></li>
              <li class="allergies"><a href="#">Allergies</a></li>
              <li class="labs"><a href="#">Labs</a></li>
              <li class="dose-hx"><a href="#">Dose Hx</a></li>
              <li class="clin-rpts"><a href="#">Clin rpts</a></li>
              <li class="problem-list"><a href="#">Problem List</a></li>
              <li class="notes"><a href="#">Notes</a></li>
              <li class="rad-rpts"><a href="#">Rad rpts</a></li>
              <li class="library"><a href="#">Library</a></li>
          </ul>
        </nav> <!-- end of nav -->
      </div>
      
      <!-- <div class="right-content cf"> -->
    
      <header class="cf">

        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <th>Tracking ID</th>
            <th>Case ID</th>
            <th>Procedure</th>
            <th>Image Type</th>
            <th id="requested_by_header">Requested By</th>
            <th>Patient Location</th>
            <th>Category of Exam</th>
          </tr>
          <tr>
            <td><?php echo($raptor_protocoldashboard["Tracking ID"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["CaseID"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["Procedure"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["ImageType"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["RequestedBy"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientLocation"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["ExamCategory"]) ?></td>
          </tr>
          <tr>
            <th>Ordered/Due Date</th>
            <th>Patient Category/Location</th>
            <th>Reason for Study</th>
            <th>SSN</th>
            <th>PCP</th>
            <th>Transport</th>
            <th>Urgency</th>
          </tr>
          <tr>
            <td><?php echo($raptor_protocoldashboard["ScheduledDate"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientCategory"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["ReasonForStudy"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientSSN"]) ?></td>
            <td><?php echo $order_overview["PCP"] ?></td>
            <td><?php echo($raptor_protocoldashboard["Transport"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["Urgency"]) ?></td>
          </tr>
          <tr>
            <th>Patient Name</th>
            <th>Age</th>
            <th>Clinical History</th>
            <th>DOB</th>
            <th>Ethnicity</th>
            <th>Attending</th>
            <th>Gender</th>
          </tr>
          <tr>
            <td><?php echo($raptor_protocoldashboard["PatientName"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientAge"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["ClinicalHistory"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientDOB"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientEthnicity"]) ?></td>
            <td><?php echo $order_overview["AtP"] ?></td>
            <td>M</td>
          </tr>
        </table>
  
      </header> <!-- end of header -->

      <?php 
          print $messages; //RAPTOR Omega errors
      ?>

      <div class="tabs-wrapper">

          <ul class="tabs">
            <li>
                <input type="radio" checked name="tabs" id="tab1">
                  <label for="tab1">Protocol</label>
                  <div id="tab-content1" class="tab-content animated fadeIn">

                    <!-- Readonly -->
                    <!-- <section id="static-warnings"></section> -->
                    <!-- end of Readonly -->

                    <!-- left side -->
                    <section class="left-side">
                      <h3>Medications</h3>
                      <table class="dashboard-table">
                        <thead>
                          <tr>
                            <th>Med</th>
                            <th>At Risk ?</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $rownum=0; $hasmore=false; ?>
                          <?php foreach($medications_detail as $data_row) { ?>
                          <?php $rownum++;if($rownum > 5){$hasmore=true;break;} ?>
                          <tr>
                            <td><?php echo $data_row["Med"] ?></td>
                            <td><?php echo $data_row["AtRisk"] ?></td>
                            <td><?php echo $data_row["Status"] ?></td>
                          </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                      (<a href="#" class="details" id="medications_detail">see medications detail</a>
                      <?php if($hasmore){ echo("<span class='summary-warning'> for more values ; only 5 of ".count($medications_detail)." rows are displayed here.</span>");} ?>
					  )

                      <h3>Vitals</h3>
                      <!-- img src="/drupal/sites/all/themes/raptor_omega/images/placeholder_small_vitals.png" alt="vitals" -->
					  <div id="thumbnail-chart"></div>
                      <table class="dashboard-table">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Vital</th>
                            <th>Value</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $rownum=0; $hasmore=false; ?>
                          <?php foreach($vitals_summary as $key => $value) { ?>
							  <?php 
							  $thedate = $value["Date of Measurement"];
							  $themeasure = $value["Measurement Value"]; 
							  if(true && (isset($thedate) && $thedate !== '' && isset($themeasure) && substr($themeasure,0,4) !== 'None'))
							  {
							  ?>
							  <?php $rownum++;if($rownum > 6){$hasmore=true;break;} ?>
							  <tr>
								<td><?php echo $thedate ?></td>
								<td><?php echo $key ?></td>
								<td><?php echo $themeasure ?></td>
							  </tr>
							  
							  <?php 
							  }
							  ?>
                          <?php } ?>
                        </tbody>
                      </table>
                      (<a href="#" class="details" id="vitals_detail">see vitals detail</a>
                      <?php if($hasmore){ echo("<span class='summary-warning'> for more values ; only 6 of ".count($vitals_summary)." rows are displayed here.</span>");} ?>
					  )

                      <h3>Allergies</h3>
                      <table class="dashboard-table">
                        <thead>
                          <tr>
                            <th>Allergy Reactant</th>
                            <th>Allergy Type</th>
                            <th>O/H</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $rownum=0; $hasmore=false; ?>
                          <?php foreach($allergies_detail as $data_row) { ?>
                          <?php $rownum++;if($rownum > 5){$hasmore=true;break;} ?>
                          <tr>
                            <td><?php echo $data_row["Item"] ?></td>
                            <td><?php echo $data_row["CausativeAgent"] ?></td>
                            <td><?php echo($data_row['ObservedHistorical']['Snippet']) ?></td>
                          </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                      (<a href="#" class="details" id="allergies_detail">see allergies detail</a>
                      <?php if($hasmore){ echo("<span class='summary-warning'> for more values ; only 5 of ".count($allergies_detail)." rows are displayed here.</span>");} ?>
					  )

                      <h3>Labs</h3>
                      <table class="dashboard-table">
                        <thead>
                          <tr>
                            <th colspan="3">RENAL PANEL</th>
                          </tr>
                          <tr>
                            <th>Date</th>
                            <th>Creatinine</th>
                            <th>eGFR</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $rownum=0; $hasmore=false; ?>
                          <?php 
                          if (is_array($diagnostic_labs_detail)) {
                            foreach($diagnostic_labs_detail as $data_row) { ?>
  
                          <?php $rownum++;if($rownum > 5){$hasmore=true;break;} ?>

                          <tr>
                            <td><?php echo $data_row["DiagDate"] ?></td>
                            <td><?php echo $data_row["Creatinine"] ?></td>
                            <td><?php echo $data_row["eGFR"] ?></td>
                          </tr>

                          <?php 
                            } // END foreach
                          } // END is_array
                        ?>
                        </tbody>
                      </table>
                      (<a href="#" class="details" id="labs_detail">see labs detail</a>
                      <?php if($hasmore){ echo("<span class='summary-warning'> for more values ; only 5 of ".count($diagnostic_labs_detail)." rows are displayed here.</span>");} ?>
					  )

                      <h3>Radiology Reports</h3>
                      <table class="dashboard-table">
                        <thead>
                          <tr>
                            <th>Title</th>
                            <th>Date</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $rownum=0; $hasmore=false; ?>
                          <?php foreach($radiology_reports_detail as $data_row) { ?>
                          <?php $rownum++;if($rownum > 5){$hasmore=true;break;} ?>
                          <tr>
                            <td><?php echo $data_row["Title"] ?></td>
                            <td><?php echo $data_row["ReportedDate"] ?></td>
                          </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                      (<a href="#" class="details" id="radiology_detail">see radiology detail</a>
                      <?php if($hasmore){ echo("<span class='summary-warning'> for more values ; only 5 of ".count($radiology_reports_detail)." rows are displayed here.</span>");} ?>
					  )
                      <!-- <h3>Top Protocol Input Area</h3>
                      <p>Content goes here....</p> -->
                      <!-- <img src="/drupal/sites/all/themes/raptor_omega/images/example/ex_003.jpg"> -->
                    </section> 
                    <!-- end of Left Side -->

                    <!-- User Input Areas -->
                    <?php echo "$protocol_input"; ?>                         
                    
                    <div class="clear_fix"></div>
                </div>
              </li>

            <li>
                <input type="radio" name="tabs" id="tab2">
                <label for="tab2">Medications</label>
                <div id="tab-content2" class="tab-content animated fadeIn">
                  <!-- Readonly -->
                  <section class="read-only2">
                    <p>Searched for at risk meds: <?php echo implode(', ', $aAtRiskMeds); ?></p>
                    <table class="dataTable">
                      <thead>
                        <tr>
                          <th>Medication</th>
                          <th>At Risk</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($medications_detail as $data_row) { ?>
                        <tr>
                          <td><?php echo $data_row["Med"] ?></td>
                          <td><?php echo $data_row["AtRisk"] ?></td>
                          <td><?php echo $data_row["Status"] ?></td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <!-- <img src="/drupal/sites/all/themes/raptor_omega/images/example/med_001.jpg"> -->
                  </section>
                  <!-- page content -->

                </div>
            </li>

            <li>
                <input type="radio" name="tabs" id="tab3">
                <label for="tab3">Vitals</label>
                <div id="tab-content3" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p> -->
                    <!-- img src="/drupal/sites/all/themes/raptor_omega/images/example/vit_001.jpg" -->
    				<div id="vitals-chart"></div>
					
                  <table class="dataTable">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Temp</th>
                        <th>Height</th>
                        <th>Weight</th>
                        <th>BMI</th>
                        <th>Blood Pressure</th>
                        <th>Pulse</th>
                        <th>Resp</th>                        
                        <th>Pain</th>                        
                        <th>C/G</th>                        
                        <th>Pox</th>                        
                        <th>CVP</th>                        
                        <th>Blood Glucose</th>                        
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($vitals_detail as $data_row) { ?>
                      <tr>
                        <td><?php echo $data_row["Date Taken"] ?></td>
                        <td><?php echo $data_row["Temp"] ?></td>
                        <td><?php echo $data_row["Height"] ?></td>
                        <td><?php echo $data_row["Weight"] ?></td>
                        <td><?php echo $data_row["BMI"] ?></td>
                        <td><?php echo $data_row["Blood Pressure"] ?></td>
                        <td><?php echo $data_row["Pulse"] ?></td>
                        <td><?php echo $data_row["Resp"] ?></td>
                        <td><?php echo $data_row["Pain"] ?></td>
                        <td><?php echo $data_row["C/G"] ?></td>
                        <td><?php echo $data_row["Pox"] ?></td>
                        <td><?php echo $data_row["CVP"] ?></td>
                        <td><?php echo $data_row["Blood Glucose"] ?></td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
					
                </section> 
                  <!-- end of Readonly -->
                  
                </div>
              </li>

            <li>
              <input type="radio" name="tabs" id="tab4">
              <label for="tab4">Allergies</label>
              <div id="tab-content4" class="tab-content animated fadeIn">

                <!-- Readonly -->
                <section class="read-only2">
                  <table class="dataTable">
                    <thead>
                      <tr>
                        <th>Date Reported</th>
                        <th>Item</th>
                        <th>Causative Agent</th>
                        <th>Signs/Symptoms</th>
                        <th>Observed/Historical</th>                        
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($allergies_detail as $data_row) { ?>
                      <tr>
                        <td><?php echo $data_row["DateReported"] ?></td>
                        <td><?php echo $data_row["Item"] ?></td>
                        <td><?php echo $data_row["CausativeAgent"] ?></td>
                        <td><a href="#" class="raptor-details"><?php echo($data_row['SignsSymptoms']['Snippet']) ?></a><?php echo raptor_print_details($data_row['SignsSymptoms']["Details"]) ?></td>
                        <td><a href="#" class="raptor-details"><?php echo($data_row['ObservedHistorical']['Snippet']) ?></a><?php echo raptor_print_details($data_row['ObservedHistorical']['Details']) ?></td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                  <!-- <img src="/drupal/sites/all/themes/raptor_omega/images/example/all_001.jpg"> -->
                </section> 
                <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab5">
                <label for="tab5">Labs</label>
                <div id="tab-content5" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p> -->
                    <!-- img src="/drupal/sites/all/themes/raptor_omega/images/example/labs_001.jpg" -->
				    <div id="labs-chart"></div>

                  <table class="dataTable">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Creatinine</th>
                        <th>eGFR</th>
                        <th>Ref</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                       if (is_array($diagnostic_labs_detail)) {
                        foreach($diagnostic_labs_detail as $data_row) { 
                      ?>

                      <tr>
                        <td><?php echo $data_row["DiagDate"] ?></td>
                        <td><?php echo $data_row["Creatinine"] ?></td>
                        <td><?php echo $data_row["eGFR"] ?></td>
                        <td><?php echo $data_row["Ref"] ?></td>
                      </tr>

                      <?php 
                          } // END foreach
                        } // END is_array
                      ?>
                    </tbody>
                  </table>
					
					
                  </section> 
                  <!-- end of Readonly -->
                  
                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab6">
                <label for="tab6">Dose Hx</label>
                <div id="tab-content6" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p> -->
                    <img src="/drupal/sites/all/themes/raptor_omega/images/example/dose_001.jpg">
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab7">
                <label for="tab7">Clin rpts</label>
                <div id="tab-content7" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p> -->
                    <h3>Pathology Reports</h3>
                    <table class="dataTable">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Date</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($pathology_reports_detail as $data_row) { ?>
                        <tr>
                          <td><?php echo $data_row["Title"] ?></td>
                          <td><?php echo $data_row["ReportDate"] ?></td>
                          <td><a href="#" class="raptor-details"><?php echo $data_row["Snippet"] ?></a><?php echo raptor_print_details($data_row["Details"]) ?></td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <h3>Surgery Reports</h3>
                    <table class="dataTable">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Date</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($surgery_reports_detail as $data_row) { ?>
                        <tr>
                          <td><?php echo $data_row["Title"] ?></td>
                          <td><?php echo $data_row["ReportDate"] ?></td>
                          <td><a href="#" class="raptor-details"><?php echo $data_row["Snippet"] ?></a><?php echo raptor_print_details($data_row["Details"]) ?></td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <!-- <img src="/drupal/sites/all/themes/raptor_omega/images/example/clin_001.jpg"> -->
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab8">
                <label for="tab8">Problem List</label>
                <div id="tab-content8" class="tab-content animated fadeIn">

                 <!-- Readonly -->
                  <section class="read-only2">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p> -->
                    <table class="dataTable">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Onset Date</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($problems_list_detail as $data_row) { ?>
                        <tr>
                          <td><?php echo $data_row["Title"] ?></td>
                          <td><?php echo $data_row["OnsetDate"] ?></td>
                          <td><a href="#" class="raptor-details"><?php echo $data_row["Snippet"] ?></a><?php echo raptor_print_details($data_row["Details"]) ?></td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <!-- <img src="/drupal/sites/all/themes/raptor_omega/images/example/prob_001.jpg"> -->
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab9">
                <label for="tab9">Notes</label>
                <div id="tab-content9" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p> -->
                    <h3>Selected Notes</h3>
                    <table class="dataTable">
                      <thead>
                        <tr>
                          <th>Type</th>
                          <th>Date</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($notes_detail as $data_row) { ?>
                        <tr>
                          <td><?php echo $data_row["Type"] ?></td>
                          <td><?php echo $data_row["Date"] ?></td>
                          <td><a href="#" class="raptor-details"><?php echo $data_row["Snippet"] ?></a> <?php echo raptor_print_details($data_row["Details"]) ?>
                          </td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <h3>Rest of Notes</h3>
                    <table class="dashboard-table">
                      <thead>
                        <tr>
                          <th>Type</th>
                          <th>Date</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($notes_detail as $data_row) { ?>
                        <tr>
                          <td><?php echo $data_row["Type"] ?></td>
                          <td><?php echo $data_row["Date"] ?></td>
                          <td><a href="#" class="raptor-details"><?php echo $data_row["Snippet"] ?></a> <?php echo raptor_print_details($data_row["Details"]) ?>
                          </td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <!-- <img src="/drupal/sites/all/themes/raptor_omega/images/example/not_001.jpg"> -->
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab10">
                <label for="tab10">Rad rpts</label>
                <div id="tab-content10" class="tab-content animated fadeIn">
<iframe id="iframe_a" style="display:none;" name="iframe_a" width="100%" height="600px" ></iframe>

                  <!-- Readonly -->
                  <section class="read-only2" data-url="<?php echo(RAPTOR_ROOT_URL) ?>raptor_datalayer/getradrptstab">
                    
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab11">
                <label for="tab11">Library</label>
                <div id="tab-content11" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2" data-url="raptor_datalayer/getprotocollibtab">
                    <!-- <h3>Medications Page</h3>
                    <p>Content goes here...</p>
                    <img src="/drupal/sites/all/themes/raptor_omega/images/example/lib_001.jpg"> -->
                  </section> 
                  <!-- end of Readonly -- >

                </div>
              </li>
        </ul>

        <div class="clear_fix"></div>

      </div> <!-- end of tabs wrapper -->
    </div>
  </div>

  <div class="clear_fix"></div>

</div> <!--! end of container -->

<div id="suspend-modal" style="display:none;" title="Suspend Ticket">
    <div class="change-columns-modal">
      <form>
        <p style="float: left; width: 105px;">Reason for suspend</p>
        <select>
          <option>Patient requested</option>
          <option>VA requested</option>
          <option>Other</option>
        </select>
      </form>
      <div style="clear: both;">
        Notes<br>
        <textarea style="width: 100%; height: 150px"></textarea>
      </div>
    </div>
</div> <!--! end of modal -->