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

module_load_include('php', 'raptor_datalayer', 'config/Choices');

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');
require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol_ajax.inc');

require_once 'FormHelper.php';
require_once 'ProtocolInfoUtility.php';

/**
 * This class returns the Admin Information input content
 *
* @author Frank Font of SAN Business Consultants
  */
class ProtocolLibPageHelper
{

    private $m_oPI = NULL;
    private $m_oContext = NULL;
    
    function __construct($oContext=NULL)
    {
        try
        {
            $this->m_oPI = new \raptor\ProtocolInfoUtility();
            if($oContext == NULL)
            {
                $this->m_oContext = \raptor\Context::getInstance();
            }
        } catch (\Exception $ex) {
            error_log("Failed ProtocolLibPageHelper because ".$ex->getMessage()
                    . "\n<br>Stack trace...<br>".Context::debugGetCallerInfo(5));
            throw new \Exception("Failed ProtocolLibPageHelper constructor because ".$ex->getMessage(), 99123, $ex);
        }
    }
    
    /**
     * Set all key values of the myvalues array as null
     * @param array $myvalues for ALL values
     */
    static function setAllValuesNull(&$myvalues)
    {
        try
        {
            $myvalues['active_yn'] = NULL;
            $myvalues['DefaultValues'] = NULL;
            $myvalues['protocol_shortname'] = NULL;
            $myvalues['name'] = NULL;
            $myvalues['version'] = 1;
            $myvalues['modality_abbr'] = NULL;
            $myvalues['active_yn'] = 1;
            $myvalues['service_nm'] = NULL;
            $myvalues['lowerbound_weight'] = NULL;
            $myvalues['upperbound_weight'] = NULL;
            $myvalues['image_guided_yn'] = 0;
            $myvalues['contrast_yn'] = 0;
            $myvalues['radioisotope_yn'] = 0;
            $myvalues['sedation_yn'] = 0;
            $myvalues['multievent_yn'] = 0;
            //$myvalues['filename'] = NULL;   //DEPRECATED
            $myvalues['protocolfile'] = NULL;
            $myvalues['created_dt'] = NULL;

            $myvalues['data_active_yn'] = NULL;
            $myvalues['protocolnotes_tx'] = NULL;
            $myvalues['examnotes_tx'] = NULL;
            $myvalues['updated_dt'] = NULL;

            $myvalues['data_active_yn'] = NULL;

            //Now clear the template stuff too.
            ProtocolLibPageHelper::setAllTemplateValuesNull($myvalues);
        } catch (\Exception $ex) {
            error_log("Failed ProtocolLibPageHelper->setAllValuesNull because ".$ex->getMessage()
                    . "\n<br>Stack trace...<br>".Context::debugGetCallerInfo(5));
            throw $ex;
        }
    }
    
    /**
     * Set all key raptor_protocol_template values of the myvalues array as null
     * @param array $myvalues for only the TEMPLATES portion of data
     */
    static function setAllTemplateValuesNull(&$myvalues)
    {
        try
        {
            $myvalues['data_active_yn'] = NULL;

            $myvalues['hydration_none_yn'] = NULL;
            $myvalues['hydration_oral_tx'] = NULL;
            $myvalues['hydration_iv_tx'] = NULL;

            $myvalues['sedation_none_yn'] = NULL;
            $myvalues['sedation_oral_tx'] = NULL;
            $myvalues['sedation_iv_tx'] = NULL;

            $myvalues['contrast_none_yn'] = NULL;
            $myvalues['contrast_enteric_tx'] = NULL;
            $myvalues['contrast_iv_tx'] = NULL;

            $myvalues['radioisotope_none_yn'] = NULL;
            $myvalues['radioisotope_enteric_tx'] = NULL;
            $myvalues['radioisotope_iv_tx'] = NULL;

            $myvalues['allergy_kw'] = NULL; //Name in the database
            $myvalues['allergy_cd'] = NULL;  //Name on the form

            $myvalues['claustrophobic_kw'] = NULL; //Name in the database
            $myvalues['claustrophobic_cd'] = NULL;  //Name on the form

            $myvalues['consent_req_kw'] = NULL; //Name in the database
            $myvalues['consentreq_radio_cd'] = NULL;  //Name on the form

            $myvalues['protocolnotes_tx'] = NULL;
            $myvalues['examnotes_tx'] = NULL;

            $myvalues['updated_dt'] = NULL;

            //Also set text values expected specifically by form controls
            $myvalues['hydration_oral_id'] = NULL;
            $myvalues['hydration_iv_id'] = NULL;
            $myvalues['hydration_oral_customtx'] = NULL;
            $myvalues['hydration_iv_customtx'] = NULL;

            $myvalues['sedation_oral_id'] = NULL;
            $myvalues['sedation_iv_id'] = NULL;
            $myvalues['sedation_oral_customtx'] = NULL;
            $myvalues['sedation_iv_customtx'] = NULL;

            $myvalues['contrast_enteric_id'] = NULL;
            $myvalues['contrast_iv_id'] = NULL;
            $myvalues['contrast_enteric_customtx'] = NULL;
            $myvalues['contrast_iv_customtx'] = NULL;

            $myvalues['radioisotope_enteric_id'] = NULL;
            $myvalues['radioisotope_iv_id'] = NULL;
            $myvalues['radioisotope_enteric_customtx'] = NULL;
            $myvalues['radioisotope_iv_customtx'] = NULL;

            $myvalues['cpt_codes'] = NULL;
            $myvalues['radlex_codes'] = NULL;
            $myvalues['icd_codes'] = NULL;
        } catch (\Exception $ex) {
            error_log("Failed ProtocolLibPageHelper->setAllTemplateValuesNull because ".$ex->getMessage()
                    . "\n<br>Stack trace...<br>".Context::debugGetCallerInfo(5));
            throw $ex;
        }
    }
    
    public function getTemplateMap($protocol_shortname)
    {
        try
        {
            $map = NULL;
            try
            {
                $result = db_select('raptor_protocol_template','p')
                        ->fields('p')
                        ->condition('protocol_shortname', $protocol_shortname, '=')
                        ->execute();
                $map = $result->fetchAssoc();
            } catch (\Exception $ex) {
                throw $ex;
            }
            return $map;
        } catch (\Exception $ex) {
            error_log("Failed ProtocolLibPageHelper->getTemplateMap because ".$ex->getMessage()
                    . "\n<br>Stack trace...<br>".Context::debugGetCallerInfo(5));
            throw $ex;
        }
    }

