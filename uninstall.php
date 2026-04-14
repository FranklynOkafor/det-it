<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}


function detit_uninstall()
{
    // Drop database table
    global $wpdb;
    $table_name = $wpdb->prefix . 'detit_products';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Cleanup options
    delete_option('detit_api_key');
    delete_option('detit_scan_status');
    delete_option('detit_settings');
}

register_uninstall_hook(__FILE__, 'detit_uninstall');
