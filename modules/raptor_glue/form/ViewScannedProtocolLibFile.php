<?php
/**
 * @file
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

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');

/**
 * This class returns content for the protocol library tab
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewScannedProtocolLibFile
{

    private $m_oContext = NULL;
    private $m_protocol_shortname = NULL;
    private $m_showclose = NULL;

    function __construct($protocol_shortname,$showclose=FALSE)
    {
        $this->m_protocol_shortname = $protocol_shortname;
        $this->m_showclose = $showclose;
        $this->m_oContext = \raptor\Context::getInstance();
    }
    
    /**
     * Returns NULL if no there is no scanned file.
     */
    private function getUploadedFileDetails($protocol_shortname, $cleanfilename)
    {
        if($cleanfilename == '')
        {
            return NULL;
        } 
        
        $uri = 'public://library/'.$cleanfilename;
        $url = file_create_url($uri);
        $filepath = drupal_realpath($uri);
        if(!file_exists($filepath))
        {
            try
            {
                //Pull it out of the database.
                $blob_result = db_select('raptor_protocol_lib_uploads','p')
                        ->fields('p')
                        ->condition('protocol_shortname', $protocol_shortname, '=')
                        ->condition('filename', $cleanfilename, '=')
                        ->execute();
                if($blob_result->rowCount() == 1)
                {
                    $blob_record = $blob_result->fetchAssoc();    //There will at most be one record.
                    $file_blob = $blob_record['file_blob'];
                    if($file_blob == NULL)
                    {
                        $mywarning = "Expected to find a scanned file blob for [$protocol_shortname + $cleanfilename] but record was empty!";
                        error_log($mywarning);
                        drupal_set_message($mywarning, 'warning');
                        return NULL;
                    } else {
                        file_put_contents($filepath, $file_blob);   //Write it to the path.
                    }
                }
            } catch (\Exception $ex) {
                error_log('Failed to extract scanned document from database for '.$protocol_shortname.' because '.$ex->getMessage());
                throw $ex;
            }
        }
        $details = array();
        $details['uri'] = $uri;
        $details['url'] = $url;
        $details['filepath'] = $filepath;
        return $details;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $protocol_shortname = $this->m_protocol_shortname;
        drupal_set_title($protocol_shortname.' Scanned Doc');
        
        $name = NULL;
        $filename = NULL;
        $original_filename = NULL;
        $uploaded_dt = NULL;
        $url = NULL;
        try
        {
            $result = db_select('raptor_protocol_lib','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $protocol_shortname, '=')
                    ->execute();
            if($result->rowCount() == 1)
            {
                $record = $result->fetchAssoc();    //There will at most be one record.
                $name = $record['name'];
                $filename = $record['filename'];
                $original_filename = $record['original_filename'];
                $uploaded_dt = $record['original_file_upload_dt'];
                $userinfo = $this->m_oContext->getUserInfo($record['original_file_upload_by_uid']);
                $uploaded_by = $userinfo->getFullName();
                $cleanfilename = trim($filename);
                $sfdetails = $this->getUploadedFileDetails($protocol_shortname, $cleanfilename);
                if($sfdetails !== NULL)
                {
                    $url = $sfdetails['url'];
                }
            }
        } catch (\Exception $ex) {
            error_log('Failed to get scanned document information for '.$protocol_shortname.' because '.$ex->getMessage());
            throw $ex;
        }

        $form["data_entry_area1"]['table_container']['heading'] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table">'
                            . '<tbody>'
                            . '<tr><td>Short Name</td><td>'.$protocol_shortname.'</td></tr>'
                            . '<tr><td>Full Name</td><td>'.$name.'</td></tr>'
                            . '</tbody>'
                            . '</table>');
        
        try
        {
            $historymarkup = '<table class="raptor-dialog-table">'
                    . '<tr>'
                    . '<th>Uploaded Version</th>'
                    . '<th>Uploaded Date</th>'
                    . '<th>Uploaded By</th>'
                    . '<th>Upload Comment</th>'
                    . '</tr>'
                    . '<tbody>';
            $result = db_select('raptor_protocol_lib_uploads','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $protocol_shortname, '=')
                    ->orderBy('version')
                    ->execute();
            $historyrows = '';
            while($record = $result->fetchAssoc()) 
            {
                $userinfo = $this->m_oContext->getUserInfo($record['uploaded_by_uid']);
                $uploaded_by = $userinfo->getFullName();
                $onerow = '<tr>'
                        . '<td>'.$record['version'].'</td>'
                        . '<td>'.$record['uploaded_dt'].'</td>'
                        . '<td>'.$uploaded_by.'</td>'
                        . '<td>'.$record['comment_tx'].'</td>'
                        . '</tr>';
                $historyrows .= $onerow;
            }
            $historymarkup .= $historyrows
                    .'</tbody></table>';
            $form["data_entry_area1"]['table_container']['uploadhistory'] = array('#type' => 'item',
                     '#markup' => $historymarkup);
        } catch (\Exception $ex) {
            error_log('Failed to get scanned document comments for '
                    .$protocol_shortname.' because '.$ex->getMessage());
            throw $ex;
        }
        
        $imgmarkup = NULL;
        if($url == NULL)
        {
            $imgmarkup = '<p>No image URL found!</p>';
        } else {
            $fileinfo = pathinfo($url);
            if(isset($fileinfo['extension']))
            {
                $ext = strtoupper($fileinfo['extension']);
            } else {
                $ext = NULL;
            }
            if($ext == 'PDF')
            {
                //Handle PDF in special way (http://get.adobe.com/reader/)
                $imgmarkup = '<iframe class="thedoc" src="'.$url.'" width="100%" height="600">'
                        ."\n"
                        .'<!-- A PDF plugin available at http://get.adobe.com/reader/ -->';
            } else if($ext == 'DOC' || $ext == 'DOCX' || $ext == 'RTF') {
                //Handle PDF in special way (http://get.adobe.com/reader/)
                $imgmarkup = '<iframe class="thedoc" src="'.$url.'" width="100%" height="600">';
            } else {
                //Simple image
                $imgmarkup = '<img class="thedoc" src="'.$url.'">';
            }
        }
            
        $form["data_entry_area1"]['table_container']['image'] = array('#type' => 'item',
                 '#markup' => $imgmarkup);
        
        if($this->m_showclose)
        {
            //Window close button only works when script created the window.
            $form['data_entry_area1']['action_buttons'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="raptor-action-buttons">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $form['data_entry_area1']['action_buttons']['close'] = array('#type' => 'item'
                    , '#markup' => '<a href="#" onclick="self.close();return false;">Close</a>');        
        }

        return $form;
    }
}
