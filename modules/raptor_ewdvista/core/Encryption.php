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
    public static function getEncryptedCredentials($keytext,$access_code,$verify_code)
    {
        //FAKE CODE NOT ENCRYPT
        $input = 'accessCode:' . $access_code . ';verifyCode:' . $verify_code;
        return $input;
        
        /* REAL CODE
        
        try
        {
            //$algorithm = 'rijndael-128';
            $algorithm = 'aes-256-cbc';
            
            //$debugstuff = openssl_get_cipher_methods();
            //$key = hash('sha256', $keytext, TRUE);
            
            $input = 'accessCode=' + $access_code + '&verifyCode=' + $verify_code;
            //$iv = unpack('C*', 'raptorraptor2015');
            $iv = 'raptorraptor2015';
            $encrypted_data = openssl_encrypt($input, $algorithm, $keytext, 0, $iv);

            //$td = mcrypt_module_open($algorithm, '', 'cbc', '');
            //$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
            //mcrypt_generic_init($td, $key, $iv);
            //$encrypted_data = mcrypt_generic($td, $input);
            //mcrypt_generic_deinit($td);
            //mcrypt_module_close($td);

            $ciphertext_base64 = base64_encode($encrypted_data);
            $ciphertext_hex = bin2hex($encrypted_data);
            error_log("LOOK encrypted_data=[$encrypted_data]");
            error_log("LOOK ciphertext_base64=[$ciphertext_base64]");
            error_log("LOOK ciphertext_hex=[$ciphertext_hex]");
            
            return $ciphertext_hex;
        } catch (\Exception $ex) {
            throw new \Exception("Failed encryption because ".$ex,99876,$ex);
        }
            */
    }
}
