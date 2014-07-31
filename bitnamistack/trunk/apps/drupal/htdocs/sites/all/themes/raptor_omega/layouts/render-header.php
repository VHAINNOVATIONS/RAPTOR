<!-- SECTION A -->
<div class="top">
    
    <?php 
    
    $userinfo = $raptor_context->getUserInfo();
    $fullname = trim($userinfo->getUserNameTitle() ." ". $userinfo->getFirstName() . " " . $userinfo->getLastName() . " " .$userinfo->getUserNameSuffix() . " (" . $userinfo->getRoleName() . ")");
    $userprivs = $userinfo->getSystemPrivileges();
    
    ?>

    <div class="top-nav">
        <ul>
            <li>Logged in as <?php echo $fullname ;?> </li>
            <li id="nav-administer"><a href="#">Administer</a>
                <ul id="sub-nav-administer">
                    <?php if( $userinfo->isSiteAdministrator() ){ ?>
                    <li><a id="nav-changepassword" href="/drupal/raptor_datalayer/changepassword">Change Password</a></li>
                    <li><a id="nav-manageusers" href="/drupal/raptor_datalayer/manageusers">Manage Users</a></li>
                    <?php } ?>
                    <?php if( !$userinfo->isSiteAdministrator() ){ ?>
                    <li><a id="nav-editprofile" href="/drupal/raptor_datalayer/edituser?uid=<?php echo($userinfo->getUserID()); ?>">Edit Profile</a></li>
                    <?php } ?>
                    <?php if($userprivs['ECIR1'] == 1){ ?>
                    <li><a id="nav-managecontraindications" href="/drupal/raptor_datalayer/managecontraindications">Manage Contraindications</a></li>
                    <?php } ?>
                    <?php if($userprivs['UNP1'] == 1 || $userprivs['REP1'] == 1){ ?>
                    <li><a id="nav-manageprotocolLibpage" href="/drupal/raptor_datalayer/manageprotocollib">Manage Protocols</a></li>
                    <?php } ?>
                    <?php 
                    $allow = $userprivs['ELCO1'] + $userprivs['ELHO1'] + $userprivs['ELRO1'] 
                            + $userprivs['ELSO1'] + $userprivs['ELSVO1'] 
                            + $userprivs['EECC1'] + $userprivs['EERL1']
                            + $userprivs['EARM1'];
                    if($allow > 0){ 
                    ?>
                    <li><a id="nav-managelists" href="/drupal/raptor_datalayer/managelists">Manage Lists</a></li>
                    <?php } ?>
                    
                    <?php if($userprivs['VREP1'] == 1 || $userprivs['VREP2'] == 1){ ?>
                    <li><a id="nav-viewReports" href="/drupal/raptor_datalayer/viewReports">View Reports</a></li>
                    <?php } ?>
                </ul>
            </li>
            <li><a title="<?php echo('Click for the '.$userinfo->getUserName(). ' user to exit RAPTOR.'); ?>" href="/drupal/user/logout">Logout</a></li>
        </ul>

        <div class="user-info">
            <div class="name">
                <p>Logged in as Dr. John Wright</p>
            </div>

            <div class="time-date-wrapper">
                <div class="time">
                    <p>8:59<span>a.m.</span> |</p>
                </div>

                <div class="date">
                    <p>28 October 2013</p>
                </div> 
            </div>
        </div> <!--! end of user info -->
    </div> <!--! end of top nav -->

    <div class="bottom-bar">
        <div class="top-wrapper">
            <div class="logo"></div> <!--! end of logo -->

            <div class="search-wrapper">
                <!-- <input type="text" name="s" id="s" class="searchfield" placeholder="Search..." tabindex="2"> -->
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
