<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *  
 */

namespace raptor;

/**
 * Provides content for menus
 *
 * @author Frank Font of SAN Business Consultants
 */
class Menus 
{
    private $m_uicontext = NULL;
    private $m_appcontext = NULL;
    
    const UICONTEXT_RAW = 'RAW';
    const UICONTEXT_ADMIN = 'ADMIN';
    const UICONTEXT_WORKLIST = 'WORKLIST';
    const UICONTEXT_PROTOCOL = 'PROTOCOL';
    
    /**
     * @param type $uicontext
     */
    public function __construct($uicontext=\raptor\Menus::UICONTEXT_RAW,$appcontext=NULL) 
    {
        $this->m_uicontext = $uicontext;
        if($appcontext == NULL)
        {
            $this->m_appcontext = \raptor\Context::getInstance();
        } else {
            $this->m_appcontext = $appcontext;
        }
    }
    
    public function getAdministerMenu()
    {
        $userinfo = $this->m_appcontext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        global $base_url;

        if($this->m_uicontext == \raptor\Menus::UICONTEXT_RAW)
        {
            $allow_indialog=FALSE;
        } else {
            $allow_indialog=TRUE;
        }
        
        $menuelements = array();
        if( $userinfo->isSiteAdministrator() )
        {
            $item = array();
            $item['id'] = 'nav-changepassword';
            $item['displayText'] = 'Change Password';
            $item['url'] = $base_url.'/raptor/changepassword';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            $item['indialog']=($allow_indialog ? TRUE : FALSE);
            $item['helpText'] = 'Change your password';
            $menuelements[] = $item;
        } else {
            $item = array();
            $item['id'] = 'nav-editprofile';
            $item['displayText'] = 'Edit Profile';
            $item['url'] = $base_url.'/raptor/editselfprofile';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            $item['indialog'] =($allow_indialog ? TRUE : FALSE);
            $item['helpText'] = 'Edit your user profile information';
            $menuelements[] = $item;
        }
        
        if($userprivs['CEUA1'] == 1 || ($userprivs['LACE1'] == 1))
        {
            $item = array();
            $item['id'] = 'nav-manageusers';
            $item['displayText'] = 'Manage Users';
            $item['url'] = $base_url.'/raptor/manageusers';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            $item['indialog'] = FALSE;
            $item['helpText'] = 'Create, edit, delete system user details';
            $menuelements[] = $item;
        }
        
        if($userprivs['ECIR1'] == 1)
        {
            $item = array();
            $item['id'] = 'nav-managecontraindications';
            $item['displayText'] = 'Manage Contraindications';
            $item['url'] = $base_url.'/raptor/managecontraindications';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            //$item['indialog'] =($allow_indialog ? TRUE : FALSE);
            $item['indialog'] = FALSE;
            $item['helpText'] = 'Create, edit, delete contraindication rules evaluated by the system';
            $menuelements[] = $item;
        }
        if($userprivs['UNP1'] == 1 && $userprivs['REP1'] == 1)
        {
            $item = array();
            $item['id'] = 'nav-manageprotocolLibpage';
            $item['displayText'] = 'Manage Protocols';
            $item['url'] = $base_url.'/raptor/manageprotocollib';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            $item['indialog']=FALSE;
            $item['helpText'] = 'Create, edit, delete the protocol library content';
            $menuelements[] = $item;
        }
        $allow = $userprivs['ELCO1'] + $userprivs['ELHO1'] + $userprivs['ELRO1'] 
                + $userprivs['ELSO1'] + $userprivs['ELSVO1'] 
                + $userprivs['EECC1'] + $userprivs['EERL1']
                + $userprivs['EARM1'];
        if($allow > 0)
        { 
            $item = array();
            $item['id'] = 'nav-managelists';
            $item['displayText'] = 'Manage Lists';
            $item['url'] = $base_url.'/raptor/managelists';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            //$item['indialog']=($allow_indialog ? TRUE : FALSE);
            $item['indialog'] = FALSE;
            $item['helpText'] = 'Crate, edit, delete content from internal lists employed by the system';
            $menuelements[] = $item;
        }

        if($userprivs['VREP1'] == 1 || $userprivs['VREP2'] == 1)
        {
            $item = array();
            $item['id'] = 'nav-viewReports';
            $item['displayText'] = 'View Reports';
            $item['url'] = $base_url.'/raptor/viewReports';
            $item['enabled']=($this->m_uicontext == \raptor\Menus::UICONTEXT_PROTOCOL ? FALSE : TRUE);
            //$item['indialog']=($allow_indialog ? TRUE : FALSE);
            $item['indialog'] = FALSE;
            $item['helpText'] = 'View available reports and execute them';
            $menuelements[] = $item;
        }

        $item = array();
        $item['id'] = 'nav-about';
        $item['displayText'] = 'About';
        $item['url'] = $base_url.'/raptor/about';
        $item['enabled']  = TRUE;
        $item['indialog'] = ($allow_indialog ? TRUE : FALSE);;
        $item['helpText'] = 'View information about RAPTOR';
        $menuelements[] = $item;
        
        return $menuelements;
    }
}
