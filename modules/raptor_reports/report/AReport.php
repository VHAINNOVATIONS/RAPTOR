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
    private $m_hide_from_list = NULL;

    function __construct($required_privs,$menukey,$reportname,$hide_from_list=FALSE)
    {
        $this->m_required_privs = $required_privs;
        $this->m_name = $reportname;
        $this->m_menukey = $menukey;
        $this->m_hide_from_list = $hide_from_list;
    }
            
    /**
     * Return the report name
     */
    public function getName()
    {
        return $this->m_name;
    }
    
    /**
     * If TRUE, then a user can only run this report if they know the URL themselves.
     */
    public function hideFromList()
    {
        return $this->m_hide_from_list;
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
    function getFieldValues($myvalues=NULL)
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
    public function downloadReport($downloadtype, $form_state, $myvalues)
    {
        $now = date('Y-m-d H:i:s');
        $report_start_date = isset($myvalues['report_start_date']) ? $myvalues['report_start_date'] : NULL;
        $filename_insert = isset($myvalues['filename_insert']) ? '_'.$myvalues['filename_insert'] : '';
        $filesuffix = strtolower($downloadtype);
        $shortname = $this->getUniqueShortname();
        if($report_start_date > '')
        {
            $exportfilename = "raptor_report_{$shortname}_rs".VISTA_SITE 
                    . "{$filename_insert}_from_{$report_start_date}_until_{$now}.$filesuffix";
        } else {
            $exportfilename = "raptor_report_{$shortname}_rs".VISTA_SITE 
                    . "{$filename_insert}_all_until_{$now}.$filesuffix";
        }
        
        $downloadmap = $this->getDownloadTypes();        
        $downloaddetails = $downloadmap[$downloadtype];
        $delimiter = $downloaddetails['delimiter'];
        
        if(!$this->isDownloadSupported($downloadtype, $form_state, $myvalues))
        {
            throw new \Exception("Download $shortname of type '$downloadtype' is NOT supported");
        }
        if(isset($myvalues['reportdata']))
        {
            //Improved approach
            $rowdata = $myvalues['reportdata']['rowdata'];
        } else {
            if(isset($myvalues['rowdata']))
            {
                //Support legacy approach
                $rowdata = $myvalues['rowdata'];
            } else {
                throw new \Exception("Did NOT find any ROW DATA to export!");
            }
        }
        //Export it.
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        //header("Content-Length: 64000;");
        header("Content-Disposition: attachment; filename=$exportfilename");
        header("Content-Type: application/octet-stream; "); 
        header("Content-Transfer-Encoding: binary");

        $rownum=0;
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
    function getDownloadLinksMarkup($urlparams=array())
    {
        $downloadlinks = array();
        foreach($this->getDownloadTypes() as $downloadtype=>$details)
        {
            $helptext = $details['helptext'];
            $linktext = $details['linktext'];
            $exporturl = $details['downloadurl']; 
            if(count($urlparams) > 0)
            {
                //Add in the parameters
                if(FALSE === strpos($exporturl, '?'))
                {
                    $exporturl .= '?';
                }
                foreach($urlparams as $key=>$value)
                {
                    $cleanparam = urlencode($value);
                    $exporturl .= '&' . $key . '=' . $cleanparam;
                }
            }
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
                    if($key != 'special_privs')
                    {
                        //Boolean values from database
                        if($aCandidatePrivs[$key] != $value)
                        {
                            return FALSE;
                        }
                    } else {
                        //Allow for case of NULL value on the key.
                        if($value != NULL)
                        {
                            //Special privileges are custom text
                            if(!is_array($value))
                            {
                                throw new \Exception("The provided special privs declaration must be an array!  Instead got this>>>" . print_r($value,TRUE));
                            }
                            if(!isset($aCandidatePrivs['special_privs']) || !is_array($aCandidatePrivs['special_privs']))
                            {
                                //User does not have the required special privilege
                                return FALSE;
                            }
                            $reqvalue = NULL;
                            $candidate_special_privs = $aCandidatePrivs['special_privs'];   //What the user has
                            foreach($value as $onereqpriv) //Check against each one required
                            {
                                //Allow for case of NULL checks
                                if($onereqpriv != NULL)
                                {
                                    $parts_req = explode('=',$onereqpriv);
                                    if(count($parts_req) == 1)
                                    {
                                        $reqvalue = NULL;
                                    } else {
                                        $reqvalue = trim($parts_req[1]);
                                    }
                                    $okay = FALSE;
                                    foreach($candidate_special_privs as $onehaspriv)
                                    {
                                        $parts_has = explode('=',$onehaspriv);
                                        if($parts_has[0] == $parts_req[0])
                                        {
                                            if($reqvalue == NULL)
                                            {
                                                $okay = TRUE;
                                            } else {
                                                if(count($parts_has) == 2 && $parts_has[1] == $reqvalue)
                                                {
                                                    $okay = TRUE;
                                                }
                                            }
                                        }
                                    }
                                    if(!$okay)
                                    {
                                        //Does NOT have the special priv needed.
                                        return FALSE;
                                    }
                                }
                            }
                        }
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
