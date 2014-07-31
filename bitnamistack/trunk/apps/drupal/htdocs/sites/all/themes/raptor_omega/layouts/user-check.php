<?php
global $user;
if($user->uid==0)
{
    echo "<hr>";
    echo "<h1>You are NOT an authorized user.  First login by following the link below.</h1>";
    //echo( "<h1>USER-->".print_r($user,true)."</h1>" );
    echo "<br><a href='/drupal/user/login'>Login Page Link</a>";
    echo "<hr>";
    die("");
}

