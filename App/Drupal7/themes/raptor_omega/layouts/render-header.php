<!-- SECTION A -->
<div class="top">
    
    <?php 
    global $base_url;
    $userinfo = $raptor_context->getUserInfo();
    $fullname = trim($userinfo->getUserNameTitle() ." ". $userinfo->getFirstName() . " " . $userinfo->getLastName() . " " .$userinfo->getUserNameSuffix() . " (" . $userinfo->getRoleName() . ")");
    $userprivs = $userinfo->getSystemPrivileges();
    if(isset($is_protocol_page) && $is_protocol_page)
    {
        $oUtility = new \raptor\ProtocolInfoUtility();
        $nSiteID = $raptor_context->getSiteID();
        $sIEN = $raptor_context->getSelectedTrackingID();
        $sTrackingID = $nSiteID.'-'.$sIEN;
        $sCWFS = $oUtility->getCurrentWorkflowState($nSiteID,$sIEN);
        $sWorklflowStatePhrase = \raptor\TicketTrackingData::getTicketPhraseFromWorflowState($sCWFS);
        if($sCWFS == 'IA')
        {
            $colorstyle = 'background-color: #FF0000;';
        } else {
            $colorstyle = '';
        }
        $sWorkflowStateCSS = $colorstyle."border-width: 3px; border-style: inset; text-align: center; display: inline-block; width:200px; position: absolute; right:0px; top:30px;";
    } else {
        $sWorklflowStatePhrase = '';
        $sWorkflowStateCSS = '';
    }
    ?>
    <div style="display: none;" hidden>
        <!-- PHP variables being passed to JavaScript -->
        <?php global $base_url ?>
        <script>
            Drupal.pageData.baseURL = <?php echo json_encode($base_url) ?>;
        </script>
    </div>
    <div class="top-nav">
        <ul>
            <li>Logged in as <?php echo $fullname ;?> </li>
            <li id="nav-administer"><a href="#">Administer</a>
                <ul id="sub-nav-administer">
                    <?php if( $userinfo->isSiteAdministrator() ){ ?>
                    <li><a id="nav-changepassword" href="<?php echo($base_url); ?>/raptor/changepassword">Change Password</a></li>
                    <li><a id="nav-manageusers" href="<?php echo($base_url); ?>/raptor/manageusers">Manage Users</a></li>
                    <?php } ?>
                    <?php if( !$userinfo->isSiteAdministrator() ){ ?>
                    <li><a id="nav-editprofile" href="<?php echo($base_url); ?>/raptor/edituser?uid=<?php echo($userinfo->getUserID()); ?>">Edit Profile</a></li>
                    <?php } ?>
                    <?php if($userprivs['ECIR1'] == 1){ ?>
                    <li><a id="nav-managecontraindications" href="<?php echo($base_url); ?>/raptor/managecontraindications">Manage Contraindications</a></li>
                    <?php } ?>
                    <?php if($userprivs['UNP1'] == 1 || $userprivs['REP1'] == 1){ ?>
                    <li><a id="nav-manageprotocolLibpage" href="<?php echo($base_url); ?>/raptor/manageprotocollib">Manage Protocols</a></li>
                    <?php } ?>
                    <?php 
                    $allow = $userprivs['ELCO1'] + $userprivs['ELHO1'] + $userprivs['ELRO1'] 
                            + $userprivs['ELSO1'] + $userprivs['ELSVO1'] 
                            + $userprivs['EECC1'] + $userprivs['EERL1']
                            + $userprivs['EARM1'];
                    if($allow > 0){ 
                    ?>
                    <li><a id="nav-managelists" href="<?php echo($base_url); ?>/raptor/managelists">Manage Lists</a></li>
                    <?php } ?>
                    
                    <?php if($userprivs['VREP1'] == 1 || $userprivs['VREP2'] == 1){ ?>
                    <li><a id="nav-viewReports" href="<?php echo($base_url); ?>/raptor/viewReports">View Reports</a></li>
                    <?php } ?>
                </ul>
            </li>
            <li><a title="<?php echo('Click for the '.$userinfo->getUserName(). ' user to exit RAPTOR.'); ?>" href="/drupal/user/logout">Logout</a></li>
        </ul>

    </div> <!--! end of top nav -->

    <div class="bottom-bar" style="width:100%;" >
        <div class="top-wrapper" >
            <div class="logo" style="display: inline-block; width:200px;" ></div>
            <div class="workflow-state-phrase" style="<?php echo($sWorkflowStateCSS); ?>" >
                <span><?php echo($sWorklflowStatePhrase); ?></span>
            </div>
        </div> <!--! end of top wrapper -->
    </div> <!--! end of bottom bar -->
</div> <!--! end of top -->

<div class="bottom-bar"></div>

<div id="administer-modal" style="display:none;" title="Administer">
    <div class="change-columns-modal">
        Loading&hellip;
    </div>
</div> <!--! end of modal -->
