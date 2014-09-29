<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
//error_reporting(0);

//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

//remove all trace
global $wpdb;
$prefix = $wpdb->base_prefix;

echo $sql = "DELETE 
		FROM `{$prefix}options` 
		WHERE `option_name` LIKE 'osd_mc_form%'";

echo $wpdb->query($sql); 
exit;