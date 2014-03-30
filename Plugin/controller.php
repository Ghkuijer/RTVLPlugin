<?php
/*
Plugin Name: RTV Omroep Programmering Plugin
Plugin URI: http://www.ghkictservices.nl
Description: Een plugin om makkelijk een programmering in te voeren in wordpress zodat deze getoond kan worden op de site.
Version: 1.0
Author: Gijs Kuijer
Author URI: http://www.ghkictservices.nl
License: ----
*/

wp_enqueue_script('jquery',plugins_url('js/jquery-1.9.1.min.js', __FILE__ ));

// include controller class file
include("RTVLController.class.php");

// initialize main controller
$controller = new RTVLController();
register_activation_hook( __FILE__, array(  &$controller, 'activatePlugin' ) );
?>