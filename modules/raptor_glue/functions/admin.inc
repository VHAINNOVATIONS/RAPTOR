<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *  
 * Functions only for admin user
 */

function raptor_seeallurls_form() 
{
    return drupal_get_form("raptor_seeallurls_form_builder");
}

function raptor_adminprotocollib_xmldatadump_form() 
{
    return drupal_get_form('raptor_adminprotocollib_xmldatadump_form_builder');
}

function raptor_adminprotocollibdatadump_form() 
{
    return drupal_get_form('raptor_adminprotocollibdatadump_form_builder');
}


function raptor_admincoredatadump_form() 
{
    return drupal_get_form('raptor_admincoredatadump_form_builder');
}

function raptor_glue_runtests_form_builder($form, $form_state)
{
	throw new \Exception('Feature not yet implemented');
}

function raptor_glue_runtests_form_builder_submit($form, &$form_state)
{
	throw new \Exception('Feature not yet implemented');
}

function raptor_adminuserdatadump_form() 
{
    return drupal_get_form('raptor_adminuserdatadump_form_builder');
}

function raptor_adminuser_xmldatadump_form() 
{
    return drupal_get_form('raptor_adminuser_xmldatadump_form_builder');
}


function raptor_resetdatafordemo_form() 
{
    return drupal_get_form("raptor_resetdatafordemo_form_builder");
}

function raptor_dataexports_form() 
{
    return drupal_get_form("raptor_dataexports_form_builder");
}

/**
 * Send a downloadable file to the browser
 * @param type $form_state
 * @return binary file
 */
function raptor_admincoredatadump_form_builder($form, &$form_state)
{
	throw new \Exception('Feature not yet implemented');
}

function raptor_glue_convertForXML($record,$indent='',$textlist=array(),$ignorefields=array(),$textclue='_tx')
{
    $attribs = '';
    $textnodes = '';
    foreach($record as $key=>$value)
    {
        if(!in_array($key,$ignorefields))
        {
            $cleanvalue = htmlspecialchars($value);
            if(in_array($key, $textlist) || strrpos($key,$textclue) !== FALSE)
            {
                $textnodes .= "\n$indent<Text type='$key'>$cleanvalue</Text>";
            } else {
                $attribs .= " $key='$cleanvalue'";
            }
        }
    }
    return array('attribs'=>$attribs,'textnodes'=>$textnodes);
}

/**
 * Send a downloadable file to the browser
 */
