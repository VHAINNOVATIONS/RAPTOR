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

namespace raptor_formulas;

/**
 * Logic for converting between units of measure
 *
 * @author Frank Font of SAN Business Consultants
 */
class Conversions 
{
    private static $temperatureMap =
            array(
                'F'=>array(
                    'C' => '($inputvalue - 32) * 5/9',
                ),
                'C'=>array(
                    'F' => '($inputvalue * 9/5) + 32',
                ),
            );

    private static $lengthMap =
            array(
                'ft'=>array(
                    'cm' => '$inputvalue * 30.48',
                ),
                'in'=>array(
                    'cm' => '$inputvalue * 2.54',
                ),
                'cm'=>array(
                    'ft' => '$inputvalue * 0.03281',
                    'in' => '$inputvalue * 0.393701',
                    'm' => '$inputvalue / 100',
                    'mm' => '$inputvalue * 10',
                ),
                'm'=>array(
                    'cm' => '$inputvalue * 100',
                    'mm' => '$inputvalue * 1000',
                ),
                'mm'=>array(
                    'cm' => '$inputvalue / 10',
                    'm' => '$inputvalue / 1000',
                ),
            );

    private static $weightMap =
            array(
                'lb'=>array(
                    'kg' => '$inputvalue * 0.453592',
                ),
                'kg'=>array(
                    'lb' => '$inputvalue * 2.20462',
                ),
            );

    private static $timeMap =
            array(
                's'=>array(
                    'min' => '$inputvalue * 0.0166667',
                    'sec' => '$inputvalue',
                ),
                'sec'=>array(
                    'min' => '$inputvalue * 0.0166667',
                    's' => '$inputvalue',
                ),
                'min'=>array(
                    'sec' => '$inputvalue * 60',
                ),
                'hour'=>array(
                    'min' => '$inputvalue * 60',
                    's' => '$inputvalue * 3600',
                    'sec' => '$inputvalue * 3600',
                ),
            );
    
    private static $radiationMap =
            array(
                'mGy/min'=>array(
                    'mGy/s' => '$inputvalue / 60',
                    'rd/min' => '$inputvalue * 6 * 60',
                ),
                'mGy/s'=>array(
                    'mGy/min' => '$inputvalue * 60',
                    'rd/min' => '$inputvalue * 6',
                ),
                'rd/min'=>array(
                    'mGy/s' => '$inputvalue / 6',
                    'mGy/min' => '($inputvalue / 6 ) * 60',
                ),
                'mGy*mm'=>array(
                    'mGy*cm' => '$inputvalue / 10',
                    'mGy*m' => '$inputvalue / 1000',
                ),
                'mGy*cm'=>array(
                    'mGy*mm' => '$inputvalue * 10',
                    'mGy*m' => '$inputvalue / 10',
                ),
                'mGy*m'=>array(
                    'mGy*mm' => '$inputvalue * 1000',
                    'mGy*cm' => '$inputvalue * 100',
                ),
                'GBq'=>array(
                    'uCi' => '$inputvalue / 37000000',
                    'mCi' => '$inputvalue / 37000',
                    'Ci' => '$inputvalue / 37',
                ),
                'uCi'=>array(
                    'mCi' => '$inputvalue / 1000',
                    'Ci' => '$inputvalue / 1000000',
                    'GBq' => '$inputvalue * 37000000',
                ),
                'mCi'=>array(
                    'uCi' => '$inputvalue * 1000',
                    'Ci' => '$inputvalue / 1000',
                    'GBq' => '$inputvalue * 37000',
                ),
                'Ci'=>array(
                    'uCi' => '$inputvalue * 1000000',
                    'mCi' => '$inputvalue * 1000',
                    'GBq' => '$inputvalue * 37',
                ),
                'uGy*cm^2'=>array(
                    'mGy*cm^2' => '$inputvalue * 1000',
                    'Gy*cm^2' => '$inputvalue * 1000000',
                ),
                'mGy*cm^2'=>array(
                    'uGy*cm^2' => '$inputvalue / 1000',
                    'Gy*cm^2' => '$inputvalue * 1000',
                ),
                'Gy*cm^2'=>array(
                    'uGy*cm^2' => '$inputvalue / 1000000',
                    'mGy*cm^2' => '$inputvalue / 1000',
                ),
                'uGycm'=>array(
                    'mGycm' => '$inputvalue / 1000',
                    'Gycm' => '$inputvalue / 1000000',
                ),
                'mGycm'=>array(
                    'uGycm' => '$inputvalue * 1000',
                    'Gycm' => '$inputvalue / 1000',
                ),
                'Gycm'=>array(
                    'uGycm' => '$inputvalue * 1000000',
                    'mGycm' => '$inputvalue * 1000',
                ),
            );

