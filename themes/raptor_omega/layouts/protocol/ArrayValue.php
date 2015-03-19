<?php
/**
 * @file
 * Helper for the RAPTOR theme
 */

namespace raptor;

class ArrayValue implements \JsonSerializable 
{
    public function __construct($array) 
    {
        if(!is_array($array))
        {
            //Continue but leave a clue
            error_log('Expected an array in ArrayValue but instead got '.print_r($array,TRUE));
            $array = array();
        }
        $this->array = $array;
    }

    public function jsonSerialize() 
    {
        return $this->array;
    }
}
