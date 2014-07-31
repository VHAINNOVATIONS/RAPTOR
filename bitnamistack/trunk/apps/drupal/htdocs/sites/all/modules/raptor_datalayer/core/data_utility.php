<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140507
 */

function error_context_log($msg, $trace=false)
{
    error_log('ContextMsg->' . $msg);
}
