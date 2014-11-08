<?php

/**
 * Implements hook_preprocess_page().
 */
function raptor_omega_preprocess_page(&$variables) 
{
  // You can use preprocess hooks to modify the variables before they are passed
  // to the theme function or template file.
    
    // HACK: Custom 403 and 404 page content
    $path = drupal_get_path_alias($_GET['q']);
    $headers = drupal_get_http_header();
    if(is_array($headers) && array_key_exists('status', $headers))
    {
        $status = $headers['status'];
        if ($status == '403 Forbidden') 
        {
            $msg = $status . ' when user tried to pull up following URL>>>' . $path;
            error_log($msg);
            $msg = '<h1>'.$status . '</h1><br>TIP: <a href="/drupal/worklist">Navigate to the worklist page of RAPTOR.</a>';
            die($msg);
        } else 
        if ($status == '404 Not Found') 
        {
            $msg = $status . ' because user tried to pull up a page that does not exist>>>' . $path;
            error_log($msg);
            $msg = '<h1>'.$status . '</h1><br>TIP: <a href="/drupal/worklist">Navigate to the worklist page of RAPTOR.</a>';
            die($msg);
            //$variables['template_files'][] = "page-404 <h1>HELLO THERE</h1>";
            //die('404 Look>>>>' . print_r($variables, TRUE) . "<br>LOOK HEADERS...<br>" . print_r($headers,TRUE));
        }    
    }
}