    private static $freqMap =
            array(
                'Hz'=>array(
                    'KHz' => '$inputvalue / 1000',
                    'MHz' => '$inputvalue / 1000000',
                ),
                'KHz'=>array(
                    'Hz' => '$inputvalue * 1000',
                    'MHz' => '$inputvalue / 1000',
                ),
                'MHz'=>array(
                    'Hz' => '$inputvalue * 1000000',
                    'KHz' => '$inputvalue / 1000',
                ),
            );
    
    private static function convertFromTo($map,$from,$to,$inputvalue)
    {
        try
        {
            if(!isset($map[$from]))
            {
                throw new \Exception("unsupported conversion from \"$from\"");
            }
            $section = $map[$from];
            if(!isset($section[$to]))
            {
                throw new \Exception("unsupported conversion to \"$to\"");
            }
            $formula = "return {$section[$to]};";
            $answer = eval($formula);
            return $answer;
        } catch (\Exception $ex) {
            throw new \Exception("Cannot convert \"$from\" units to \"$to\" units because " 
                    . $ex->getMessage() 
                    . "\nmap=".print_r($map,TRUE));
        }
    }
    
    public static function getAllSupportedConversions()
    {
        $map = array(
            'temperature'=>self::$temperatureMap,
            'length'=>self::$lengthMap,
            'weight'=>self::$weightMap,
            'radiation'=>self::$radiationMap,
            'time'=>self::$timeMap,
            'frequency'=>self::$freqMap,
        );
        return $map;
    }

    private static function getMapWithTo($to)
    {
        $allmaps = self::getAllSupportedConversions();
        foreach($allmaps as $onemap)
        {
            foreach($onemap as $from=>$submap)
            {
                if(isset($submap[$to]))
                {
                    //Found the parent map!
                    return $onemap;
                }
            }
        }
        throw new \Exception("Conversion to units of '$to' is NOT supported");
    }
    
    public static function getMapWithFrom($from)
    {
        $allmaps = self::getAllSupportedConversions();
        foreach($allmaps as $onemap)
        {
            if(isset($onemap[$from]))
            {
                //Found the map!
                return $onemap;
            }
        }
        throw new \Exception("Conversion from units of '$from' is NOT supported");
    }
    
    /**
     * Use the 'FROM' units to figure out the context.
     */
    public static function convertAnything($from,$to,$inputvalue)
    {
        try
        {
            $map = self::getMapWithFrom($from);
            return self::convertFromTo($map, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR CONVERSION FAILED '.$ex->getMessage());
        }
    }
    
    public static function convertTemparature($from,$to,$inputvalue)
    {
        try
        {
            return self::convertFromTo(self::$temperatureMap, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR TEMPERATURE CONVERSION FAILED '.$ex->getMessage());
        }
    }

    public static function convertLength($from,$to,$inputvalue)
    {
        try
        {
            return self::convertFromTo(self::$lengthMap, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR LENGTH CONVERSION FAILED '.$ex->getMessage());
        }
    }

    public static function convertWeight($from,$to,$inputvalue)
    {
        try
        {
            return self::convertFromTo(self::$weightMap, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR WEIGHT CONVERSION FAILED '.$ex->getMessage());
        }
    }

    public static function convertRadiation($from,$to,$inputvalue)
    {
        try
        {
            return self::convertFromTo(self::$radiationMap, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR RADIATION CONVERSION FAILED '.$ex->getMessage());
        }
    }
    
    public static function convertTime($from,$to,$inputvalue)
    {
        try
        {
            return self::convertFromTo(self::$timeMap, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR TIME CONVERSION FAILED '.$ex->getMessage());
        }
    }
    
    public static function convertFrequency($from,$to,$inputvalue)
    {
        try
        {
            return self::convertFromTo(self::$freqMap, $from, $to, $inputvalue);
        } catch (\Exception $ex) {
            throw new \Exception('ERROR FREQUENCY CONVERSION FAILED '.$ex->getMessage());
        }
    }
}
