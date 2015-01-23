<?php
/*
Plugin Name: OSD MailChimp Forms
Plugin URI: http://outsidesource.com
Description: A plugin for adding one or more signup forms for MailChimp lists.
Version: 2.0
Author: OSD Web Development Team
Author URI: http://outsidesource.com
License: GPL2v2
*/

defined('ABSPATH') or die("No script kiddies please!");

include_once('includes/OSDMailChimp.php');

if (defined('DOING_AJAX')) {
	include_once('includes/external_request.php');
}

if (is_admin()) {
	include_once('includes/global_settings.php');
	include_once('includes/form_settings.php');
	if (defined('DOING_AJAX')) {
		include_once('includes/shortcode.php');
	}
} else {
	$osd_mc_form_pg_options =get_option('osd_mc_form_options');
	if(isset($osd_mc_form_pg_options['mcKey']) &&  $osd_mc_form_pg_options['mcKey'] != '') {
		include_once('includes/shortcode.php');
		include_once('includes/js.php');
	}
}

// Add settings page link to plugins page
function osd_mc_settings_link_generate($links) { 
	$settings_link = '<a href="admin.php?page=osd-mailchimp-form-options">Settings</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'osd_mc_settings_link_generate' );