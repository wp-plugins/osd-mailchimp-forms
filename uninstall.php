<?php
//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

//remove all trace
global $wpdb;
$prefix = $wpdb->base_prefix;

$wpdb->query("DELETE FROM `{$prefix}options` WHERE `option_name` LIKE 'osd_mc_form%'");