function raptor_adminprotocollib_xmldatadump_form_builder($form, &$form_state)
{
    //Log this.
    global $user;
    error_log('Exporting User Accounts to XML as user ' . print_r($user,TRUE));

    $ar_extraattribs['raptor_build_id'] = RAPTOR_BUILD_ID;
    $ar_extraattribs['raptor_config_id'] = RAPTOR_CONFIG_ID;
    $ar_extraattribs['vista_site'] = VISTA_SITE;
    $ar_extraattribs['export_util_version'] = '20150630.2';
    $extracontextinfo = 'These are the protocol library entries as found in the RAPTOR application for one site.';

    $extraattribs = '';
    foreach($ar_extraattribs as $attribname=>$attribvalue)
    {
       $extraattribs .= " $attribname=\"$attribvalue\"";
    }
    $dumpdate = date('c');
    
    $sXML = '<?xml version="1.0"?>';
    try
    {
        $tablename = 'raptor_protocol_lib';
        $result = db_select($tablename, 'n')
                        ->fields('n')
                        ->orderBy('modality_abbr')
                        ->orderBy('protocol_shortname')
                        ->execute();
        $sXML .= "\n<RaptorProtocolLibrary export_dt='$dumpdate' $extraattribs>";
        $sXML .= "\n\t<Context>$extracontextinfo</Context>";
        while($record = $result->fetchAssoc())
        {
            $protocol_shortname = $record['protocol_shortname'];
            $bundle1 = raptor_glue_convertForXML($record,"\t\t",array('name'),array('protocol_shortname'));
            $attribs1 = $bundle1['attribs'];
            $textnodes1 = $bundle1['textnodes'];
            $sXML .= "\n\t<Protocol $attribs1 >$textnodes1";

            $childtables = array(
                 'raptor_protocol_template'=>array('wrappernodename'=>NULL,'nodename'=>'Template','orderby'=>array())
                ,'raptor_protocol_keywords'=>array('wrappernodename'=>'Keywords','nodename'=>'Keyword','orderby'=>array('weightgroup'))
                ,'raptor_protocol_code_maps'=>array('wrappernodename'=>'CodeMaps','nodename'=>'CodeMap','orderby'=>array())
            );
            foreach($childtables as $tablename2=>$details)
            {
                $wrappernodename = $details['wrappernodename'];
                $nodename = $details['nodename'];
                if($wrappernodename != NULL)
                {
                    $i1 = "\t\t";
                    $i2 = "\t\t\t";
                    $wn1 = "\n\t<$wrappernodename>";
                    $wn2 = "\n\t</$wrappernodename>";
                } else {
                    $i1 = "\t\t\t";
                    $i2 = "\t\t\t\t";
                    $wn1 = "";
                    $wn2 = "";
                }
                $query = db_select($tablename2, 'n')
                        ->fields('n')
                        ->condition('protocol_shortname',$protocol_shortname,'=');
                foreach($details['orderby'] as $orderbyfieldname)
                {
                    $query->orderBy($orderbyfieldname);
                }
                $result2 = $query->execute();
                if($result2->rowCount() > 0)
                {
                    while($record2 = $result2->fetchAssoc())
                    {
                        $bundle2 = raptor_glue_convertForXML($record2,$i2,array('name'),array('protocol_shortname'));
                        $attribs2 = $bundle2['attribs'];
                        $nodes2 = $bundle2['textnodes'];
                        if($nodes2 > '')
                        {
                            $sXML .= "$wn1\n$i1<$nodename $attribs2>$nodes2\n$i1</$nodename>$wn2";
                        } else {
                            $sXML .= "$wn1\n$i1<$nodename $attribs2 />$wn2";
                        }
                    }
                }
            }
            $sXML .= "\n\t</Protocol>";
        }
        $sXML .= "\n</RaptorProtocolLibrary>";
        
    } catch (\Exception $ex) {
        throw $ex;
    }
    
    //Dump it.
    $now = date('Y-m-d H:i:s');
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=raptor_protocollib_rsite".VISTA_SITE."_$now.xml");
    header("Content-Type: application/octet-stream; "); 
    header("Content-Transfer-Encoding: binary");
    
    echo $sXML;

    drupal_exit();  //Otherwise more stuff gets added to the file.
}

/**
 * Send a downloadable file to the browser
 * @param type $form_state
 * @return binary file
 */
function raptor_adminprotocollibdatadump_form_builder($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }
    
    //Log this.
    error_log('Dumping the protocol library information from the ' . print_r($userinfo,TRUE));

    //Dump it.
    $now = date('Y-m-d H:i:s');
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    //header("Content-Length: 64000;");
    header("Content-Disposition: attachment; filename=raptor_protocollib_rsite".VISTA_SITE."_$now.raw");
    header("Content-Type: application/octet-stream; "); 
    header("Content-Transfer-Encoding: binary");
    echo '### RAPTOR DATA DUMPED ' . $now;
    echo "\n### TIMEZONE " . date_default_timezone_get();
    echo "\n### PROTOCOL LIB DATA DUMP TOOL VERSION 20150630.1";
    $extraattribs['raptor_build_id'] = RAPTOR_BUILD_ID;
    $extraattribs['raptor_config_id'] = RAPTOR_CONFIG_ID;
    $extraattribs['vista_site'] = VISTA_SITE;
    $extracontextinfo = 'These are the protocol library entries as found in the RAPTOR application for one site.';
    foreach($extraattribs as $name=>$value)
    {
        echo "\n### $name=$value";
    }
    echo "\n### $extracontextinfo";
    
    $tablename = 'raptor_protocol_lib';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('modality_abbr')
            ->orderBy('protocol_shortname')
            ->execute();
    $sDataDump = serialize($result->fetchAllAssoc('protocol_shortname'));
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sDataDump";
    
    $tablename = 'raptor_protocol_template';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('protocol_shortname')
            ->execute();
    
    $sDataDump = serialize($result->fetchAllAssoc('protocol_shortname'));
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sDataDump";

    $tablename = 'raptor_protocol_keywords';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('protocol_shortname')
            ->orderBy('weightgroup')
            ->execute();
    $sDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sDataDump";

    $tablename = 'raptor_protocol_code_maps';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('protocol_shortname')
            ->execute();
    $sDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sDataDump";

    drupal_exit();  //Otherwise more stuff gets added to the file.
}

