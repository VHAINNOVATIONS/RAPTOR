<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
 * Copyright 2014 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.gnu.org/copyleft/gpl.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

/**
 * Helpful utilities.
 *
 * @author Frank Font
 */
class SREUtility 
{
    
    /**
     * Does NOT create a new array.  Instead, adds the contents of the new array into the existing one.
     * Use this instead of array_merge when you want to keep the reference to existing array!
     * @param type $existing_array
     * @param type $new_array
     */
    public static function mergeIntoExistingArray(&$existing_array, $new_array)
    {
        foreach($new_array as $key=>$value)
        {
            $existing_array[$key] = $value;
        }
    }
    
    /**
     * Convert the three state value into something we can print to the user in a message.
     * @param type $result one of three states.
     * @return string we can read
     * @throws \Exception
     */
    public static function translateThreeStateResultToText($result)
    {
        if($result === NULL)
        {
            return 'NULL';
        }
        if($result === TRUE)
        {
            return 'TRUE';
        }
        if($result === FALSE)
        {
            return 'FALSE';
        }
        throw new \Exception('Cannot translate boolean value [' . $result . ']');
    }
}
