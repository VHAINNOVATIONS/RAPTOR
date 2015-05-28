<?php

module_load_include('php', 'raptor_imageviewing', 'core/config');
require_once 'ArrayValue.php';

global $raptor_context;
global $raptor_protocoldashboard;
global $raptor_protocol_content;
global $base_url;
//$protocol_input = $raptor_protocol_content["Input"]["Protocol"];

$protocol_input = render($page['content']);

//$medications_detail = $raptor_protocol_content["Reference"]["MedicationsDetail"];
//$allergies_detail = $raptor_protocol_content["Reference"]["AllergiesDetail"];
$pathology_reports_detail = $raptor_protocol_content["Reference"]["PathologyReportsDetail"];
$surgery_reports_detail = $raptor_protocol_content["Reference"]["SurgeryReportsDetail"];
$problems_list_detail = $raptor_protocol_content["Reference"]["ProblemsListDetail"];
//deprecated 20150524 $notes_detail = $raptor_protocol_content["Reference"]["NotesDetail"];
//$radiology_reports_detail = $raptor_protocol_content["Reference"]["RadiologyReportsDetail"][0];
$order_overview = $raptor_protocol_content["Reference"]["OrderOverview"];
$aMedBundle = $raptor_protocol_content["Reference"]["MedicationsBundle"];
$medications_detail = $aMedBundle['details'];
$medications_atrisk_hits = $aMedBundle['atrisk_hits'];
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


$labsJSON      = json_encode(new \raptor\ArrayValue($raptor_protocol_content["Reference"]["Graph"]["Labs"]), JSON_PRETTY_PRINT);
$vitalsJSON    = json_encode(new \raptor\ArrayValue($raptor_protocol_content["Reference"]["Graph"]["Vitals"]), JSON_PRETTY_PRINT);
$thumbnailJSON = json_encode(new \raptor\ArrayValue($raptor_protocol_content["Reference"]["Graph"]["Thumbnail"]), JSON_PRETTY_PRINT);
$modalityJSON  = json_encode($modality, JSON_PRETTY_PRINT);

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

Drupal.pageData.modality = <?php echo $modalityJSON ?>;
</script>

<div id="header-sticky-wrapper-patient-name" class="sticky-wrapper">
        <p id="paragraph-patient-name"><?php echo($raptor_protocoldashboard["PatientName"]) ?></p>
</div>

