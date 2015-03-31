<?php
//global $user;
global $raptor_context;
global $raptor_worklist_rows;

if(isset($raptor_worklist_rows["DataRows"]))
{
    $data_rows = $raptor_worklist_rows["DataRows"];
    if($data_rows === null || !is_array($data_rows))    //20140718
    {
        $data_rows = array();   //Just assign an empty array.
    }
} 
else 
{
    $data_rows = array();   //Just assign an empty array.
}

$m_oContext = \raptor\Context::getInstance();
$m_oUserinfo = $raptor_context->getUserInfo();
$m_aHiddenCols = $m_oUserinfo->getPrefWorklistColsHidden();   //These are the columns we should hide by default.

/*
if($data_rows === null || !is_array($data_rows))
{
    //Work around issue with no data for now. FJF 20140323
    $data_rows = array(
        array("NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA","NO_DATA"),
    );
}
 */

function get_raptor_workflow_status($code,$assignmentdetails=NULL) 
{
    $workflowStatusCodes = array(
        "AC" => "Active",
        "AP" => "Approved",
        "CO" => "Collaborative",
        "RV" => "Ready for Review",
        "PA" => "Protocol Acknowledged",
        //"IA" => "Inactive",
        "EC" => "Interpretation",
        "QA" => "QA"
    );
    if($code === 'CO' && is_array($assignmentdetails))
    {
        $notes = $assignmentdetails['requester_notes_tx'];
        if(strpos($notes, 'Reserving for myself') !== FALSE)
        {
            return 'Reserved';
        } else {
            if(strpos($notes, 'Scheduler suggested') !== FALSE)
            {
                return 'Scheduler suggested';
            }
        }
    }
    return $workflowStatusCodes[$code];
}

function getRankScoreIcon($score)
{
    if($score > 600)
    {
        $url = 'sites/all/themes/raptor_omega/images/score_critical.png';
    } else if($score > 450) {
        $url = 'sites/all/themes/raptor_omega/images/score_urgent.png';
    } else if($score > 200) {
        $url = 'sites/all/themes/raptor_omega/images/score_high.png';
    } else if($score > 100) {
        $url = 'sites/all/themes/raptor_omega/images/score_medium.png';
    } else if($score > 20) {
        $url = 'sites/all/themes/raptor_omega/images/score_low.png';
    } else if($score > 0) {
        $url = 'sites/all/themes/raptor_omega/images/score_verylow.png';
    } else {
        $url = 'sites/all/themes/raptor_omega/images/score_zero.png';
    }
 
    return $url;
}

// Worklist table hide columns
class ArrayValue implements JsonSerializable 
{
    public function __construct($array) 
    {
        if(!is_array($array))
        {
            $array = array();   //Make it an empty array.
        } 
        $this->array = $array;
    }

    public function jsonSerialize() 
    {
        return $this->array;
    }
}

$hiddenColsJSON = json_encode(new ArrayValue($m_aHiddenCols), JSON_PRETTY_PRINT);
?>
<!--<pre><?php print_r($m_aHiddenCols); ?></pre>
<pre><?php print_r($hiddenColsJSON); ?></pre>-->
<script>
    Drupal.pageData.hiddenColumns = <?php echo $hiddenColsJSON ?>;
    Drupal.pageData.userID = <?php echo json_encode($m_oUserinfo->getUserID()) ?>;