/**
 * Send a downloadable file to the browser
 */
function raptor_adminuser_xmldatadump_form_builder($form, &$form_state)
{
    //Log this.
    global $user;
    error_log('Exporting User Accounts to XML as user ' . print_r($user,TRUE));

    $ar_extraattribs['raptor_build_id'] = RAPTOR_BUILD_ID;
    $ar_extraattribs['raptor_config_id'] = RAPTOR_CONFIG_ID;
    $ar_extraattribs['vista_site'] = VISTA_SITE;
    $ar_extraattribs['export_util_version'] = '20150630.2';
    $extracontextinfo = 'These are the user accounts as found in the RAPTOR application for one site.';

    $extraattribs = '';
    foreach($ar_extraattribs as $attribname=>$attribvalue)
    {
       $extraattribs .= " $attribname=\"$attribvalue\"";
    }
    $dumpdate = date('c');
    
    $sXML = '<?xml version="1.0"?>';
    try
    {
        $tablename = 'raptor_user_profile';
        $result = db_select($tablename, 'n')
                        ->fields('n')
                        ->orderBy('role_nm')
                        ->orderBy('username')
                        //->condition('role_nm','Site Administrator','<>')
                        ->condition('uid',1,'<>')   //Do NOT export the CORE admin account.
                        ->execute();
        $sXML .= "\n<RaptorUsers export_dt='$dumpdate' $extraattribs>";
        $sXML .= "\n\t<Context>$extracontextinfo</Context>";
        while($record = $result->fetchAssoc())
        {
            $uid = $record['uid'];
            $bundle1 = raptor_glue_convertForXML($record,"\t\t",array('worklist_cols'));
            $attribs1 = $bundle1['attribs'];
            $textnodes1 = $bundle1['textnodes'];
            $sXML .= "\n\t<User $attribs1 >$textnodes1";

            $childtables = array(
                 'raptor_user_modality'=>array('wrappernodename'=>'Modalities','nodename'=>'Modality','orderby'=>array('modality_abbr'))
                ,'raptor_user_anatomy'=>array('wrappernodename'=>'Keywords','nodename'=>'Keyword','orderby'=>array('weightgroup'))
            );
            foreach($childtables as $tablename2=>$details)
            {
                $wrappernodename = $details['wrappernodename'];
                $nodename = $details['nodename'];
                $query = db_select($tablename2, 'n')
                        ->fields('n')
                        ->condition('uid',$uid,'=');
                foreach($details['orderby'] as $orderbyfieldname)
                {
                    $query->orderBy($orderbyfieldname);
                }
                $result2 = $query->execute();
                if($result2->rowCount() > 0)
                {
                    $sXML .= "\n\t\t<$wrappernodename>";
                    while($record2 = $result2->fetchAssoc())
                    {
                        $bundle2 = raptor_glue_convertForXML($record2,"\t\t\t\t",array(),array('uid'));
                        $attribs2 = $bundle2['attribs'];
                        $nodes2 = $bundle2['textnodes'];
                        if($nodes2 > '')
                        {
                            $sXML .= "\n\t\t\t<$nodename $attribs2>$nodes2\n\t\t</$nodename>";
                        } else {
                            $sXML .= "\n\t\t\t<$nodename $attribs2 />";
                        }
                    }
                    $sXML .= "\n\t\t</$wrappernodename>";
                }
            }
            $sXML .= "\n\t</User>";
        }
        $sXML .= "\n</RaptorUsers>";
        
    } catch (\Exception $ex) {
        throw $ex;
    }
    
    //Dump it.
    $now = date('Y-m-d H:i:s');
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=raptor_useraccounts_rsite".VISTA_SITE."_$now.xml");
    header("Content-Type: application/octet-stream; "); 
    header("Content-Transfer-Encoding: binary");
    
    echo $sXML;

    drupal_exit();  //Otherwise more stuff gets added to the file.
}

/**
 * Send a downloadable file to the browser
 */