<div id="protocol_container">
  <?php 
  $is_protocol_page = TRUE; //Imporant that we set this so that the header shows right content!
  $folder = realpath(dirname(__FILE__));
  include("$folder/../user-check.php");
  include("$folder/../render-header.php");
  include("$folder/../render-timeout-warning.php");
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

        <style>
        .generic-warning-area {
            background-color: yellow;
            border: red;
        }
        </style>

          
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <th>Tracking ID</th>
            <!-- removed case id as per #369 -->
            <th colspan="2">Procedure</th>
            <th>Image Type</th>
            <th id="requested_by_header">Requested By</th>
            <th>Submit Request To</th>
            <th>Category of Exam</th>
          </tr>
          <tr>
            <td title='Vista order status is <?php echo($raptor_protocoldashboard["orderFileStatus"]) ?>'>
                <?php echo($raptor_protocoldashboard["Tracking ID"]) ?></td>
            <!-- removed case id as per #369 -->
            <td colspan="2"><?php echo($raptor_protocoldashboard["Procedure"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["ImageType"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["RequestedBy"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["PatientLocation"]) ?></td>
            <td><?php echo($raptor_protocoldashboard["ExamCategory"]) ?></td>
          </tr>
          <tr>
            <th>Ordered/Due Date</th>
            <th>Requesting Location</th>
            <th>Reason for Study</th>
            <th>SSN</th>
            <th>PCP</th>
            <th>Transport</th>
            <th>Urgency</th>
          </tr>
          <tr>
              <?php
              //CompoundDateInfo = Ordered/Due Date
              $sCompoundDateInfo = $raptor_protocoldashboard['RequestedDate'] 
                      . ' / ' 
                      . ($raptor_protocoldashboard["ScheduledDate"] > '' ? $raptor_protocoldashboard["ScheduledDate"] : $raptor_protocoldashboard['DesiredDate']);
              ?>
            <td><?php echo($sCompoundDateInfo) ?></td>
            <td><?php echo($raptor_protocoldashboard["RequestingLocation"]) ?></td>
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
            <td><?php echo(implode("<br>",explode("\n",$raptor_protocoldashboard["ClinicalHistory"]))) ?></td>
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


      <!-- See protocol.js to determine how this HTML is moved to this location -->
      <!-- <section id="static-warnings"></section> -->
      
      <div id="tabs-wrapper" class="tabs-wrapper">

          <ul class="tabs">
            <li>
                <input type="radio" checked name="tabs" id="tab1" accesskey="p">
                  <label for="tab1">Protocol</label>
                  <div id="tab-content1" class="tab-content animated fadeIn">

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
                            <td class="medical-health-<?php echo $data_row['eGFR_Health'] ?>"><?php echo $data_row["eGFR"] ?></td>
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
                    </section> 
                    <!-- end of Left Side -->

                    <!-- User Input Areas -->
                    <?php echo "$protocol_input"; ?>                         
                    
                    <div class="clear_fix"></div>
                </div>
              </li>

            <li>
                <input type="radio" name="tabs" id="tab2" accesskey="m">
                <label for="tab2">Medications</label>
                <div id="tab-content2" class="tab-content animated fadeIn">
                  <!-- Readonly -->
                  <section class="read-only2">
                    <p>Searched for at risk meds: 
                        <?php 
                        $searchedmarkup = array();
                        //echo implode(', ', $aAtRiskMeds); 
                        //error_log("LOOK THEME ATRISKHITS>>> ".print_r($medications_atrisk_hits,TRUE));
                        foreach($aAtRiskMeds as $onemed)
                        {
                            if(in_array($onemed, $medications_atrisk_hits))
                            {
                                $searchedmarkup[] 
                                        = "<span class='medical-value-danger' style='background-color: yellow; font-weight: bold;'>$onemed</span>";
                            } else {
                                $searchedmarkup[] = $onemed;
                            }
                        }
                        echo implode(', ', $searchedmarkup); 
                        ?>
                    </p>
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
                  </section>
                  <!-- page content -->

                </div>
            </li>

            <li>
                <input type="radio" name="tabs" id="tab3" accesskey="v">
                <label for="tab3">Vitals</label>
                <div id="tab-content3" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">
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
              <input type="radio" name="tabs" id="tab4" accesskey="a">
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
                </section> 
                <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab5" accesskey="l">
                <label for="tab5">Labs</label>
                <div id="tab-content5" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2">

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
                <input type="radio" name="tabs" id="tab6" accesskey="h">
                <label for="tab6">Dose Hx</label>
                <div id="tab-content6" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2" data-url="<?php echo($base_url) ?>/raptor/getradiationdosehxtab">
                  </section> 
                  <!-- end of Readonly -->

                  
                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab7" accesskey="c">
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
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab8" accesskey="o">
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
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab9" accesskey="n">
                <label for="tab9">Notes</label>
                <div id="tab-content10" class="tab-content animated fadeIn">
<iframe id="iframe_a" style="display:none;" name="iframe_a" width="100%" height="600px" ></iframe>

                  <!-- Readonly -->
                  <section class="read-only2" data-url="<?php echo($base_url) ?>/raptor/getnotestab">
                    
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab10" accesskey="r">
                <label for="tab10">Rad rpts</label>
                <div id="tab-content10" class="tab-content animated fadeIn">
<iframe id="iframe_a" style="display:none;" name="iframe_a" width="100%" height="600px" ></iframe>

                  <!-- Readonly -->
                  <section class="read-only2" data-url="<?php echo($base_url) ?>/raptor/getradrptstab">
                    
                  </section> 
                  <!-- end of Readonly -->

                </div>
              </li>

              <li>
                <input type="radio" name="tabs" id="tab11" accesskey="y">
                <label for="tab11">Library</label>
                <div id="tab-content11" class="tab-content animated fadeIn">

                  <!-- Readonly -->
                  <section class="read-only2" data-url="<?php echo($base_url) ?>/raptor/getprotocollibtab">
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

<div id="cancelorder-modal" style="display:none;" title="Cancel Order"></div> <!--! end of modal -->
<div id="replaceorder-modal" style="display:none" title="Replace Order"></div> <!--! end of modal alex edits-->

