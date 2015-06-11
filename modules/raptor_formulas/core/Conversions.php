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
                    'C' => '(\$inputvalue - 32) * 5/9',
                ),
                'C'=>array(
                    'F' => '(\$inputvalue * 9/5) + 32',
                ),
            );

    private static $lengthMap =
            array(
                'ft'=>array(
                    'cm' => '\$inputvalue * 30.48',
                ),
                'in'=>array(
                    'cm' => '\$inputvalue * 2.54',
                ),
                'cm'=>array(
                    'ft' => '\$inputvalue * 0.03281',
                ),
                'cm'=>array(
                    'in' => '\$inputvalue * 0.393701',
                ),
                'm'=>array(
                    'cm' => '\$inputvalue * 100',
                ),
                'm'=>array(
                    'mm' => '\$inputvalue * 1000',
                ),
                'cm'=>array(
                    'm' => '\$inputvalue / 100',
                ),
                'cm'=>array(
                    'mm' => '\$inputvalue * 10',
                ),
                'mm'=>array(
                    'cm' => '\$inputvalue / 10',
                ),
                'mm'=>array(
                    'm' => '\$inputvalue / 1000',
                ),
            );

    private static $weightMap =
            array(
                'lb'=>array(
                    'kg' => '\$inputvalue * 0.453592',
                ),
                'kg'=>array(
                    'lb' => '\$inputvalue * 2.20462',
                ),
            );

    private static $timeMap =
            array(
                's'=>array(
                    'm' => '\$inputvalue * 0.0166667',
                ),
                'sec'=>array(
                    'min' => '\$inputvalue * 0.0166667',
                ),
                'm'=>array(
                    's' => '\$inputvalue * 60',
                ),
                'min'=>array(
                    'sec' => '\$inputvalue * 60',
                ),
                'h'=>array(
                    's' => '\$inputvalue * 3600',
                ),
                'hour'=>array(
                    'sec' => '\$inputvalue * 3600',
                ),
                'h'=>array(
                    'm' => '\$inputvalue * 60',
                ),
                'hour'=>array(
                    'min' => '\$inputvalue * 60',
                ),
            );
    
    private static $radiationMap =
            array(
                'mGy*mm'=>array(
                    'mGy*cm' => '\$inputvalue TODO',
                ),
            );

    private static function convertFromTo($map,$from,$to,$inputvalue)
    {
        try
        {
            $section = $map[$from];
            $formula = $section[$to];
            $answer = NULL;
            eval("\$answer = \"$formula\";");
            return $answer;
        } catch (\Exception $ex) {
            throw new \Exception("Cannot convert \"$from\" units to \"$to\" units because ".$ex->getMessage());
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
        );
        return $map;
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
}
