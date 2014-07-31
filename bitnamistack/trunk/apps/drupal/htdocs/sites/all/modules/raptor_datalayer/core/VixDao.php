<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140610 
 */

namespace raptor;

require_once("config.php");

/**
 * Interface to the RAPTOR VIX tier
 *
 * @author FrankWin7VM
 */
class VixDao 
{
    private $m_username = NULL;
    private $m_password = NULL;
    
    public function __construct($username, $password)
    {
        $this->m_username = $username;
        $this->m_password = $password;
    }    
    
    /**
     * Return associative array with image inforamtion for the VIX call.
     */
    public function getImageInfoForReport($patientDFN, $patientICN, $reportID, $caseNumber)
    {
        if(trim($patientDFN == '') || trim($patientICN) == '' || trim($reportID) == '' || trim($caseNumber) == '')
        {
            error_log("Incomplete request: Empty parameters passed to getImageInfoForReport([$patientDFN], [$patientICN], [$reportID], [$caseNumber])");
            $returnInfo['imageCount'] = -1;
            $returnInfo['description'] = "Incomplete request!!!";
            $returnInfo['thumbnailImageUri'] = 'na';
            $returnInfo['thumbnailImageUrl'] = 'na';
            $returnInfo['viewerUrl'] = 'na';
            return $returnInfo;        
        }
        
        
        //http://<VIX hostname>:<VIX port>/RaptorWebApp/secure/restservices/raptor/studies/<VA patient ICN>/<VA site number>/<Patient DFN>/<Report ID>/<Case Number>
        //http://localhost:8090/RaptorWebApp/secure/restservices/raptor/studies/10110V004877/901/8/6859578.8896-1/54
        $sURL = VIX_STUDIES_URL . $patientICN . '/' . VISTA_SITE . '/' . $patientDFN . '/' . $reportID . '/' . $caseNumber;
        //$sURL = 'http://localhost:8090/RaptorWebApp/secure/restservices/raptor/studies/10110V004877/901/8/6859578.8896-1/54';
        //         http://localhost:8090/RaptorWebApp/secure/restservices/raptor/studies/            /901/129/7018895.8391-1/16
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'xxx-authenticate-site-number:'.VISTA_SITE,
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->m_username . ':' . $this->m_password );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, $sURL );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        $returnInfo = array();
        
        try {
            $response = curl_exec($ch);
            if($response == NULL)
            {
                error_log('Check VIX because got NULL instead of XML for URL=' . $sURL);
                $returnInfo['imageCount'] = -1;
                $returnInfo['description'] = 'VIX RESPONSE: NULL';
                $returnInfo['thumnailImageUri'] = 'na';
                $returnInfo['thumnailImageUrl'] = 'na';
                $returnInfo['viewerUrl'] = 'na';
                $returnInfo['studyId'] = 'na';
                return $returnInfo;        
            } else 
            if(FALSE == strpos(substr($response, 0,20), 'xml') )
            {
                error_log('Check VIX because did not get XML for URL=' . $sURL
                        . "\n >>>VIX RESPONSE: ".  print_r($response,TRUE));
                $returnInfo['imageCount'] = -1;
                $returnInfo['description'] = 'VIX RESPONSE: '.  print_r($response,TRUE);
                $returnInfo['thumnailImageUri'] = 'na';
                $returnInfo['thumnailImageUrl'] = 'na';
                $returnInfo['viewerUrl'] = 'na';
                $returnInfo['studyId'] = 'na';
                return $returnInfo;        
            }
            $oXML = new \SimpleXmlElement($response);
        } catch (\Exception $ex) {
            error_log('Check VIX for URL=' . $sURL
                    . "\n >>>VIX RESPONSE: ".   print_r($ex,TRUE));
            $returnInfo['imageCount'] = -1;
            $returnInfo['description'] = $ex->getMessage() 
                    . '<br> >>>RESPONSE: '.  print_r($response,TRUE)
                    . '<br> >>>XML: '.  print_r($oXML,TRUE);
            $returnInfo['thumnailImageUri'] = 'na';
            $returnInfo['thumnailImageUrl'] = 'na';
            $returnInfo['viewerUrl'] = 'na';
            $returnInfo['studyId'] = 'na';
            return $returnInfo;        
        }
        if(isset($oXML->study[0]->serieses[0]->series[0]))
        {
            
            $series = $oXML->study[0]->serieses[0]->series[0];
            $imageCount = $series->imageCount;
            if($imageCount > 0)
            {
                
                $imageInfo = $series->images[0]->image;
                $securityToken = $oXML->study[0]->securityToken;
                $description = $imageInfo->description;
                $thumbnailImageUri = $imageInfo->thumbnailImageUri;
                //http://<VIX hostname>:<VIX port>/RaptorWebApp/token/thumbnail<thumbnailImageUri>
                $sThumbnailUrl = VIX_THUMBNAIL_URL . $thumbnailImageUri . '&securityToken='.$securityToken;
                //$sThumbnailUrl='http://localhost:8090/RaptorWebApp/secure/thumbnail?imageUrn=urn:vaimage:901-866-865-10110V004877-CR';

                $studyId = ''.$oXML->study[0]->studyId;
                //http://<VIX hostname>:<VIX port>/HTML5DicomViewer/secure/HTML5Viewer.html?studyId=<studyId>&securityToken=<securityToken>
                $sViewerUrl = VIX_HTML_VIEWER_URL . '?studyId='.$studyId.'&securityToken='.$securityToken;
                
            } else {
                $imageCount = 0;
                $securityToken = NULL;
                $description = NULL;
                $thumbnailImageUri = NULL;
                $sThumbnailUrl = NULL;
                $sViewerUrl = NULL;
                $studyId = NULL;
            }

        } else {
            $imageCount = 0;
            $securityToken = NULL;
            $description = NULL;
            $thumbnailImageUri = NULL;
            $sThumbnailUrl = NULL;
            $sViewerUrl = NULL;
            $studyId = NULL;
        }
        $returnInfo['imageCount'] = $imageCount;
        $returnInfo['securityToken'] = $securityToken;
        $returnInfo['description'] = $description;
        $returnInfo['thumbnailImageUri'] = $thumbnailImageUri;
        $returnInfo['thumbnailImageUrl'] = $sThumbnailUrl;
        $returnInfo['viewerUrl'] = $sViewerUrl;
        $returnInfo['studyId'] = $studyId;
        
        //drupal_set_message('VIX Call info=' . print_r($returnInfo,TRUE),'warning');
        return $returnInfo;
    }
    
}
