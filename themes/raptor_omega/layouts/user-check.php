<?php
//Warning --- do not apply this block to the login page otherwise you can never login!
global $user;
global $base_url;
if($user->uid==0)
{
    echo "<hr>";
    echo "<h1>You are NOT an authorized user.  First login by following the link below.</h1>";
    //echo( "<h1>USER-->".print_r($user,true)."</h1>" );
    echo "<br><a href='$base_url/user/login'>Login Page Link</a>";
    echo "<hr>";
    die("");
}

