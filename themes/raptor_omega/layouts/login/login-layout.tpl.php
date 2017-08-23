<?php
global $base_url;

$folder = realpath(dirname(__FILE__));
include("$folder/../render-login-header.php");

$mylogourl = $base_url.'/sites/all/themes/raptor_omega/images/raptorwordylogo1.png';
?>
<div id="raptor-login-container" class="l-page" style="width: 800px; margin: 0 auto;">
  <?php

  ?>

  <header class="l-header" role="banner">
    <?php print render($page['header']); ?>
    <?php print render($page['navigation']); ?>
  </header>

  <div style="width: 270px; padding: 0; margin: 0; float: left;">
    <img src="<?php print $mylogourl; ?>" alt="RAPTOR Logo" />
  </div>  
  <div style="width: auto; padding: 0; margin: 0; float: left;">
    <?php print render($page['highlighted']); ?>
    <a id="main-content"></a>
    <?php print render($title_prefix); ?>
    <?php if ($title): ?>
      <h1><?php print $title; ?></h1>
    <?php endif; ?>
    <?php print render($title_suffix); ?>
    <?php print $messages; ?>
    <?php print render($tabs); ?>
    <?php print render($page['help']); ?>
    <?php print render($page['content']); ?>
    <?php print $feed_icons; ?>
  </div>

 </div>