</script>
<!-- Updated 20140323 FJF -->
<div id="worklist_container">
    <?php

    $folder = realpath(dirname(__FILE__));
    include("$folder/../user-check.php");
    include("$folder/../render-header.php");
    include("$folder/../render-timeout-warning.php");

    ?>
    <!--<form action=""><textarea name="" id="" cols="30" rows="10"></textarea></form>-->
    <!-- SECTION B -->
    <?php

    $fullname = trim($m_oUserinfo->getUserNameTitle() ." ". $m_oUserinfo->getFirstName() . " " . $m_oUserinfo->getLastName() . " " .$m_oUserinfo->getUserNameSuffix());
    $userprivs = $m_oUserinfo->getSystemPrivileges();
    if($m_oUserinfo->hasModalityPreferencesOverrides() || $m_oUserinfo->hasWeightedAnatomyPreferencesOverrides())
    {
        $rankingmode = 'Customized';
    } else {
        $rankingmode = 'Standard';
    }
    if($userprivs['SWI1'] != 1)
    {
        //THIS USER DOES NOT HAVE RIGHTS TO VIEW WORKLIST!

        //Show Drupal errors, if any.
        print $messages; //RAPTOR Omega errors
        ?>
        <h3>This user account does not have patient data access privileges.</h3>
        <br>
        <?php
        $tz = NULL;
        if(date_default_timezone_get())
        {
            $tz = date_default_timezone_get();
            echo '<p>System default timezone is '.$tz.'</p>';
        }
        if(ini_get('date.timezone'))
        {
            $tz = ini_get('date.timezone');
            echo '<p>Local file date.timezone setting is '.$tz.'</p>';
        }
        $rawdt = new DateTime();
        if(isset($tz))
        {
            $localdt = clone $rawdt;
            $timezone = new DateTimeZone($tz);
            $localdt->setTimezone($timezone);
        }
        ?>
        <p>Server date and time when this page was created...<p>
        <ul>
            <li>
                GMT was <?php
                echo $rawdt->format('Y-m-d H:i:s');
                ?>
            <?php if(isset($tz)) { ?>
            <li>
                Local was <?php
                echo $localdt->format('Y-m-d H:i:s');
                ?>
            <?php } ?>

        </ul>

    <?php } else { ?>
    
    <div class="selection">
        <section class="selection-mode">
            <label class="assignment-filter" title="Ordering of entries in worklist"><strong>Ranking Mode:</strong> <?php echo($rankingmode) ?>
                <input type="button" id="edit-ranking-mode" value="Edit" />
            </label>
            <label class="right last"><strong>Worklist Filter Mode:</strong>
                <select id="worklist_filter" name="">
                    <option value="AC|CO|RV">Needs Protocol</option>
                    <option value="AP|PA" <?php echo( $m_oUserinfo->getRoleName() == "Technologist" ? "selected" : "" ) ?>>Ready for Examination</option>
                    <option value="EC">Interpretation</option>
                    <option value="QA">QA</option>
                    <!-- Commented out suspended per ticket 000482
                    <option value="IA">Suspended</option>
                    -->
                    <option value=".*">Show All</option>
                </select>
            </label>
            <label class="right last"><strong>Click Mode:</strong>
                <select id="selection_mode" name="">
                    <option value="edit">Edit The Protocol</option>
                    <option value="view">View The Protocol</option>
                    <option value="checkmark">Checkmark Toggle</option>
                </select>
            </label>
            <input class="change-columns right" title="Add or Remove Columns from the Worklist" type="button" value="Change Columns" />
        </section>
        <div style="clear: both"></div>
        <?php

        print $messages; //RAPTOR Omega errors

        ?>
    </div>

    <div class="wrapper">
        <div class="main-content">
            <div class="side-nav">
                <div id="user-icon"></div>
            </div>
            <div class="right-content">
                
                <div id="worklist_wrapper">
                    <div class="top-buttons-wrapper">
                        <div id="buttonsWrapper">
                            <span id="edit-top-work-order-top" title="Start by editing top item or top checked item"><a href="#">Edit Top Work Order</a></span>
                            <span id="refresh-top" title="Refresh the contents of the worklist"><a href="#" class="refresh-worklist">Refresh Worklist</a></span>
                        </div>
                    </div> <!-- End of Top Buttons Wrapper -->
                    <div id="worklistLoaderWrapper" style="display: block;">
                        <img src="sites/all/themes/raptor_omega/images/worklist-loader.gif" id="worklistLoader" alt="Loading Worklist">
                        <br>
                        Loading Worklist&hellip;
                    </div>
                    <table id="worklistTable" class="datatable" width="100%" border="0" cellspacing="0" cellpadding="0" style="display: none;">
                        <thead>
                            <tr>
                                <th class="chk-all"><input title="Change all values" type="checkbox" id="chk_new" data-checkId="chk" ></th>
                                <th class="rtid_column" title="Unique tracking ID for each order">Tracking ID</th>
                                <th class="rankscore_column" title="Dynamic ranking score">RS</th>
                                <th class="pat_column" title="Patient name">Patient</th>
                                <th class="desired_column">Date Desired</th>
                                <th class="ordered_column">Date Ordered</th>
                                <th class="modality_column">Modality</th>
                                <th class="sub_column">Image Type</th>
                                <th class="study_column">Study</th>
                                <th class="urgency_column">Urgency</th>
                                <th class="transport_column">Transport</th>
                                <th class="loc_column">Patient Category / Location</th>
                                <th class="status_column" title="RAPTOR status of the order">Workflow Status</th>
                                <th class="status_code_column">Workflow Status Code</th>
                                <th class="assignment_column" title="Collaboration information if any">Assignment</th>
                                <th class="pending_column" title="# of pending imaging orders">#P</th>
                                <th class="scheduled_column" title="Scheduling information for the exam">Pass Box</th>
                            </tr>
                        </thead>
                        <tbody class="table-content">
                            <?php
                            
                            foreach($data_rows as $data_row)
                            {
                                if(!is_array($data_row) || count($data_row) == 0)    //20140715
                                {
                                    continue;
                                } 
                                $aRankScoreDetails = $data_row[18];
                                $score = $aRankScoreDetails[0];
                                $aRSComment = $aRankScoreDetails[1];
                                $rscomment = '';
                                foreach($aRSComment as $key => $value)
                                {
                                    $rscomment .= "<br>$key=$value";
                                }

                                $rsurl = getRankScoreIcon($score);
                                // Change row background color if it is assigned to the current user
                                $rowStyle = is_array($data_row[12]) && $data_row[12]['uid'] == $m_oContext->getUID() ? "font-weight: bold" : "";
                            ?>
                            <tr data-rawrtid="<?php echo('['.$data_row[0].']') ?>" style="<?php echo($rowStyle); ?>">
                                <td><input type="checkbox" name="tracking-id" value="<?php echo($data_row[0]) ?>"></td>
                                <td class="rtid_column" title='ranking score = <?php echo($score)  ?>'><?php echo($data_row[0]) ?></td>
                                <td class="rankscore_column" title='ranking score = <?php echo($score) ?>' data='<?php echo($rscomment) ?>' >
                                    <img src="<?php echo($rsurl) ?>">
                                    <p style='opacity:0;height: 0;'><?php echo($score) ?></p>
                                </td>
                                <td class="pat_column"><?php echo($data_row[2]) ?></td>
                                <td class="desired_column"><?php echo($data_row[3]) ?></td>
                                <td class="ordered_column"><?php echo($data_row[4]) ?></td>
                                <td class="modality_column"><?php echo($data_row[5]) ?></td>
                                <td class="sub_column"><?php echo($data_row[17]) ?></td>
                                <td class="study_column"><?php echo($data_row[6]) ?></td>
                                <td class="urgency_column"><?php echo($data_row[7]) ?></td>
                                <td class="transport_column"><?php echo($data_row[8]) ?></td>
                                <td class="loc_column"><?php echo($data_row[9]) ?></td>
                                <td class="status_column"><?php echo(get_raptor_workflow_status($data_row[11],$data_row[12])) ?></td>
                                <td class="status_code_column"><?php echo($data_row[11]) ?></td>
                                <td class="assignment_column"><?php
                                    if (is_array($data_row[12]))
                                    {
                                        echo('<span title="'.$data_row[12]['requester_notes_tx'].'">'.$data_row[12]['fullname'].'</span>');
                                    }
                                    else
                                    {
                                        echo($data_row[12]);
                                    }
                                ?>
                                </td>
                                <?php $pending_alert = ($data_row[19] > 5) ? 'pending_alert' : ''; ?>
                                <td class="pending_column <?php echo $pending_alert ?>"><a href="#" data-patient-name="<?php echo($data_row[2]) ?>"><?php echo($data_row[19]) ?></a></td>
                                <td class="scheduled_column"><a href="#"><?php echo($data_row[15]['ShowTx']) ?></a></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table><!-- End of table -->

                    <div id="buttonsWrapper">
                        <span id="edit-top-work-order-bottom" title="Start by editing top item or top checked item"><a href="#">Edit Top Work Order</a></span>
                        <span id="refresh-bottom" title="Refresh the contents of the worklist"><a href="#" class="refresh-worklist">Refresh Worklist</a></span>
                    </div>
                </div>
            </div> <!-- End of right-content-->
        </div> <!-- End of main-content -->
        <div class="clear_fix"></div>
    </div> <!--! end of wrapper -->
    <?php } ?>
    <?php include("$folder/../render-footer.php"); ?>
</div> <!--! end of container -->

<div id="column-modal" style="display:none;" title="Change Columns">
    <div class="change-columns-modal">
        <h2>Put checkmark next to columns which you want show in the worklist.</h2>
        <form>
            <label><input type="checkbox" checked name="column_display" value="1"><span>Tracking ID</span></label>
            <label><input type="checkbox" checked name="column_display" value="3"><span>Patient</span></label>
            <label><input type="checkbox" checked name="column_display" value="4"><span>Date Desired</span></label>
            <label><input type="checkbox" checked name="column_display" value="5"><span>Date Ordered</span></label>
            <label><input type="checkbox" checked name="column_display" value="6"><span>Modality</span></label>
            <label><input type="checkbox" checked name="column_display" value="7"><span>Image Type</span></label>
            <label><input type="checkbox" checked name="column_display" value="8"><span>Study</span></label>
            <label><input type="checkbox" checked name="column_display" value="9"><span>Urgency</span></label>
            <label><input type="checkbox" checked name="column_display" value="10"><span>Transport</span></label>
            <label><input type="checkbox" checked name="column_display" value="11"><span>Patient Category / Location</span></label>
            <label><input type="checkbox" checked name="column_display" value="12"><span>Workflow Status</span></label>
            <label><input type="checkbox" checked name="column_display" value="14"><span>Assignment</span></label>
            <label><input type="checkbox" checked name="column_display" value="16"><span>Pass Box</span></label>
        </form>
    </div>
</div> <!--! end of modal -->
<div id="schedule-modal" style="display:none;" title="Scheduled Date">
    <div class="change-columns-modal">
        <div style="width: 370px; margin: 0 auto;">
            <form>
                <p style="float: left; width: 45px;">Date:</p>
                <div id="scheduledDate" style="float: left; margin-bottom: 0;"></div>
                <div class="clear_fix"></div>
                <p style="margin: 18px 0 5px 0">Time: <input id="scheduledTime" type="text" value="" placeholder="10:00 AM"></p>
                <p style="margin: 18px 0 5px 0">Place: <input id="scheduledPlace" type="text" value="" placeholder="RM 000"></p>
            </form>
        </div>
    </div>
</div> <!--! end of modal -->
<div id="edit-ranking-mode-modal" style="display:none;" title="Edit Ranking Mode">
        <div class="change-columns-modal">
            Loading&hellip;
        </div>
</div> <!--! end of modal -->
