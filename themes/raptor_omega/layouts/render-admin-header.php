<?php 
global $base_url;
if($raptor_context != NULL)
{
    $userinfo = $raptor_context->getUserInfo();
    $fullname = trim($userinfo->getUserNameTitle() 
            ." ". $userinfo->getFirstName() 
            . " " . $userinfo->getLastName() 
            . " " .$userinfo->getUserNameSuffix() 
            . " (" . $userinfo->getRoleName() . ")");
    $username = $userinfo->getUserName();
    $userprivs = $userinfo->getSystemPrivileges();
} else {
    $fullname = 'NOT LOGGED IN';
    $username=$fullname;
    $userprivs = array();
    //Bad path enable user to navigate away
    error_log('There was NO session found for the current user.  This might be due to a login conflict.');
    //die("There is a problem with your session.   Please <a href='$base_url/user/logout'>Logout</a> now.");
}
$minilogourl = $base_url.'/sites/all/themes/raptor_omega/images/topminilogo.png';
?>
<!-- PHP variables being passed to JavaScript -->
<script>
    Drupal.pageData.baseURL = <?php echo json_encode($base_url) ?>;
</script>

<div class="top">
    <a title="Click to return to worklist without saving changes" href="<?php echo($base_url); ?>/worklist"><div class="mini-logo"></div></a>
    <div class="top-nav">
        <ul>
            <li>Logged in as <?php echo $fullname ;?> </li>
            <li><a title="<?php echo('Click for the '.$username
                    . ' user to exit RAPTOR.'); ?>" 
                   href="<?php echo($base_url); ?>/user/logout">Logout</a></li>
        </ul>
    </div> <!--! end of top nav -->
</div> <!--! end of top -->

<div class="bottom-bar"></div>