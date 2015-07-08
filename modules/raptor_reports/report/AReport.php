<?php
/**
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

abstract class AReport 
{
    private $m_required_privs = NULL;
    private $m_menukey = NULL;
    private $m_name = NULL;

    function __construct($required_privs,$menukey,$reportname)
    {
        $this->m_required_privs = $required_privs;
        $this->m_name = $reportname;
        $this->m_menukey = $menukey;
    }
            
    /**
     * Return the report name
     */
    public function getName()
    {
        return $this->m_name;
    }
    
    /**
     * Return the array of privs required to run this report
     */
    public function getRequiredPrivileges() 
    {
        return $this->m_required_privs;
    }
    
    /**
     * Return the menu key for this report
     */
    public function getMenuKey() 
    {
        return $this->m_menukey;
    }

    /**
     * Return a unique shortname to embed in filenames etc
     */
    public function getUniqueShortname() 
    {
        $keyparts = explode('/',$this->m_menukey);
        $lastidx = count($keyparts) - 1;
        return $keyparts[$lastidx];
    }
    
    /**
     * Return the report description
     */
    abstract function getDescription();
    
    /**
     * Some reports return initial values from this function.
     */
    function getFieldValues()
    {
        return array();
    }

    /**
     * Return associative array of supported downloads
     */
    function getDownloadTypes()
    {
        return array();
    }
    
    /**
     * Return TRUE if it is supported
     */
    function isDownloadSupported($downloadtype, $form_state, $myvalues)
    {
        $map = $this->getDownloadTypes();
        if(key_exists($downloadtype, $map))
        {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Return the URL to download the report
     */
    public function getDownloadURL($downloadtype) 
    {
        global $base_url;
        $url = "$base_url/{$this->m_menukey}?download=$downloadtype";
        return $url;
    }
    
    /**
     * Return download contents directly into the HTTP stream
     */
    function downloadReport($downloadtype, $form_state, $myvalues)
    {
        $now = date('Y-m-d H:i:s');
        $report_start_date = isset($myvalues['report_start_date']) ? $myvalues['report_start_date'] : NULL;
        $filesuffix = strtolower($downloadtype);
        $shortname = $this->getUniqueShortname();
        if($report_start_date > '')
        {
            $exportfilename = "raptor_report_{$shortname}_rs".VISTA_SITE."_from_{$report_start_date}_until_{$now}.$filesuffix";
        } else {
            $exportfilename = "raptor_report_{$shortname}_rs".VISTA_SITE."_all_until_{$now}.$filesuffix";
        }
        
        $downloadmap = $this->getDownloadTypes();        
        $downloaddetails = $downloadmap[$downloadtype];
        $delimiter = $downloaddetails['delimiter'];
        
        if(!$this->isDownloadSupported($downloadtype, $form_state, $myvalues))
        {
            throw new \Exception("Download $shortname of type '$downloadtype' is NOT supported");
        }
        
        //Export it.
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        //header("Content-Length: 64000;");
        header("Content-Disposition: attachment; filename=$exportfilename");
        header("Content-Type: application/octet-stream; "); 
        header("Content-Transfer-Encoding: binary");

        $rownum=0;
        $rowdata = $myvalues['rowdata'];
        foreach($rowdata as $row)
        {
            $rownum++;
            if($rownum == 1)
            {
                $header = array();
                foreach($row as $colname=>$coldata)
                {
                    $header[] = $colname;
                }
                $csvrow = implode($delimiter,$header);
                echo "rownum$delimiter$csvrow";
            }
            $csvrow = implode($delimiter,$row);
            echo "\n$rownum$delimiter$csvrow";
        }
        
        drupal_exit();  //Otherwise more stuff gets added to the file.
    }
    
    /**
     * Return an array with download links for all supported download types
     */
    function getDownloadLinksMarkup()
    {
        $downloadlinks = array();
        foreach($this->getDownloadTypes() as $downloadtype=>$details)
        {
            $helptext = $details['helptext'];
            $linktext = $details['linktext'];
            $exporturl = $details['downloadurl']; 
            $downloadlinks[] = "<span  title='$helptext'><a href='$exporturl'>$linktext</a></span>";
               
        }
        return $downloadlinks;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    abstract function getForm($form, &$form_state, $disabled, $myvalues);
    
    /**
     * Determine if a user has the right privs for running a report.
     * @param array $aCandidatePrivs the user privs
     * @return boolean TRUE if the requirements are satisfied for this user to run the report
     */
    public function hasRequiredPrivileges($aCandidatePrivs)
    {
        try
        {
            $aRequired = $this->getRequiredPrivileges();
            if(count($aRequired) > 0)
            {
                foreach($aRequired as $key => $value)
                {
                    if($aCandidatePrivs[$key] != $value)
                    {
                        return FALSE;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Unable to check privs for "'.$this->getName().'" because '.$ex->getMessage(),99901,$ex);
        }
        return TRUE;
    }
    
    function getExitButtonMarkup($goback='',$label='Exit')
    {
        if($goback == '')
        {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="#">'.$label.'</a>');
        } else {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="'.$goback.'">'.$label.'</a>');
        }
        return $markup;
    }
}