function raptor_adminuserdatadump_form_builder($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }
    
    //Log this.
    error_log('Dumping the user account information from the ' . print_r($userinfo,TRUE));

    //Dump it.
    $now = date('Y-m-d H:i:s');
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    //header("Content-Length: 64000;");
    header("Content-Disposition: attachment; filename=raptor_useraccounts_rsite".VISTA_SITE."_$now.raw");
    header("Content-Type: application/octet-stream; "); 
    header("Content-Transfer-Encoding: binary");
    echo '### RAPTOR DATA DUMPED ' . $now;
    echo "\n### TIMEZONE " . date_default_timezone_get();
    echo "\n### USER DATA DUMP TOOL VERSION 20150630.2";
    $extraattribs['raptor_build_id'] = RAPTOR_BUILD_ID;
    $extraattribs['raptor_config_id'] = RAPTOR_CONFIG_ID;
    $extraattribs['vista_site'] = VISTA_SITE;
    $extracontextinfo = 'These are the user accounts as found in the RAPTOR application for one site.';
    foreach($extraattribs as $name=>$value)
    {
        echo "\n### $name=$value";
    }
    echo "\n### $extracontextinfo";
    
    $tablename = 'raptor_user_profile';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('role_nm')
            ->orderBy('username')
            //->condition('role_nm','Site Administrator','<>')
            ->condition('uid',1,'<>')   //Do NOT export the CORE admin account.
            ->execute();
    $sUserDataDump = serialize($result->fetchAllAssoc('uid'));
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";
    
    $tablename = 'raptor_user_modality';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('uid')
            ->orderBy('modality_abbr')
            ->execute();
    $sUserDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";

    $tablename = 'raptor_user_modality_override';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('uid')
            ->orderBy('modality_abbr')
            ->execute();
    $sUserDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";

    $tablename = 'raptor_user_anatomy';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('uid')
            ->execute();
    $sUserDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";

    $tablename = 'raptor_user_anatomy_override';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('uid')
            ->execute();
    $sUserDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";

    $tablename = 'raptor_user_group_membership';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('uid')
            ->execute();
    $sUserDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";
    
    $tablename = 'raptor_user_service';
    $result = db_select($tablename, 'n')
            ->fields('n')
            ->orderBy('uid')
            ->execute();
    $sUserDataDump = serialize($result->fetchAll());
    echo "\n>>>tablename=" . $tablename;
    echo "\n>>>rows=" . $result->rowCount();
    echo "\n>>>data=$sUserDataDump";
    drupal_exit();  //Otherwise more stuff gets added to the file.
}

function raptor_resetdatafordemo_form_builder($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }

    $loaded = module_load_include('inc','raptor_datalayer','install/LoadUtils');
    if(!$loaded)
    {
        drupal_set_message('Failed to load the LoadUtils class', 'error');
    }
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Reset Everything')
    , '#attributes' => array('title' => 'Reset everything for demo purposes.')
    );
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Reset Core Ticket Data')
    , '#attributes' => array('title' => 'Reset ticket workflow statuses.')
    );
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Reset User Accounts')
    , '#attributes' => array('title' => 'Reset all user account information.')
    );
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Reset ProtocolLib Data')
    , '#attributes' => array('title' => 'Reset all protocol library information.')
    );
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Reset Contraindication Data')
    , '#attributes' => array('title' => 'Reset all contraindication measures and rules.')
    );
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Exit')
    , '#attributes' => array('title' => 'Done with all operations, go back to main page.')
    );

    //drupal_exit();
    return $form;
}