    /**
     * We have to backup the original values before we change them.
     */
    public function copyTemplateValuesToReplacedTable($protocol_shortname)
    {
        $replaced_dt = date("Y-m-d H:i", time());
        try
        {
            $result = db_select('raptor_protocol_template','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $protocol_shortname, '=')
                    ->execute();
            //Did we have a record to copy?
            if($result->rowCount() == 1)
            {
                $record = $result->fetchAssoc();
                $nAdded = db_insert('raptor_protocol_template_replaced')
                    ->fields(array(
                        'protocol_shortname' => $protocol_shortname,
                        'active_yn' => $record['active_yn'],
                        'hydration_none_yn' => $record['hydration_none_yn'],
                        'hydration_oral_tx' => $record['hydration_oral_tx'],
                        'hydration_iv_tx' => $record['hydration_iv_tx'],
                        'sedation_none_yn' => $record['sedation_none_yn'],
                        'sedation_oral_tx' => $record['sedation_oral_tx'],
                        'sedation_iv_tx' => $record['sedation_iv_tx'],
                        'contrast_none_yn' => $record['contrast_none_yn'],
                        'contrast_enteric_tx' => $record['contrast_enteric_tx'],
                        'contrast_iv_tx' => $record['contrast_iv_tx'],
                        'radioisotope_none_yn' => $record['radioisotope_none_yn'],
                        'radioisotope_enteric_tx' => $record['radioisotope_enteric_tx'],
                        'radioisotope_iv_tx' => $record['radioisotope_iv_tx'],
                        'consent_req_kw' => $record['consent_req_kw'],
                        'protocolnotes_tx' => $record['protocolnotes_tx'],
                        'examnotes_tx' => $record['examnotes_tx'],
                        'updated_dt' => $record['updated_dt'],
                        'replaced_dt' => $replaced_dt,
                    ))
                    ->execute();
            }
        } catch (\Exception $ex) {
            $msg = 'Cannot copy '.$protocol_shortname.' to raptor_protocol_template_replaced table because ' . $ex->getMessage();
            drupal_set_message($msg, 'error');
            //Rethrow the same exception
            throw $ex;
        }
    }
    
    /**
     * Get the blob and meta details of posted file
     */
    public function getPostedFileDetails($myvalues, $newbasename, $fieldname='protocolfile')
    {
        $details = array();
        try
        {
            if(!isset($myvalues[$fieldname]) || $myvalues[$fieldname] == '')
            {
                $details['file']=NULL;
                $details['rawfilename']=NULL;
                $details['newfilename']=NULL;
                $details['filetype']=NULL;
                $details['filesize']=NULL;
                $details['file_blob']=NULL;
                $details['fid']=NULL;
            } else {
                $details['file']=$myvalues[$fieldname];
                $details['rawfilename'] = $details['file']->filename;
                $fileinfo = pathinfo($details['rawfilename']);
                $details['newfilename'] = $newbasename
                        .'-v'.$myvalues['version']
                        .'.'.$fileinfo['extension'];
                $details['filetype'] = strtoupper($fileinfo['extension']);
                $details['filesize']=$details['file']->filesize;
                $details['fid'] = $details['file']->fid;

                //Load the raw file blob object
                $fileuri = $details['file']->uri;
                $filepath = drupal_realpath($fileuri);
                $fp      = fopen($filepath, 'r');
                $rawfilesize = filesize($filepath);
                $rawcontent = fread($fp, $rawfilesize);
                $details['file_blob'] = $rawcontent; //No need for mysqli_real_escape_string if we bind on write!
                fclose($fp);   
            }
        } catch (\Exception $ex) {
            error_log("Failed ProtocolLibPageHelper->getPostedFileDetails because ".$ex->getMessage()
                    . "\n<br>Stack trace...<br>".Context::debugGetCallerInfo(5));
            throw $ex;
        }
        return $details;
    }
    
    /**
     * We have to backup the original values before we change them.
     * @param type $protocol_shortname
     */
    public function copyKeywordsToReplacedTable($protocol_shortname)
    {
        //get all existing raptor_protocol_keywords records
        $replaced_dt = date("Y-m-d H:i", time());
        try
        {
            $result = db_select('raptor_protocol_keywords','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $protocol_shortname, '=')
                    ->execute();
            //Copy all the records, if any.
            while($record = $result->fetchAssoc()) 
            {
               $nAdded = db_insert('raptor_protocol_keywords_replaced')
                 ->fields(array(
                    'protocol_shortname' => $protocol_shortname,
                    'weightgroup' => $record['weightgroup'],
                    'keyword' => $record['keyword'],
                    'updated_dt' => $record['updated_dt'],
                    'replaced_dt' => $replaced_dt,
                ))
                ->execute();
            }
        } catch (\Exception $ex) {
            $msg = 'Cannot copy '.$protocol_shortname.' to raptor_protocol_keywords_replaced table because ' . $ex->getMessage();
            drupal_set_message($msg, 'error');
            //Rethrow the same exception
            throw $ex;
        }
    }
    
    /**
     * We have to backup the original values before we change them.
     * IMPORTANT: This method also SETS THE UPLOADED FILE FIELDS!!!!
     * @param type $protocol_shortname
     * @throws \raptor\Exception
     */
    public function copyProtocolLibToReplacedTable($protocol_shortname, &$existingfileinfo)
    {
        $replaced_dt = date("Y-m-d H:i", time());
        try
        {
            $result = db_select('raptor_protocol_lib','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $protocol_shortname, '=')
                    ->execute();
            //Did we have a record to copy?
            $existingfileinfo = NULL;
            if($result->rowCount() == 1)
            {
                $record = $result->fetchAssoc();    //There will at most be one record.
                $existingfileinfo['filename'] = $record['filename'];
                $existingfileinfo['original_filename'] = $record['original_filename'];
                $existingfileinfo['original_file_upload_dt'] = $record['original_file_upload_dt'];
                $existingfileinfo['original_file_upload_by_uid'] = $record['original_file_upload_by_uid'];
                $nAdded = db_insert('raptor_protocol_lib_replaced')
                    ->fields(array(
                        'protocol_shortname' => $protocol_shortname,
                        'name' => $record['name'],
                        'version' => $record['version'],
                        'modality_abbr' => $record['modality_abbr'],
                        'service_nm' => $record['service_nm'],
                        'lowerbound_weight' => $record['lowerbound_weight'],
                        'upperbound_weight' => $record['upperbound_weight'],
                        'image_guided_yn' => $record['image_guided_yn'],
                        'contrast_yn' => $record['contrast_yn'],
                        'radioisotope_yn' => $record['radioisotope_yn'],
                        'sedation_yn' => $record['sedation_yn'],
                        'multievent_yn' => $record['multievent_yn'],
                        'filename' => $record['filename'],
                        'active_yn' => $record['active_yn'],
                        'updated_dt' => $record['updated_dt'],
                        'replaced_dt' => $replaced_dt,
                    ))
                    ->execute();
            }
        } catch (\Exception $ex) {
            $msg = 'Cannot copy '.$protocol_shortname.' to raptor_protocol_lib_replaced table because ' . $ex->getMessage();
            drupal_set_message($msg, 'error');
            //Rethrow the same exception
            throw $ex;
        }
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues($protocol_shortname)
    {
        $myvalues = array();
        try
        {
            ProtocolLibPageHelper::setAllValuesNull($myvalues); //Always initialize because there are some extra values there we want null at the start.
            $myvalues['protocol_shortname'] = $protocol_shortname;
            $myvalues['keywords1'] = array();
            $myvalues['keywords2'] = array();
            $myvalues['keywords3'] = array();
            $myvalues['upload_comment_tx'] = NULL;
            $myvalues['filetype'] = NULL;
            $myvalues['filesize'] = NULL;

            if($protocol_shortname != NULL)
            {
                //Get highest scanned document version number first
                $scanned_version = 0;
                $protocol_version = 0;
                $result = db_select('raptor_protocol_lib_uploads','p')
                    ->fields('p')
                    ->orderBy('version', 'DESC')
                    ->condition('protocol_shortname',$protocol_shortname,'=')
                    ->execute();
                if(isset($result) && $result->rowCount()>0)
                {
                    $record = $result->fetchObject();
                    $scanned_version = $record->version;
                }

                $result = db_select('raptor_protocol_lib','p')
                        ->fields('p')
                        ->condition('protocol_shortname',$protocol_shortname,'=')
                        ->execute();

                if(isset($result) && $result->rowCount()>0)
                {
                    //Not deleted yet.
                    $record = $result->fetchObject();
                    $myvalues['DefaultValues'] = NULL;
                    $myvalues['protocol_shortname'] = $protocol_shortname;
                    $myvalues['name'] = $record->name;
                    $protocol_version = $record->version;
                    $myvalues['modality_abbr'] = $record->modality_abbr;
                    $myvalues['service_nm'] = $record->service_nm;
                    $myvalues['lowerbound_weight'] = $record->lowerbound_weight;
                    $myvalues['upperbound_weight'] = $record->upperbound_weight;
                    $myvalues['image_guided_yn'] = $record->image_guided_yn;
                    $myvalues['contrast_yn'] = $record->contrast_yn;
                    $myvalues['radioisotope_yn'] = $record->radioisotope_yn;
                    $myvalues['sedation_yn'] = $record->sedation_yn;
                    $myvalues['multievent_yn'] = $record->multievent_yn;
                    $myvalues['filename'] = $record->filename;
                    $myvalues['original_filename'] = $record->original_filename;
                    $myvalues['original_file_upload_dt'] = $record->original_file_upload_dt;
                    $myvalues['original_file_upload_by_uid'] = $record->original_file_upload_by_uid;
                    $myvalues['active_yn'] = $record->active_yn;
                    $myvalues['created_dt'] = $record->updated_dt;

                    //Now get the default values, if any.
                    $result = db_select('raptor_protocol_template','p')
                            ->fields('p')
                            ->condition('protocol_shortname',$protocol_shortname,'=')
                            ->execute();
                    if($result->rowCount()===0)
                    {
                        error_log("WARNING: NO TEMPLATE RECORDS FOUND for PROTOCOL $protocol_shortname");
                        //Set all the template keys as NULL.
                        ProtocolLibPageHelper::setAllTemplateValuesNull($myvalues);                
                    } else {
                        $record = $result->fetchObject();
                        $myvalues['data_active_yn'] = $record->active_yn;

                        $myvalues['hydration_none_yn'] = $record->hydration_none_yn;
                        $myvalues['hydration_oral_tx'] = $record->hydration_oral_tx;
                        $myvalues['hydration_oral_id'] = $record->hydration_oral_tx;
                        $myvalues['hydration_iv_tx'] = $record->hydration_iv_tx;
                        $myvalues['hydration_iv_id'] = $record->hydration_iv_tx;
                        $myvalues['hydration_oral_customtx'] = $record->hydration_oral_tx;
                        $myvalues['hydration_iv_customtx'] = $record->hydration_iv_tx;

                        $myvalues['sedation_none_yn'] = $record->sedation_none_yn;
                        $myvalues['sedation_oral_tx'] = $record->sedation_oral_tx;
                        $myvalues['sedation_oral_id'] = $record->sedation_oral_tx;
                        $myvalues['sedation_iv_tx'] = $record->sedation_iv_tx;
                        $myvalues['sedation_iv_id'] = $record->sedation_iv_tx;
                        $myvalues['sedation_oral_customtx'] = $record->sedation_oral_tx;
                        $myvalues['sedation_iv_customtx'] = $record->sedation_iv_tx;

                        $myvalues['contrast_none_yn'] = $record->contrast_none_yn;
                        $myvalues['contrast_enteric_tx'] = $record->contrast_enteric_tx;
                        $myvalues['contrast_enteric_id'] = $record->contrast_enteric_tx;
                        $myvalues['contrast_iv_tx'] = $record->contrast_iv_tx;
                        $myvalues['contrast_iv_id'] = $record->contrast_iv_tx;
                        $myvalues['contrast_enteric_customtx'] = $record->contrast_enteric_tx;  //custom field
                        $myvalues['contrast_iv_customtx'] = $record->contrast_iv_tx;            //custom field

                        $myvalues['radioisotope_none_yn'] = $record->radioisotope_none_yn;
                        $myvalues['radioisotope_enteric_tx'] = $record->radioisotope_enteric_tx;
                        $myvalues['radioisotope_enteric_id'] = $record->radioisotope_enteric_tx;
                        $myvalues['radioisotope_iv_tx'] = $record->radioisotope_iv_tx;
                        $myvalues['radioisotope_iv_id'] = $record->radioisotope_iv_tx;
                        $myvalues['radioisotope_enteric_customtx'] = $record->radioisotope_enteric_tx;
                        $myvalues['radioisotope_iv_customtx'] = $record->radioisotope_iv_tx;

                        //$myvalues['allergy_kw'] = $record->allergy_kw;
                        //$myvalues['claustrophobic_kw'] = $record->claustrophobic_kw;
                        $myvalues['consent_req_kw'] = $record->consent_req_kw;

                        $myvalues['protocolnotes_tx'] = $record->protocolnotes_tx;
                        $myvalues['examnotes_tx'] = $record->examnotes_tx;
                        $myvalues['updated_dt'] = $record->updated_dt;
                    }

                    $keyword_result = db_select('raptor_protocol_keywords','p')
                            ->fields('p')
                            ->condition('protocol_shortname',$protocol_shortname,'=')
                            ->execute();
                    if($keyword_result->rowCount() > 0)
                    {
                        foreach($keyword_result as $item)
                        {
                            if($item->weightgroup == 1)
                            {
                                $myvalues['keywords1'][] = $item->keyword;
                            } else
                            if($item->weightgroup == 2)
                            {
                                $myvalues['keywords2'][] = $item->keyword;
                            } else
                            if($item->weightgroup == 3)
                            {
                                $myvalues['keywords3'][] = $item->keyword;
                            } else {
                                die("Invalid weightgroup value for filter=" . print_r($filter, true));
                            }
                        }
                    }

                    //Get the mappings.
                    $mappings = db_select('raptor_protocol_code_maps','p')
                            ->fields('p')
                            ->condition('protocol_shortname',$protocol_shortname,'=')
                            ->execute();
                    $cpt_codes = array();
                    $radlex_codes = array();
                    $icd_codes = array();
                    if($mappings->rowCount() > 0)
                    {
                        foreach($mappings as $item)
                        {
                            if(isset($item->cpt_cd))
                            {
                                $cpt_codes[] = $item->cpt_cd;
                            }
                            if(isset($item->radlex_cd))
                            {
                                $radlex_codes[] = $item->radlex_cd;
                            }
                            if(isset($item->icd_cd))
                            {
                                $icd_codes[] = $item->icd_cd;
                            }
                        }
                    }

                    //Sort the contents
                    sort($cpt_codes);
                    sort($radlex_codes);
                    sort($icd_codes);

                    //Format for user display
                    $myvalues['cpt_codes'] = implode(', ', $cpt_codes);
                    $myvalues['radlex_codes'] = implode(', ', $radlex_codes);
                    $myvalues['icd_codes'] = implode(', ', $icd_codes);
                }
                if($protocol_version > $scanned_version)
                {
                    $myvalues['version'] = $protocol_version;
                } else if($protocol_version < $scanned_version) {
                    $myvalues['version'] = $scanned_version;
                } else {
                    //Both were zero
                    $myvalues['version'] = 1;
                }
                $myvalues['newversion'] = $myvalues['version'] + 1;
            }

            //Properly set the YN array 20140714
            $yn_attribs = array();
            if(isset($myvalues['image_guided_yn']) && $myvalues['image_guided_yn'] == 1)
            {
                $yn_attribs['IG'] = 'IG';
            }
            if(isset($myvalues['contrast_yn']) && $myvalues['contrast_yn'] == 1)
            {
                $yn_attribs['C'] = 'C';
            }
            if(isset($myvalues['radioisotope_yn']) && $myvalues['radioisotope_yn'] == 1)
            {
                $yn_attribs['RI'] = 'RI';
            }
            if(isset($myvalues['sedation_yn']) && $myvalues['sedation_yn'] == 1)
            {
                $yn_attribs['S'] = 'S';
            }
            $myvalues['yn_attribs'] = $yn_attribs;

            //Derive the hydration_radio_cd value
            if($myvalues['hydration_none_yn'] == 1)        
            {
                $myvalues['hydration_radio_cd'] = 'none';
            } else {
                if(trim($myvalues['hydration_oral_tx']) > '')
                {
                    $myvalues['hydration_radio_cd'] = 'oral'; 
                } else
                if(trim($myvalues['hydration_iv_tx']) > '')
                {
                    $myvalues['hydration_radio_cd'] = 'iv';
                } else {
                    $myvalues['hydration_radio_cd'] = NULL;
                }
            }

            //Derive the sedation_radio_cd value
            if($myvalues['sedation_none_yn'] == 1)        
            {
                $myvalues['sedation_radio_cd'] = 'none';
            } else {
                if(trim($myvalues['sedation_oral_tx']) > '')
                {
                    $myvalues['sedation_radio_cd'] = 'oral'; 
                } else
                if(trim($myvalues['sedation_iv_tx']) > '')
                {
                    $myvalues['sedation_radio_cd'] = 'iv';
                } else {
                    $myvalues['sedation_radio_cd'] = NULL;
                }
            }

            //Derive the contrast_cd value
            $cc = array();
            if(isset($myvalues['contrast_none_yn']) && $myvalues['contrast_none_yn'] == 1)
            {
                $cc['none'] = 'none';
            }
            if(isset($myvalues['contrast_enteric_tx']) && $myvalues['contrast_enteric_tx'] > '')
            {
                $cc['enteric'] = 'enteric';
            }
            if(isset($myvalues['contrast_iv_tx']) && $myvalues['contrast_iv_tx'] > '')
            {
                $cc['iv'] = 'iv';
            }
            $myvalues['contrast_cd'] = $cc;

            //Derive the radioisotope_cd value
            $cc = array();
            if(isset($myvalues['radioisotope_none_yn']) && $myvalues['radioisotope_none_yn'] == 1)
            {
                $cc['none'] = 'none';
            }
            if(isset($myvalues['radioisotope_enteric_tx']) && $myvalues['radioisotope_enteric_tx'] > '')
            {
                $cc['enteric'] = 'enteric';
            }
            if(isset($myvalues['radioisotope_iv_tx']) && $myvalues['radioisotope_iv_tx'] > '')
            {
                $cc['iv'] = 'iv';
            }
            $myvalues['radioisotope_cd'] = $cc;

            $myvalues['allergy_cd'] = $myvalues['allergy_kw'];
            $myvalues['claustrophobic_cd'] = $myvalues['claustrophobic_kw'];
            $myvalues['consentreq_radio_cd'] = $myvalues['consent_req_kw'];
        } catch (\Exception $ex) {
            error_log("Failed ProtocolLibPageHelper->getFieldValues because ".$ex->getMessage()
                    . "\n<br>Stack trace...<br>".Context::debugGetCallerInfo(5));
            throw $ex;
        }
        return $myvalues;
    }

    /**
     * Write new keywords
     * @param type $protocol_shortname
     * @param type $weightgroup
     * @param type $userpref_keywords
     * @return boolean
     */
    public function writeKeywords($protocol_shortname, $weightgroup, $userpref_keywords)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            //Now replace the existing normal records.
            foreach($userpref_keywords as $keyword)
            {
              $nAdded = db_insert('raptor_protocol_keywords')
                ->fields(array(
                  'protocol_shortname' => $protocol_shortname,
                  'weightgroup' => $weightgroup,
                  'keyword' => strtoupper($keyword),
                  'updated_dt' => $updated_dt,
                    ))
                  ->execute();
            }
            
        } catch (\Exception $ex) {
            //Leave some information we can then use to correct the problem.
            $msg = 'Cannot write keywords for '.$protocol_shortname.' because ' . $ex;
            error_log($msg);
            drupal_set_message('Cannot write keywords because ' . $ex, 'error');
            return FALSE;
        }
        
        return TRUE;
    }

    private function insertCodeMapRecords($protocol_shortname,$fieldname,$cleancodes)
    {
        $updated_dt = date("Y-m-d H:i", time());

        try
        {
            foreach($cleancodes as $cleancode)
            {
                $nAdded = db_insert('raptor_protocol_code_maps')
                ->fields(array(
                    'protocol_shortname' => $protocol_shortname,
                    $fieldname => $cleancode,
                    'updated_dt' => $updated_dt,
                ))
                ->execute();
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Store the scanned image.
     */
    public function writeFileUploadDetails($protocol_shortname, $myvalues, $file_blob=NULL)
    {
        if(isset($myvalues['upload_file_now']) && $myvalues['upload_file_now'])
        {
            try
            {
              db_insert('raptor_protocol_lib_uploads')
                ->fields(array(
                    'protocol_shortname' => $protocol_shortname,
                    'version' => $myvalues['version'],    
                    'filename' => $myvalues['filename'],
                    'original_filename' => $myvalues['original_filename'],
                    'filetype' => $myvalues['filetype'],
                    'filesize' => $myvalues['filesize'],
                    'file_blob' => $file_blob,
                    'uploaded_by_uid' => $myvalues['original_file_upload_by_uid'],
                    'comment_tx' => $myvalues['upload_comment_tx'],
                    'uploaded_dt' => $myvalues['original_file_upload_dt'],
                  ))
                    ->execute(); 
            } catch (\Exception $ex) {
                throw $ex;
            }
        }
        return TRUE;
    }
    
    public function writeCodeMappings($protocol_shortname, $myvalues, $removeExistingRecords=FALSE)
    {
        $bHappy = TRUE;
        if($removeExistingRecords)
        {
            //Delete the existing code mapping records
            $num_deleted = db_delete('raptor_protocol_code_maps')
             ->condition('protocol_shortname', $protocol_shortname,'=')
             ->execute();
        }
            
        try 
        {
            $fieldname = 'cpt_codes';
            $codes = trim($myvalues[$fieldname]);
            $codes_array = explode(',', $codes);
            $cleancodes = array();
            foreach($codes_array as $code)
            {
                $clean = trim($code);
                if(strlen($clean) > 0)
                {
                    $cleancodes[] = $clean;
                }
            }
            $this->insertCodeMapRecords($protocol_shortname,'cpt_cd',$cleancodes);
            
            $fieldname = 'radlex_codes';
            $codes = trim($myvalues[$fieldname]);
            $codes_array = explode(',', $codes);
            $cleancodes = array();
            foreach($codes_array as $code)
            {
                $clean = strtoupper(trim($code));
                if(strlen($clean) > 0)
                {
                    $cleancodes[] = $clean;
                }
            }
            $this->insertCodeMapRecords($protocol_shortname,'radlex_cd',$cleancodes);
            
            $fieldname = 'icd_codes';
            $codes = trim($myvalues[$fieldname]);
            $codes_array = explode(',', $codes);
            $cleancodes = array();
            foreach($codes_array as $code)
            {
                $clean = strtoupper(trim($code));
                if(strlen($clean) > 0)
                {
                    $cleancodes[] = $clean;
                }
            }
            $this->insertCodeMapRecords($protocol_shortname,'icd_cd',$cleancodes);
        }
        catch(\Exception $ex)
        {
            error_log("Failed to add protocol lib information into database at mapping time!\n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            drupal_set_message('Failed to add the new protocol lib information because ' . $ex->getMessage(), 'error');
            $bHappy = FALSE;
        }
        
        return $bHappy;
    }
    
    /**
     * Write new a template record.
     * @return boolean
     */
    public function writeTemplateValues($protocol_shortname, $myvalues, $removeExistingRecords=FALSE)
    {
        try 
        {
            if($removeExistingRecords)
            {
                //Delete all the existing raptor_protocol_template records for protocol_shortname
                $num_deleted = db_delete('raptor_protocol_template')
                ->condition('protocol_shortname', $protocol_shortname,'=')
                ->execute();
            }

            //Insert into raptor_protocol_template table
            $updated_dt = date("Y-m-d H:i", time());
            $active_yn = 1;
            if(!isset($myvalues['hydration_radio_cd']) || $myvalues['hydration_radio_cd'] == NULL)
            {
                $hydration_none_yn = 1;
                $hydration_oral_tx = NULL;
                $hydration_iv_tx = NULL;
            } else {
                if($myvalues['hydration_radio_cd'] == 'none')
                {
                    $hydration_none_yn = 1;
                    $hydration_oral_tx = NULL;
                    $hydration_iv_tx = NULL;
                } else {
                    $hydration_none_yn = 0;
                    if($myvalues['hydration_radio_cd'] == 'oral')
                    {
                        //$hydration_oral_tx = trim($myvalues['hydration_oral_id']) > '' ? $myvalues['hydration_oral_id'] : $myvalues['hydration_oral_customtx'];
                        $hydration_oral_tx = $this->getTextFromCustomList('hydration_oral_',$myvalues);
                        $hydration_iv_tx = NULL;
                    } else {
                        //$hydration_iv_tx = trim($myvalues['hydration_iv_id']) > '' ? $myvalues['hydration_iv_id'] : $myvalues['hydration_iv_customtx'];
                        $hydration_iv_tx = $this->getTextFromCustomList('hydration_iv_',$myvalues);
                        $hydration_oral_tx = NULL;
                    }
                }
            }
            if(!isset($myvalues['sedation_radio_cd']) || $myvalues['sedation_radio_cd'] == NULL)
            {
                $sedation_none_yn = 1;
                $sedation_oral_tx = NULL;
                $sedation_iv_tx = NULL;
            } else {
                if($myvalues['sedation_radio_cd'] == 'none')
                {
                    $sedation_none_yn = 1;
                    $sedation_oral_tx = NULL;
                    $sedation_iv_tx = NULL;
                } else {
                    $sedation_none_yn = 0;
                    if($myvalues['sedation_radio_cd'] == 'oral')
                    {
                        //$sedation_oral_tx = trim($myvalues['sedation_oral_id']) > '' ? $myvalues['sedation_oral_id'] : $myvalues['sedation_oral_customtx'];
                        $sedation_oral_tx = $this->getTextFromCustomList('sedation_oral_',$myvalues);
                        $sedation_iv_tx = NULL;
                    } else {
                        //$sedation_iv_tx = trim($myvalues['sedation_iv_id']) > '' ? $myvalues['sedation_iv_id'] : $myvalues['sedation_iv_customtx'];
                        $sedation_iv_tx = $this->getTextFromCustomList('sedation_iv_',$myvalues);
                        $sedation_oral_tx = NULL;
                    }
                }
            }
            if(!is_array($myvalues['contrast_cd']))
            {
                $contrast_none_yn = 1;
                $contrast_enteric_yn = 0;
                $contrast_iv_yn = 0;
            } else {
                //Check the array values.
                $a = $myvalues['contrast_cd'];
                $contrast_none_yn = ($a['none'] === 'none') ? 1 : 0; //MUST USE TRIPLE EQUAL!!!!
                $contrast_enteric_yn = ($a['enteric'] === 'enteric') ? 1 : 0; //MUST USE TRIPLE EQUAL!!!!
                $contrast_iv_yn = ($a['iv'] === 'iv') ? 1 : 0; //MUST USE TRIPLE EQUAL!!!!
            }
            if($contrast_none_yn == 1)
            {
                $contrast_enteric_tx = NULL;
                $contrast_iv_tx = NULL;
            } else {
                if($contrast_enteric_yn == 1)
                {
                    //$contrast_enteric_tx = trim($myvalues['contrast_enteric_id'])  > '' ? $myvalues['contrast_enteric_id'] : $myvalues['contrast_enteric_customtx'];
                    $contrast_enteric_tx = $this->getTextFromCustomList('contrast_enteric_',$myvalues);
                } else {
                    $contrast_enteric_tx = NULL;
                }
                if($contrast_iv_yn == 1)
                {
                    //$contrast_iv_tx = trim($myvalues['contrast_iv_id']) > '' ? $myvalues['contrast_iv_id'] : $myvalues['contrast_iv_customtx'];
                    $contrast_iv_tx = $this->getTextFromCustomList('contrast_iv_',$myvalues);
                } else {
                    $contrast_iv_tx = NULL;
                }
            }

            if(!is_array($myvalues['radioisotope_cd']))
            {
                $radioisotope_none_yn = 1;
                $radioisotope_enteric_yn = 0;
                $radioisotope_iv_yn = 0;
            } else {
                $a = $myvalues['radioisotope_cd'];
                $radioisotope_none_yn = $a['none'] === 'none' ? 1 : 0; // || ($a['enteric'] == 0 && $a['iv'] == 0)) ? 1 : 0;
                $radioisotope_enteric_yn = $a['enteric'] === 'enteric' ? 1 : 0; // || ($a['enteric'] == 0 && $a['iv'] == 0)) ? 1 : 0;
                $radioisotope_iv_yn = $a['iv'] === 'iv' ? 1 : 0; // || ($a['enteric'] == 0 && $a['iv'] == 0)) ? 1 : 0;
            } 
            if($radioisotope_none_yn == 1)
            {
                $radioisotope_enteric_tx = NULL;
                $radioisotope_iv_tx = NULL;
            } else {
                if($radioisotope_enteric_yn == 1)
                {
                    //$radioisotope_enteric_tx = trim($myvalues['radioisotope_enteric_id'])  > '' ? $myvalues['radioisotope_enteric_id'] : $myvalues['radioisotope_enteric_customtx'];
                    $radioisotope_enteric_tx = $this->getTextFromCustomList('radioisotope_enteric_',$myvalues);
                } else {
                    $radioisotope_enteric_tx = NULL;
                }
                if($radioisotope_iv_yn == 1)
                {
                    //$radioisotope_iv_tx = trim($myvalues['radioisotope_iv_id']) > '' ? $myvalues['radioisotope_iv_id'] : $myvalues['radioisotope_iv_customtx'];
                    $radioisotope_iv_tx = $this->getTextFromCustomList('radioisotope_iv_',$myvalues);
                } else {
                    $radioisotope_iv_tx = NULL;
                }
            }
            
//drupal_set_message(">>>>write $radioisotope_enteric_tx and $radioisotope_iv_tx  <br>rawentid={$myvalues['radioisotope_enteric_id']} <br>rawenttx={$myvalues['radioisotope_enteric_customtx']} ");

            $consent_req_kw = trim($myvalues['consentreq_radio_cd']) > '' ? $myvalues['consentreq_radio_cd'] : NULL;

            $protocolnotes_tx = trim($myvalues['protocolnotes_tx']);
            $examnotes_tx = trim($myvalues['examnotes_tx']);

            $nAdded = db_insert('raptor_protocol_template')
            ->fields(array(
                'protocol_shortname' => $protocol_shortname,
                'active_yn' => $active_yn,
                'hydration_none_yn' => $hydration_none_yn,
                'hydration_oral_tx' => $hydration_oral_tx,
                'hydration_iv_tx' => $hydration_iv_tx,
                'sedation_none_yn' => $sedation_none_yn,
                'sedation_oral_tx' => $sedation_oral_tx,
                'sedation_iv_tx' => $sedation_iv_tx,
                'contrast_none_yn' => $contrast_none_yn,
                'contrast_enteric_tx' => $contrast_enteric_tx,
                'contrast_iv_tx' => $contrast_iv_tx,
                'radioisotope_none_yn' => $radioisotope_none_yn,
                'radioisotope_enteric_tx' => $radioisotope_enteric_tx,
                'radioisotope_iv_tx' => $radioisotope_iv_tx,
                'consent_req_kw' => $consent_req_kw,
                'protocolnotes_tx' => $protocolnotes_tx,
                'examnotes_tx' => $examnotes_tx,
                'updated_dt' => $updated_dt,
            ))
            ->execute();
        }
        catch(\Exception $ex)
        {
                error_log("Failed to add protocol lib information into database!\n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                drupal_set_message('Failed to add the new protocol lib information because ' . $ex->getMessage(), 'error');
                return FALSE;
        }
      
        return TRUE;
    }
    
    private function getTextFromCustomList($rootname,$myvalues)
    {
        $result_tx = NULL;
        if($myvalues[$rootname . '_inputmode'] == 'list')
        {
            $result_tx = $myvalues[$rootname . 'id'];
        } else
        if($myvalues[$rootname . '_inputmode'] == 'customtx')
        {
            $result_tx = $myvalues[$rootname . 'customtx'];
        } else {
            $result_tx = trim($myvalues[$rootname . 'id'])  > '' ? $myvalues[$rootname . 'id'] : $myvalues[$rootname . 'customtx'];
        }
        return $result_tx;
    }
    
    /**
     * Write all the dependent protocol records
     */
    public function writeChildLibraryRecords($protocol_shortname, $myvalues, $removeExistingRecords=FALSE, $file_blob=NULL)
    {
        try
        {
            //First copy all child records to the replaced tables.
            $this->copyKeywordsToReplacedTable($protocol_shortname);
            $this->copyTemplateValuesToReplacedTable($protocol_shortname);

            if($removeExistingRecords)
            {
                //Delete all the existing keyword records.
                $num_deleted = db_delete('raptor_protocol_keywords')
                 ->condition('protocol_shortname', $protocol_shortname, '=')
                 ->execute();
            }

            //Now write all the keywords to raptor_protocol_keywords table
            $keywords1 = explode(',',$myvalues['keywords1']);
            $this->writeKeywords($protocol_shortname, 1, $keywords1);
            $keywords2 = explode(',',$myvalues['keywords2']);
            $this->writeKeywords($protocol_shortname, 2, $keywords2);
            $keywords3 = explode(',',$myvalues['keywords3']);
            $this->writeKeywords($protocol_shortname, 3, $keywords3);
        } catch (\Exception $ex) {
            error_log('Failed in writeChildRecords because '.$ex->getMessage());
            throw $ex;
        }

        //Write all the settings to raptor_protocol_template table
        $bHappy = $this->writeTemplateValues($protocol_shortname, $myvalues, $removeExistingRecords);

        if($bHappy)
        {
            //Write all the code mappings
            $bHappy = $this->writeCodeMappings($protocol_shortname, $myvalues, $removeExistingRecords);
        }

        if($bHappy && $file_blob != NULL)
        {
            //Write the uploaded file to the database
            $bHappy = $this->writeFileUploadDetails($protocol_shortname, $myvalues, $file_blob);
        }
        
        return $bHappy;
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        $aOptions = array();

        //Get all the modality options from a query
        $modality_result = db_query('SELECT modality_abbr, `modality_desc` FROM `raptor_list_modality` ORDER BY modality_abbr');
        if($modality_result->rowCount()==0)
        {
            die('Did NOT find any modality options!');
        } else {
            $modality_choices=array();
            foreach($modality_result as $item)
            {
                $modality_choices[$item->modality_abbr] = $item->modality_desc;
            }
        }

        $categoryoptions = array('IG' => t('Image Guided'), 'C' => t('Contrast'),
            'RI' => t('Radionuclide'), 'S' => t('Sedation'));

        $aOptions['categories'] = $categoryoptions;
        $aOptions['modality'] = $modality_choices;

        return $aOptions;
    }

    /**
     * @return structure good for ajax usage
     */
    public static function getAllOptionsOfModalityStructured($modality_abbr)
    {

        $oLO = new ListOptions();
        
        $hydration['oral'] = $oLO->getHydrationOptions('ORAL', $modality_abbr);
        $hydration['iv'] = $oLO->getHydrationOptions('IV', $modality_abbr);
        
        $sedation['oral'] = $oLO->getSedationOptions('ORAL', $modality_abbr);
        $sedation['iv'] = $oLO->getSedationOptions('IV', $modality_abbr);

        $contrast['enteric'] = $oLO->getContrastOptions('ENTERIC', $modality_abbr);
        $contrast['iv'] = $oLO->getContrastOptions('IV', $modality_abbr);

        $radioisotope['enteric'] = $oLO->getRadioisotopeOptions('ENTERIC', $modality_abbr);
        $radioisotope['iv'] = $oLO->getRadioisotopeOptions('IV', $modality_abbr);

        $radioisotope = -1;
        $consentreq = -1;
        $protocolnotes = -1;
        $examnotes = -1;
        

        $value = array(
            'hydration' => $hydration,
            'sedation' => $sedation,
            'contrast' => $contrast,
            'radioisotope' => $radioisotope,
            'consentreq' => $consentreq,
            'protocolnotes' => $protocolnotes,
            'examnotes' => $examnotes,
        );
        return $value;
    }
    
    private function getInputArea1($oPI, $form_state, $disabled, $myvalues, $supportEditMode=TRUE)
    {
        
        //Hydration
        $shownow = !$disabled || $oPI->hasHydrationValues($myvalues);
        $hydrationarea = $oPI->getOverallSectionRadioType($form_state
                , 'hydration', 'Hydration'
                , $disabled
                , $myvalues
                , NULL
                , TRUE
                , $shownow); 
        $form['hydration'] = $hydrationarea;
        
        //Sedation
        $shownow = !$disabled || $oPI->hasSedationValues($myvalues);
        $sedationarea = $oPI->getOverallSectionRadioType($form_state
                , 'sedation', 'Sedation'
                , $disabled
                , $myvalues
                , NULL
                , TRUE
                , $shownow); 
        $form['sedation'] = $sedationarea;
        
        //Radioisotope
        $shownow = !$disabled || $oPI->hasRadioisotopeValues($myvalues);
        $radioisotopearea = $oPI->getOverallSectionCheckboxType($form_state
                , 'radioisotope', 'Radionuclide'
                , $disabled
                , $myvalues
                , NULL
                , TRUE
                , $shownow); 
        $form['radioisotope'] = $radioisotopearea;
        
        //Contrast
        $shownow = !$disabled || $oPI->hasRadioisotopeValues($myvalues);
        $contrastarea = $oPI->getOverallSectionCheckboxType($form_state
                , 'contrast', 'Contrast'
                , $disabled
                , $myvalues
                , NULL
                , TRUE
                , $shownow); 
        $form['contrast'] = $contrastarea;

        //Consent Required
        $shownow = TRUE;
        $consentarea = $oPI->getYesNoResetRadioTypeSection('consentreq', 'Consent Required'
            , $disabled
            , $myvalues
            , NULL
            , TRUE
            , $shownow);
        $form['protocolinput'][] = $consentarea;
        /*
        $form['consent'] = $oPI->raptor_form_get_consent($form_state, $disabled, $myvalues, NULL, NULL, $supportEditMode);
        */

        return $form;
    }

    public function formatKeywordText($myvalues)
    {
        $aFormatted = array();
        if(!isset($myvalues['keywords1']))
        {
            $aFormatted['keywords1'] = '';
        } else {
            $aFormatted['keywords1'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['keywords1'], ',');
        }
        if(!isset($myvalues['keywords2']))
        {
            $aFormatted['keywords2'] = '';
        } else {
            $aFormatted['keywords2'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['keywords2'], ',');
        }
        if(!isset($myvalues['keywords3']))
        {
            $aFormatted['keywords3'] = '';
        } else {
            $aFormatted['keywords3'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['keywords3'], ',');
        }
        return $aFormatted;
    }

    /**
     * Validate the proposed values and update the form state.
     * @return true if no validation errors detected
     */
    function looksValidFormState($form, &$form_state, $formMode)
    {
        $bGood = TRUE;
        $myvalues = $form_state['values'];
        if(trim($myvalues['modality_abbr']) == '')
        {
            form_set_error('modality_abbr','You must select a modality');
            $bGood = FALSE;
        }

        if(isset($myvalues['lowerbound_weight']) && trim($myvalues['lowerbound_weight']) > '' && !is_numeric($myvalues['lowerbound_weight']))
        {
            form_set_error('lowerbound_weight','Lower bound weight must be numeric');
            $bGood = FALSE;
        } else
        if(isset($myvalues['upperbound_weight']) && trim($myvalues['upperbound_weight']) > '' && !is_numeric($myvalues['upperbound_weight']))
        {
            form_set_error('upperbound_weight','Upper bound weight must be numeric');
            $bGood = FALSE;
        } else {
          //No errors in the weight data, is the relationship between the values okay too?
          $lbw = (isset($myvalues['lowerbound_weight']) && is_numeric($myvalues['lowerbound_weight']) ? $myvalues['lowerbound_weight'] : 0);
          $ubw = (isset($myvalues['upperbound_weight']) && is_numeric($myvalues['upperbound_weight']) ? $myvalues['upperbound_weight'] : 0);
          if($ubw < $lbw)
          {
              form_set_error('upperbound_weight','Upper bound cannot be smaller than lower bound');
              $bGood = FALSE;
          }
        }

        $psn = trim($myvalues['protocol_shortname']);
        if($psn == '')
        {
            form_set_error('protocol_shortname', 'The protocol shortname cannot be empty');
            $bGood = FALSE;
        } else if(is_numeric($psn)) {
            form_set_error('protocol_shortname', 'The protocol shortname cannot be numeric');
            $bGood = FALSE;
        } else {
            if($formMode == 'A')
            {
                //Check for duplicate keys too
                $result = db_select('raptor_protocol_lib','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $myvalues['protocol_shortname'],'=')
                    ->execute();
                if($result->rowCount() > 0)
                {
                    form_set_error('protocol_shortname', 'Already have a protocol with this short name');
                    $bGood = FALSE;
                }
            }
            //Check for duplicate long name too
            $result = db_select('raptor_protocol_lib','p')
                ->fields('p')
                ->condition('protocol_shortname', $myvalues['protocol_shortname'],'<>')
                ->condition('name', $myvalues['name'],'=')
                ->execute();
            if($result->rowCount() > 0)
            {
                $record = $result->fetchAssoc();
                form_set_error('name', 'Already have a protocol with this long name (see '. $record['protocol_shortname'] .')');
                $bGood = FALSE;
            }
        }
        
        $yn_attribs = $myvalues['yn_attribs'];
        $checks = array(
            'C'=>array('Contrast','hasContrastValues','yn_attribs_c','C','contrast_cd','none'
                ,'textmap'=>array('enteric'=>'contrast_enteric_','iv'=>'contrast_iv_')),
            'RI'=>array('Radionuclide','hasRadioisotopeValues','yn_attribs_ri','C','radioisotope_cd','none'
                ,'textmap'=>array('enteric'=>'radioisotope_enteric_','iv'=>'radioisotope_iv_')),
            'S'=>array('Sedation','hasSedationValues','yn_attribs_s','R','sedation_radio_cd','none'
                ,'textmap'=>array('oral'=>'sedation_oral_','iv'=>'sedation_iv_')),
            );
        foreach($checks as $code=>$details)
        {
            $name = $details[0];
            $func = $details[1];
            $catradio = $details[2];
            $sr_type = $details[3];
            $sr_name = $details[4];
            $sr_empty_value = $details[5];
            $textmap = $details['textmap'];
            $hasvalues = FALSE; //Initialize with this assumption
            $analysis = array();
            if(isset($myvalues[$sr_name]))
            {
                //Just go by the section radio button
                if($sr_type == 'R')
                {
                    //Radio
                    $buttontypename = 'radio button';
                    $rvalue = $myvalues[$sr_name];
                    $analysis['none'] = array('flag'=>($rvalue === 'none' ? 1 : 0));
                    foreach($textmap as $typename=>$controlrootname)
                    {
                        $flagvalue = ($rvalue === $typename ? 1 : 0);
                        $analysis[$typename] = array('flag'=>$flagvalue);
                        $textvalue = $this->getTextFromCustomList($controlrootname,$myvalues);
                        if(trim($textvalue) > '')
                        {
                            if($flagvalue > 0)
                            {
                                //This is a good value scenario
                                $hasvalues = TRUE;
                            } else {
                                //This is a BAD value scenario
                                $analysis['orphantext'] = $typename;
                            }
                        }
                    }
                } else {
                    //Checkboxes
                    $buttontypename = 'checkbox';
                    $a = $myvalues[$sr_name];
                    $cbvalue = $a[$sr_empty_value];
                    $analysis['none'] = array('flag'=>($a['none'] === 'none' ? 1 : 0));
                    foreach($textmap as $typename=>$controlrootname)
                    {
                        $flagvalue = $a[$typename] === $typename ? 1 : 0;
                        $analysis[$typename] = array('flag'=>$flagvalue);
                        $textvalue = $this->getTextFromCustomList($controlrootname,$myvalues);
                        if(trim($textvalue) > '')
                        {
                            if($flagvalue > 0)
                            {
                                //This is a good value scenario
                                $hasvalues = TRUE;
                            } else {
                                //This is a BAD value scenario
                                $analysis['orphantext'] = $typename;
                            }
                        }
                    }
                }
            } else {
                //Check the text fields.
                $hasvalues =  $this->m_oPI->$func($myvalues);
            }
            //Compare to the active categories selections
            $checkboxisset = (is_array($yn_attribs) && $yn_attribs[$code] === $code);
            if($checkboxisset)
            {
                if(!$hasvalues)
                {
                    form_set_error($catradio, 'Declared category '.$name.' but no '.$name.' default value was provided');
                    $bGood = FALSE;
                }
            } else {
                if($hasvalues || $analysis['none']['flag'] != 1)
                {
                    form_set_error($catradio, 'Did not declare category '.$name.' but the "none" flag is not set as default '.$name.' value');
                    $bGood = FALSE;
                }
            }
            if(isset($analysis['orphantext']))
            {
                form_set_error($catradio, 'There is non-blank Default Value in '.$name.' with a blank '.$buttontypename);
                $bGood = FALSE;
            }
        }

        $this->validateCodeMappings($myvalues, $bGood);
        
        try
        {
            //Validate upload file AND update the form_state!!!!
            $file = file_save_upload('protocolfile'
                    , array(
                        'file_validate_extensions' => array('doc docx pdf png gif jpg jpeg'),
                ));
            if ($file) 
            {
              $file = file_move($file, 'public://', FILE_EXISTS_REPLACE);
              if ($file) {
                $form_state['values']['protocolfile'] = $file;
              } else {
                $bGood = FALSE;
                error_log('Failed to write the uploaded file to the site\'s file folder.'.print_r($myvalues,TRUE));
                form_set_error('protocolfile', t('Failed to write the uploaded file to the site\'s file folder.'));
              }
            } else {
                if($formMode == 'A')
                {
                    //Must at least have one file.
                    form_set_error('protocolfile', 'A file must be provided.');
                    $bGood = FALSE;
                }
            }
        } catch (\Exception $ex) {
            error_log('Failed on upload file because '.print_r($ex,TRUE));
            form_set_error('protocolfile', t('Failed upload file because '.$ex->getMessage()));
            $bGood = FALSE;
        }
  
        return $bGood;
    }

    private function validateCodeMappings($myvalues, &$bGood)
    {
        $fieldname = 'cpt_codes';
        $codes = trim($myvalues[$fieldname]);
        $codes_array = explode(',', $codes);
        foreach($codes_array as $code)
        {
            $clean = trim($code);
            if(strlen($clean) > 0)
            {
                if(strlen($clean) < 5)
                {
                    form_set_error($fieldname, 'CPT codes must be a five digit integer but  "'.$clean.'" is too short.');
                    $bGood = FALSE;
                    break;
                } else {
                    if(strlen($clean) > 5)
                    {
                        form_set_error($fieldname, 'CPT codes must be a five digit integer but  "'.$code.'" is too big.');
                        $bGood = FALSE;
                        break;
                    } else {
                        if(!is_numeric($clean))
                        {
                            form_set_error($fieldname, 'CPT codes must be a five digit integer but  "'.$code.'" is not an integer.');
                            $bGood = FALSE;
                            break;
                        }
                    }
                }
            } 
        }

        $fieldname = 'radlex_codes';
        $codes = trim($myvalues[$fieldname]);
        $codes_array = explode(',', $codes);
        foreach($codes_array as $code)
        {
            $clean = strtoupper(trim($code));
            if(strlen($clean) > 0)
            {
                if(strlen($clean) < 5 || substr($clean,0,4) != 'RPID')
                {
                    form_set_error($fieldname, 'RADLEX codes are RPID prefix followed by digits but  "'.$code.'" is wrong.');
                    $bGood = FALSE;
                    break;
                } else {
                    $numbers = substr($clean, 4);
                    if(!is_numeric($numbers))
                    {
                        form_set_error($fieldname, 'RADLEX codes are RPID prefix followed by digits but  "'.$code.'" is not numbers.');
                        $bGood = FALSE;
                        break;
                    }
                }
            } 
        }
    }
    
    /**
     * Get all the form contents for rendering
     * formType = A, E, D, V
     * @return type renderable array
     */
    function getForm($formType, $form, &$form_state, $disabled
            , $myvalues, $containerID=NULL)
    {
        $aOptions = $this->getAllOptions();
        $aFormattedKeywordText = $this->formatKeywordText($myvalues);

        if($containerID == NULL)    //201407018
        {
            $topidtxt = '';
        } else {
            $topidtxt = ' ID="'.$containerID.'" ';
        }
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<!--  Start of $containerID protocol-dataentry div section -->"
                . "\n<div $topidtxt class='protocol-dataentry'>\n",
            '#suffix' => "\n</div>\n<!--  End of $containerID protocol-dataentry div section -->\n",
            '#disabled' => $disabled,
        );

        $form[] = array('#markup' 
            => '<div id="protocol-lib-options-data">'
            . '<div style="visibility:hidden" id="json-option-values-all-sections"></div></div>');

        $form['data_entry_area1']['toppart'] = array(
            '#type'     => 'fieldset',
            '#attributes' => array(
                'class' => array(
                    'data-entry-area'
                )
             ),
            '#disabled' => $disabled,
        );

        if(isset($myvalues['protocol_shortname']))
        {
            $protocol_shortname = $myvalues['protocol_shortname'];
        } else {
            $protocol_shortname = NULL;
        }


        $showfieldname_version = 'version';
        $showfieldname_shortname = 'protocol_shortname';
        $disabled_shortname = $disabled;   //Default behavior
        $disabled_version = $disabled;  //Default behavior
        if($disabled || $formType == 'E' || $formType == 'A')
        {
            //Hidden values for key fields
            if($formType == 'E')
            {
                $form['hiddenthings']['protocol_shortname']
                    = array('#type' => 'hidden', '#value' 
                        => $protocol_shortname, '#disabled' => FALSE);
                $showfieldname_shortname = 'show_protocol_shortname';
                $disabled_shortname = TRUE;
                $oldversionnumber = (isset($myvalues['version']) ? $myvalues['version'] : 1);
                if(isset($myvalues['newversion']))
                {
                    $newversionnumber = $myvalues['newversion'];
                } else {
                    $newversionnumber = $oldversionnumber + 1;
                }
                $form['hiddenthings']['oldversion']
                    = array('#type' => 'hidden', '#value' => $oldversionnumber, '#disabled' => FALSE);
                $form['hiddenthings']['version']
                    = array('#type' => 'hidden', '#value' => $newversionnumber, '#disabled' => FALSE);
                $showfieldname_version = 'show_version';
            } else
            if($formType == 'A')
            {
                $newversionnumber = 1;
                $form['hiddenthings']['version']
                    = array('#type' => 'hidden', '#value' => $newversionnumber, '#disabled' => FALSE);
                $showfieldname_version = 'show_version';
                $myvalues['version'] = $newversionnumber;
            }
            $disabled_version = TRUE;
        }


        $form['data_entry_area1']['toppart'][$showfieldname_shortname] = array(
          '#type' => 'textfield',
          '#title' => t('Short Name'),
          '#default_value' => $protocol_shortname,
          '#size' => 20,
          '#maxlength' => 20,
          '#required' => TRUE,
          '#description' => t('The unique short name for this protocol'),
          '#disabled' => $disabled_shortname,
        );

        $form['data_entry_area1']['toppart']['name'] = array(
          '#type' => 'textfield',
          '#title' => t('Long Name'),
          '#default_value' => $myvalues['name'],
          '#size' => 80,
          '#maxlength' => 128,
          '#required' => TRUE,
          '#description' => t('The unique long name for this protocol'),
          '#disabled' => $disabled,
        );
        $form['data_entry_area1']['toppart'][$showfieldname_version] = array(
          '#type' => 'textfield',
          '#title' => t('Version number'),
          '#value' => $myvalues['version'],
          '#size' => 5,
          '#maxlength' => 5,
          '#description' => t('The version number increases'
                  . ' each time a protocol is changed.'
                  . '  It does not, however, change when the default values'
                  . ' or keywords of the protocol are edited.'),
          '#disabled' => $disabled_version,
        );

        $sName                                  = 'service_nm';
        $oChoices                               = new \raptor\raptor_datalayer_Choices();   
        $aChoices                               = $oChoices->getServicesData('');
        if(count($aChoices) > 0)
        {
            //We only prompt for a service name if the database has services defined.
            $aStatesEntry                           = array();
            $element                                = FormHelper::createSelectList($sName, $aChoices, $disabled, $aStatesEntry, $myvalues);
            $form['data_entry_area1']['toppart'][$sName] = $element;
            $form['data_entry_area1']['toppart'][$sName]['#title'] = t('Service');
            $form['data_entry_area1']['toppart'][$sName]['#description'] = t('The service associated with this protocol');

            $form['data_entry_area1']['toppart']['active_yn'] = array(
               '#type' => 'checkbox',
               '#title' => t('Protocol active (Y/N)'),
               '#default_value' => $myvalues['active_yn'],
               '#description' => t('Protocol is not available if it is not active'),
               '#disabled' => $disabled,
            );
        }

        //Group the scanned file information together into a fieldset
        $form['data_entry_area1']['toppart']['scanning'] = array(
            '#type'     => 'fieldset',
            '#attributes' => array(
                'class' => array(
                    'data-entry-area'
                )
             ),
            '#disabled' => $disabled,
        );
        if($myvalues['filename'] > '' && $myvalues['filename'] != 'no-filename')
        {
            $uploadlabel = 'Replace scanned protocol document';
            $userinfo = $this->m_oContext->getUserInfo($myvalues['original_file_upload_by_uid']);
            $uploaded_by = $userinfo->getFullName();
            $form['data_entry_area1']['toppart']['scanning']['existingprotocolfile'] = array('#type' => 'item',
                     '#markup' => '<p>A scanned document for this protocol is already in the system since '.$myvalues['original_file_upload_dt'].'.</p>'
                . '<ul>'
                . '<li>Filename: '.$myvalues['filename']
                . '<li>Original filename: '.$myvalues['original_filename']
                . '<li>Uploaded by: '.$uploaded_by
                . '</ul>'
                );
        } else {
            $uploadlabel = FormHelper::getTitleAsRequiredField('Scanned protocol document');
        }
        if(!$disabled && ($formType == 'E' || $formType == 'A'))
        {
            $form['data_entry_area1']['toppart']['scanning']['protocolfile'] = array(
              '#type' => 'file',
              '#title' => t($uploadlabel),
              '#required' => FALSE,
              '#description' => t('The scanned image of the protocol for visual reference (*.pdf preferred; *.jpg, *.png, *.gif and *.doc/docx allowed)'),
              '#disabled' => $disabled,
            );
            
            $form['data_entry_area1']['toppart']['scanning']['upload_comment_tx'] = array(
                '#title'         => t('Upload Notes'),
                '#maxlength' => 1024,
                '#resizable' => TRUE,
                '#rows' => 2,
                '#description' => t('If uploading a file, please provide a comment.'),
                '#type'          => 'textarea',
                '#disabled'      => $disabled,
                '#default_value' => $myvalues['upload_comment_tx'],
            );
        }
        
        $form['data_entry_area1']['toppart']['modality_abbr'] = array(
            '#type' => 'radios',
            '#options' => $aOptions['modality'],
            '#default_value' => $myvalues['modality_abbr'],
            '#title' => t('Modality'),
            '#description' => t('The modality of this protocol'),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['toppart']['modality_abbr']['#ajax'] = array(
            'callback' => 'raptor_fetch_protocollib_options',
            //'wrapper' => 'protocol-template-data',    //Using other commands in the callback instead
            //'method' => 'replace'
        );

        $form['data_entry_area1']['toppart']['lowerbound_weight'] = array(
           '#type' => 'textfield',
           '#title' => t('Lowerbound Patient Weight (kg)'),
           '#size' => 3,
           '#maxlength' => 3,
           '#default_value' => $myvalues['lowerbound_weight'],
           '#description' => t('Lowest weight to which this protocol applies (ignored if value is 0)'),
           '#disabled' => $disabled,
        );
        $form['data_entry_area1']['toppart']['upperbound_weight'] = array(
           '#type' => 'textfield',
           '#title' => t('Upperbound Patient Weight (kg)'),
           '#size' => 3,
           '#maxlength' => 3,
           '#default_value' => $myvalues['upperbound_weight'],
           '#description' => t('Highest weight to which this protocol applies (ignored if value is 0)'),
           '#disabled' => $disabled,
        );

        $form['data_entry_area1']['categories'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Active Categories'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $form['data_entry_area1']['categories']['yn_attribs'] = array(
           '#type' => 'checkboxes',
           '#options' => $aOptions['categories'],
           '#title' => t('Categories'),
           '#description' => t('Checking these boxes indicates the categories that have default values for this protocol.  These values are used by the automatic protocol suggestion engine.'),
           '#default_value' => $myvalues['yn_attribs'],
           '#disabled' => $disabled,
        );

        $form['data_entry_area1']['keywords'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Keyword Matching'),
            '#description' => t('For automatic protocol suggestion where keywords are in the order text'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['keywords']['keywords1'] = array(
          '#type' => 'textfield',
          '#title' => t('Most Significant'),
          '#default_value' => $aFormattedKeywordText['keywords1'],
          '#size' => 100,
          '#maxlength' => 128,
          '#description' => t('Comma delimited list of most significant keywords'),
          '#disabled' => $disabled,
        );
        $form['data_entry_area1']['keywords']['keywords2'] = array(
          '#type' => 'textfield',
          '#title' => t('Moderately Significant'),
          '#default_value' => $aFormattedKeywordText['keywords2'],
          '#size' => 100,
          '#maxlength' => 128,
          '#description' => t('Comma delimited list of moderately significant keywords'),
          '#disabled' => $disabled,
        );
        $form['data_entry_area1']['keywords']['keywords3'] = array(
          '#type' => 'textfield',
          '#title' => t('Least Significant'),
          '#default_value' => $aFormattedKeywordText['keywords3'],
          '#size' => 100,
          '#maxlength' => 128,
          '#description' => t('Comma delimited list of least significant keywords'),
          '#disabled' => $disabled,
        );

        $form['data_entry_area1']['defaultvaluespart'] = array(
            '#type'     => 'fieldset',
            '#title' => t('Default Values'),
            '#attributes' => array(
                'class' => array(
                    'data-entry-area'
                )
             ),
            '#disabled' => $disabled,
          '#description' => t('The default options available here depend on the categories identfied for this protocol.'),
        );

        $form['data_entry_area1']['defaultvaluespart']['input'] 
                = $this->getInputArea1($this->m_oPI, $form_state, $disabled, $myvalues);

        $form['data_entry_area1']['defaultvaluespart']['protocolnotes_tx'] = array(
            '#title'         => t('Protocol Notes'),
            '#maxlength' => 500,
            '#description' => t('Custom text to prepopulate the protocol notes input area.'),
            '#type'          => 'textarea',
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['protocolnotes_tx'],
        );

        $form['data_entry_area1']['defaultvaluespart']['examnotes_tx'] = array(
            '#title'         => t('Exam Notes'),
            '#maxlength' => 500,
            '#description' => t('Custom text to prepopulate the exam notes input area for technologist.'),
            '#type'          => 'textarea',
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['examnotes_tx'],
        );
        
        $form['data_entry_area1']['codemappings'] = array(
            '#type'     => 'fieldset',
            '#title' => t('Code Mappings'),
            '#attributes' => array(
                'class' => array(
                    'data-entry-area'
                )
             ),
            '#disabled' => $disabled,
            '#description' => t('<p>The codes that this protocol maps'
                  . ' into so that RAPTOR can more granularly'
                  . ' apply matching order criteria.</p>'),
        );
        $form['data_entry_area1']['codemappings']['cpt_codes'] = array(
            '#title'         => t('CPT Codes'),
            '#maxlength' => 400,
            '#rows' => 2,
            '#description' => t('Comma delimited list of CPT codes to which this protocol maps'),
            '#type'          => 'textarea',
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['cpt_codes'],
        );
        $form['data_entry_area1']['codemappings']['radlex_codes'] = array(
            '#title'         => t('RADLEX Codes'),
            '#maxlength' => 400,
            '#rows' => 2,
            '#description' => t('Comma delimited list of RADLEX codes to which this protocol maps'),
            '#type'          => 'textarea',
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['radlex_codes'],
        );
        $form['data_entry_area1']['codemappings']['icd_codes'] = array(
            '#title'         => t('ICD Codes'),
            '#maxlength' => 400,
            '#rows' => 2,
            '#description' => t('Comma delimited list of ICD codes that this which this protocol maps'),
            '#type'          => 'textarea',
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['icd_codes'],
        );
        
        return $form;
    }
}