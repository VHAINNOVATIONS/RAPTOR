<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * EWD Integration and VISTA collaboration: Joel Mewton, Rob Tweed
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */ 

namespace raptor_ewdvista;

/**
 * This is the primary interface implementation to encryption
 *
 * @author Frank Font of SAN Business Consultants
 */
class Encryption
{
    private static function getEncryptedText($key, $data)
    {
        try
        {
            $cstrong = TRUE;
            $bytes = openssl_random_pseudo_bytes(8, $cstrong);
            $iv   = bin2hex($bytes);
            $cleankey = str_replace('-', '', $key);
            $encrypted_data = openssl_encrypt($data, "aes-256-cbc", $cleankey, 0, $iv);

            $binary = base64_decode($encrypted_data);
            $hex = bin2hex($binary);
            $encrypted_bundle= ($iv."_x_".$hex);

            return $encrypted_bundle;
        }
        catch (\Exception $ex) {
            throw new \Exception("Failed encryption because ".$ex,99876,$ex);
        }
    }
    
    private static function getDecryptedText($key, $encrypted_bundle)
    {
        try
        {
            $a = explode("_x_", $encrypted_bundle);
            $iv = $a[0];
            $encry =$a[1];

            $binary = hex2bin($encry);
            $base64 = base64_encode($binary);

            $decrypted_data = openssl_decrypt($base64, "aes-256-cbc", $key, 0, $iv);
            return $decrypted_data;
        }
        catch (\Exception $ex) {
                throw new \Exception("Failed encryption because " . $ex, 99876, $ex);
        }
    }    
    
    public static function getEncryptedCredentials($keytext,$access_code,$verify_code)
    {
        try
        {
            $input = 'accessCode:' . $access_code . ';verifyCode:' . $verify_code;
            $output = self::getEncryptedText($keytext, $input);
            return $output;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