function raptor_resetdatafordemo_form_builder_submit($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }
    
    //TODO remove this once protocol lib is converted from config file raptor_debugtemp();

    $clickedbutton = $form_state['clicked_button'];
    $clickedvalue = $clickedbutton['#value'];
    error_log("PROCESSING '$clickedvalue' BUTTON IN RESET ROUTINE for user ".print_r($userinfo,TRUE));

    //drupal_set_message('Clicked button = [' . $clickedvalue . ']');
    if($clickedvalue == t('Exit'))
    {
        $form_state['redirect'] = array(
            'worklist',
            array(),
          );
    } else {
        //Stay on same page!
        if(!module_load_include('inc','raptor_datalayer','install/LoadUtils'))
        {
            die('Could NOT include the LoadUtils class!');
        }
        $resetall = ($clickedvalue == t('Reset Everything'));
        if($resetall)
        {
            $resetcoredata = TRUE;
            $resetusers = TRUE;
            $resetprotocollib = TRUE;
            $resetcontraindications = TRUE;
        } else {
            $resetcoredata = ($clickedvalue == t('Reset Core Ticket Data'));
            $resetusers=($clickedvalue == t('Reset User Accounts'));
            $resetprotocollib=($clickedvalue == t('Reset ProtocolLib Data'));
            $resetcontraindications=($clickedvalue == t('Reset Contraindication Data'));
        }

        $didsomething = FALSE;
        if($resetusers)
        {
            \raptor\LoadUtils::loadUserBasedata(TRUE);
            drupal_set_message('User accounts have been reset as of ' . date("Y-m-d H:i:s", time()));
            $didsomething = TRUE;
        }
        if($resetcontraindications)
        {
            if(!module_load_include('inc','raptor_contraindications','install/CILoadUtils'))
            {
                die('Could NOT include the CILoadUtils class!');
            }
            \raptor\CILoadUtils::loadContraindicationBasedata(TRUE);
            drupal_set_message('Contraindication measures and rules have been reset as of ' . date("Y-m-d H:i:s", time()));
            $didsomething = TRUE;
        }
        if($resetprotocollib)
        {
            \raptor\LoadUtils::loadProtocolLibBasedata(TRUE);
            drupal_set_message('ProptocolLib data has been reset as of ' . date("Y-m-d H:i:s", time()));
            $didsomething = TRUE;
        }
        if($resetcoredata)
        {
            $didsomething = TRUE;
            //Remove all the existing tracking records.
            error_log("REMOVING CORE TRACKING DATA!!!!");
            $num = db_delete('raptor_ticket_tracking')
                    ->execute();
            $num = db_delete('raptor_ticket_workflow_history')
                    ->execute();
            $num = db_delete('raptor_ticket_collaboration')
                    ->execute();
            $num = db_delete('raptor_ticket_lock_tracking')
                    ->execute();
            $num = db_delete('raptor_schedule_track')
                    ->execute();
            $num = db_delete('raptor_schedule_track_replaced')
                    ->execute();
            $num = db_delete('raptor_ticket_protocol_settings')
                    ->execute();
            $num = db_delete('raptor_ticket_protocol_settings_replaced')
                    ->execute();
            $num = db_delete('raptor_ticket_checklist')
                    ->execute();
            $num = db_delete('raptor_ticket_checklist_replaced')
                    ->execute();
            $num = db_delete('raptor_ticket_contraindication')
                    ->execute();

            $updated_dt = date("Y-m-d H:i:s", time());
            $num = db_insert('raptor_ticket_tracking')
                    ->fields(array(
                        'siteid' => VISTA_SITE,
                        'IEN' => 2029,
                        'workflow_state' => 'AP',
                        'updated_dt' => $updated_dt,
                    ))
                    ->execute();
            $num = db_insert('raptor_ticket_tracking')
                    ->fields(array(
                        'siteid' => VISTA_SITE,
                        'IEN' => 2929,
                        'workflow_state' => 'AP',
                        'updated_dt' => $updated_dt,
                    ))
                    ->execute();
            $num = db_insert('raptor_ticket_tracking')
                    ->fields(array(
                        'siteid' => VISTA_SITE,
                        'IEN' => 2051,
                        'workflow_state' => 'IA',
                        'suspended_dt' => $updated_dt,
                        'updated_dt' => $updated_dt,
                    ))
                    ->execute();
            $num = db_insert('raptor_ticket_tracking')
                    ->fields(array(
                        'siteid' => VISTA_SITE,
                        'IEN' => 2052,
                        'workflow_state' => 'EC',
                        'exam_completed_dt' => $updated_dt,
                        'updated_dt' => $updated_dt,
                    ))
                    ->execute();
            $num = db_insert('raptor_ticket_tracking')
                    ->fields(array(
                        'siteid' => VISTA_SITE,
                        'IEN' => 2054,
                        'workflow_state' => 'EC',
                        'exam_completed_dt' => $updated_dt,
                        'updated_dt' => $updated_dt,
                    ))
                    ->execute();
            drupal_set_message('Core ticket data has been reset as of ' . date("Y-m-d H:i:s", time()));
        }
        if($didsomething == TRUE)
        {
            drupal_set_message('Because of browser cookies the only reliable way to use RAPTOR now is to close your current browser instances and start with new browser instance.','warning');
        }
    }
}





