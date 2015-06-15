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

/**
 * The functions to interact with facility radation dose tracking information
 *
 * @author Frank Font of SAN Business Consultants
 */
class FacilityRadiationDose 
{
    
    public function getFacilityDoseInfo($bundle, $psn, $dose_source_cd)
    {
        $sitedose_summary = $bundle['summary'];
        if(!isset($sitedose_summary[$psn]) 
                || !isset($sitedose_summary[$psn][$dose_source_cd]))
        {
            $label = 'unavailable';
            $tip = 'No facility history found';
        } else {
            $label = $sitedose_summary[$psn][$dose_source_cd]['dose_avg'];
            $tip_ct = $sitedose_summary[$psn][$dose_source_cd]['sample_ct'];
            $tip_dt = $sitedose_summary[$psn][$dose_source_cd]['updated_dt'];
            $tip_dtinfo = date('Y/m',strtotime($tip_dt));   //Obscure the exact time.
            $tip = "sample size $tip_ct last updated $tip_dtinfo";
        }
        $markup = array('label'=>$label,'tip'=>$tip);
        return $markup;
    }
    
    /**
     * Get all the site dose tracking information
     */
    public function getSiteDoseTracking()
    {
        $siteid=VISTA_SITE;
        $details = array();
        $summary = array();
        $result = db_select('raptor_protocol_radiation_dose_tracking', 'u')
                    ->fields('u')
                    ->condition('siteid', $siteid, '=')
                    ->orderBy('protocol_shortname','ASC')
                    ->orderBy('dose_source_cd','ASC')
                    ->execute();
        if($result->rowCount() > 0)
        {
            while($record = $result->fetchAssoc())
            {
                $psnkey = $record['protocol_shortname'];
                $dose_source_cd = $record['dose_source_cd'];
                
                //Get summary containers
                if(!key_exists($psnkey, $summary))
                {
                    $summary[$psnkey] = array();
                }
                $onepsnsummary = $summary[$psnkey];
                if(!key_exists($dose_source_cd, $onepsnsummary))
                {
                    $onepsnsummary[$dose_source_cd] = array();
                }
                $onepsnsummary_level2 = $onepsnsummary[$dose_source_cd];
                
                //Get details containers
                if(!key_exists($psnkey, $details))
                {
                    $details[$psnkey] = array();
                }
                $onepsn = $details[$psnkey];
                if(!key_exists($dose_source_cd, $onepsn))
                {
                    $onepsn[$dose_source_cd] = array();
                }
                $onepsn_level2 = $onepsn[$dose_source_cd];
                
                //Assign detail
                $onepsn_level2['detail'][] = $record;
                
                //Update summary
                $new_dose_avg = 1;
                $new_sample_ct = 2;
                $sample_ct = $record['sample_ct'];
                $uom = $record['uom'];
                $dose_avg = (float) $record['dose_avg'];
                $updated_dt = $record['updated_dt'];
                if(isset($onepsnsummary_level2['uom']))
                {
                    if($onepsnsummary_level2['uom'] != $uom)
                    {
                        //Todo --- gracefully normalize the values
                        throw new \Exception("Mixed UOM are not handled at this time -- check ".print_r($record,TRUE));
                    }
                    $existing_sample_ct = $onepsnsummary_level2['sample_ct'];
                    $existing_dose_avg = $onepsnsummary_level2['dose_avg'];
                    $existing_updated_dt = $onepsnsummary_level2['updated_dt'];
                    $new_sample_ct = $existing_sample_ct + $sample_ct;
                    $new_dose_avg = (($existing_dose_avg * (float) $existing_sample_ct) + ($dose_avg * (float) $sample_ct)) / ($existing_sample_ct + $sample_ct);
                    if($existing_updated_dt < $new_updated_dt)
                    {
                        $new_updated_dt = $updated_dt;
                    } else {
                        $new_updated_dt = $existing_updated_dt;
                    }
                } else {
                    $new_sample_ct = $sample_ct;
                    $new_dose_avg = $dose_avg;
                    $new_updated_dt = $updated_dt;
                }
                $onepsnsummary_level2['dose_avg'] = $new_dose_avg;
                $onepsnsummary_level2['sample_ct'] = $new_sample_ct;
                $onepsnsummary_level2['uom'] = $uom;
                $onepsnsummary_level2['updated_dt'] = $new_updated_dt;
                
                //Assign details back to top levels
                $onepsn[$dose_source_cd] = $onepsn_level2;
                $details[$psnkey] = $onepsn;
                
                //Assign summary back to top levels
                $onepsnsummary[$dose_source_cd] = $onepsnsummary_level2;
                $summary[$psnkey] = $onepsnsummary;
            }
        }
        $bundle = array('summary'=>$summary,'details'=>$details);
        return $bundle;
    }
    
    /**
     * Track radiation exposure at the site level
     */
    public function updateSiteDoseTracking($protocol_shortname
            , $dose_source_cd, $uom, $dose_type_cd
            , $dose, $sample_size)
    {
        try
        {
            $siteid=VISTA_SITE;
            $updated_dt = date("Y-m-d H:i:s", time());
            //Create a new record?
            $result = db_select('raptor_protocol_radiation_dose_tracking', 'u')
                        ->fields('u')
                        ->condition('siteid', $siteid, '=')
                        ->condition('protocol_shortname', $protocol_shortname, '=')
                        ->condition('dose_source_cd', $dose_source_cd, '=')
                        ->condition('uom', $uom, '=')
                        ->condition('dose_type_cd', $dose_type_cd, '=')
                        ->execute();
            if($result->rowCount() < 1)
            {
                //Create a new record
                db_insert('raptor_protocol_radiation_dose_tracking')
                ->fields(array(
                        'siteid'=>$siteid,
                        'protocol_shortname'=>$protocol_shortname,
                        'dose_source_cd' => $dose_source_cd,
                        'uom' => $uom,
                        'dose_type_cd' => $dose_type_cd,
                        'dose_avg' => ((float) $dose),
                        'sample_ct' => $sample_size,
                        'updated_dt'=>$updated_dt,
                        'created_dt'=>$updated_dt,
                    ))
                    ->execute();
            } else {
                //Update an existing record with weighted average
                $record = $result->fetchAssoc();
                $existing_sample_ct = $record['sample_ct'];
                $existing_dose_avg = (float)$record['dose_avg'];
                $new_dose_avg = (float)((float)($dose * (float)$sample_size) 
                        + (float)($existing_dose_avg * (float)$existing_sample_ct)) 
                        / ($sample_size + $existing_sample_ct);
                db_update('raptor_protocol_radiation_dose_tracking')
                        ->fields(array(
                            'dose_avg' => $new_dose_avg,
                            'sample_ct' => $existing_sample_ct + $sample_size,
                            'updated_dt' => $updated_dt,
                        ))
                        ->condition('siteid',$siteid,'=')
                        ->condition('protocol_shortname', $protocol_shortname, '=')
                        ->condition('dose_source_cd', $dose_source_cd,'=')
                        ->condition('uom',$uom,'=')
                        ->condition('dose_type_cd',$dose_type_cd,'=')
                        ->execute();
            }
        } catch (\Exception $ex) {
            //During development just write to the log --- table is still new!!!!!
            error_log("Failed to update dose tracking with"
                    . " (psn=$protocol_shortname, dsc=$dose_source_code, uom=$uom, qcd=$quality_cd, dose=$dose)"
                    . " because ".$ex->getMessage());
        }
    }
    
}
