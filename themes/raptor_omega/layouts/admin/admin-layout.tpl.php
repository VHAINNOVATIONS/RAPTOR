<?php
global $raptor_context;
?>

<style>
  .top-nav {
    width: 95%;
    min-width: 960px;
    height: 35px;
    margin: 0 auto;
    z-index: 99;
    position: relative; }
  .top-nav ul {
    float: right;
    color: lightblue;
    margin: 10px 0; }
  .top-nav ul li {
    display: inline-block;
    border-right: 1px solid #ddd;
    padding: 0 5px; }
  .top-nav ul li a {
    text-decoration: none;
    color: #fff;
    font-size: .9em; }
  .top-nav ul li a:hover {
    color: #0c63a1; }
  .top-nav ul li:last-child, .top-nav ul li:last-child a {
    border: 0; }    
  
        .generic-warning-area {
            background-color: yellow;
            border: red;
        }
        </style>

<div id="raptor-admin-container" class="l-page">
  <?php

  $folder = realpath(dirname(__FILE__));
  include("$folder/../render-admin-header.php");
  include("$folder/../render-timeout-warning.php");
  ?>
  <div class="l-main">
    <div class="l-content" role="main">
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
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php print render($page['content']); ?>
      <?php print $feed_icons; ?>
    </div>

  </div>
</div>