function raptor_seeallurls_form_builder($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }

    
    //Get all the URLs from each module.
    $aModules = array();
    $aModules[] = 'raptor_glue';
    $aModules[] = 'raptor_reports';
    $aModules[] = 'raptor_contraindications';
    $aModules[] = 'raptor_imageviewing';
    $aModules[] = 'raptor_scheduling';
    $aModules[] = 'raptor_workflow';
    $aModules[] = 'raptor_datalayer';
    /*
    $aModules[] = 'raptor_protocollib';
    $aModules[] = 'raptor_formulas';
    $aModules[] = 'raptor_graph';
     */
    
    global $base_url;
    
    $html = '<p>This listing is for developers of the RAPTOR application to debug configuration</p>';
    foreach($aModules as $sModuleName)
    {
        $nCount = 0;
        $oFunction = $sModuleName.'_menu';
        $aItems = $oFunction();
        $sTitle = 'URLs from the '.strtoupper($sModuleName). ' module';
        $html .= "<h2>$sTitle</h2>";
        $html .= '<table border=1 padding=1 width="100%">';
        $html .= '<tr><th>#</th><th>URL</th><th>Title</th><th>Description</th></tr>';
        if(!is_array($aItems) || count($aItems) < 1)
        {
                $html .= '<tr>';
                $html .= '<td colspan="3"> NO URLS </td>';
                $html .= '</tr>';
        } else {
            asort($aItems);
            foreach($aItems as $key=>$value)
            {
                $nCount++;
                $html .= '<tr>';
                $html .= '<td>'.$nCount.'</td>';
                $html .= '<td><a href="'.$base_url.'/'.$key.'">'.$key.'</a></td>';
                $html .= '<td>'.$value['title'].'</td>';
                $html .= '<td>'.$value['description'].'</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        $html .= '<br>';
    }
    $html .= '</ol>';
    
    $form = array();
    $form[] = array('#markup' => $html );
    
    return $form;
}

function raptor_dataexports_form_builder($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }

    $loaded = module_load_include('inc','raptor_datalayer','install/LoadUtils');
    if(!$loaded)
    {
        drupal_set_message('Failed to load the LoadUtils class', 'error');
    }
    
    $form[]['intro'] = array('#markup'=>'<p>Use this page to export portions of the RAPTOR database during development activities.</p>');
    
    $form[]['users_button'] = array('#type' => 'submit'
    , '#value' => t('Export User Account Data')
    , //'#attributes' => array('title' => 'Export data to a file')
    );
    $form[]['ci_button'] = array('#type' => 'submit'
    , '#value' => t('Export Contraindication Data')
    , //'#attributes' => array('title' => 'Export data to a file')
    );
    $form[]['plib_button'] = array('#type' => 'submit'
    , '#value' => t('Export ProtocolLib Data')
    , //'#attributes' => array('title' => 'Export data to a file')
    );
    $form[]['done_button'] = array('#type' => 'submit'
    , '#value' => t('Exit')
    , '#attributes' => array('title' => 'Done with all operations, go back to main page.')
    );

    //drupal_exit();
    return $form;
}

function raptor_dataexports_form_builder_submit($form, &$form_state)
{
    //Make sure only authorized user can do this.
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    if(!$userinfo->isSiteAdministrator())
    {
        drupal_set_message('Only a Site Administrator can access this URL.','error');
        return array();
    }
    
    $clickedbutton = $form_state['clicked_button'];
    $clickedvalue = $clickedbutton['#value'];
    if($clickedvalue == t('Exit'))
    {
        $form_state['redirect'] = array(
            'worklist',
            array(),
          );
    } else {
        $exportall = ($clickedvalue == t('Export Everything'));
        if($exportall)
        {
            drupal_set_message('Export all is not implemented!','warning');
        } else {
            $exportusers=($clickedvalue == t('Export User Account Data'));
            $exportplib=($clickedvalue == t('Export ProtocolLib Data'));
            $exportci = ($clickedvalue == t('Export Contraindication Data'));
        }
        
        if($exportusers)
        {
            $form_state['redirect'] = array(
                'raptor/users/exportdata',
                array(),
            );
        } else 
        if($exportplib)
        {
            $form_state['redirect'] = array(
                'raptor/protocollib/exportdata',
                array(),
            );
        } else 
        if($exportci)
        {
            $form_state['redirect'] = array(
                'raptor/contraindications/exportdata',
                array(),
            );
        } else {
            drupal_set_message('Did not recognize "'.$clickedvalue.'"','error');
        }
    }
}
