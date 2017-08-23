<?php 
global $base_url;
$userinfo = $raptor_context->getUserInfo();
$fullname = trim($userinfo->getUserNameTitle() ." ". $userinfo->getFirstName() . " " . $userinfo->getLastName() . " " .$userinfo->getUserNameSuffix() . " (" . $userinfo->getRoleName() . ")");
$userprivs = $userinfo->getSystemPrivileges();
module_load_include('php', 'raptor_workflow', 'core/Menus');
$menus = new \raptor\Menus(\raptor\Menus::UICONTEXT_WORKLIST);  
$adminmenu = $menus->getAdministerMenu();
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
<!-- PHP variables being passed to JavaScript -->
<?php global $base_url ?>
<script>
    Drupal.pageData.baseURL = <?php echo json_encode($base_url) ?>;
</script>

<!-- SECTION A -->
<div class="top">
    <div class="top-nav">
        <ul>
            <li>Logged in as <?php echo $fullname ;?> </li>
            <li id="nav-administer"><a href="#">Administer</a>
                <ul id="sub-nav-administer">
                    <?php
                    foreach($adminmenu as $item)
                    {
                        $li_customattrib_text = $item['indialog'] ? '' : ' data-no-dialog';
                        $li_customattrib_text .= ' dialog-size-'.$item['size'];
                        $a_id = $item['id'];
                        $a_href = $item['url'];
                        $itemhelp = $item['helpText'];
                        if($item['enabled'])
                        {
                            $li_html = "\n<li$li_customattrib_text><a title='$itemhelp' id='$a_id' href='$a_href'>".$item['displayText']."</a></li>";
                        } else {
                            $li_html = "\n<li$li_customattrib_text><a title='$itemhelp' disabled=true id='$a_id' href='#'>".$item['displayText']."</a></li>";
                        }
                        echo $li_html;
                    }
                    ?>
             
                    
                </ul>
            </li>
            <li><a title="<?php echo('Click for the '.$userinfo->getMaskedUserName(). ' user to exit RAPTOR.'); ?>" 
                   href="<?php echo($base_url); ?>/user/logout">Logout</a></li>
        </ul>

    </div> <!--! end of top nav -->

    <div class="bottom-bar" style="width:100%;" >
        <div class="top-wrapper">
            <div title="<?php echo("Return to worklist ("); echo(RAPTOR_CONFIG_ID); echo(' | '); echo(RAPTOR_BUILD_ID); echo(")") ?>" class="logo"></div>
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
