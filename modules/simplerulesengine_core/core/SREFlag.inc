<?php
/**
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

require_once 'SREMeasure.inc';

/**
 * Instance of a rule from database.
 * A flag is simply a boolean measure.
 */
class SREFlag extends \simplerulesengine\SREMeasure
{
    public function __construct($flag_info_array)
    {
        parent::__construct($flag_info_array);
        if($flag_info_array['return_type'] != 'boolean')
        {
            throw new \Exception('Trouble with '.print_r($flag_info_array,TRUE).' because cannot create a flag that is not a measure of type boolean!', 9101);
        }
    }
}
