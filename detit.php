<?php

/**
 * Plugin Name: DetIt – WooCommerce SEO Auditor
 * Description: Audits WooCommerce product SEO and generates optimized product content.
 * Version: 0.1.0
 * Author: Emeka
 * License: GPL2+
 * Text Domain: detit
 */

if (! defined('ABSPATH')) {
    exit;
}



// Plugin constants
define('DETIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DETIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DETIT_VERSION', '0.1.0');


// Plugin includes
require_once DETIT_PLUGIN_DIR . 'includes/seo-score.php';
require_once DETIT_PLUGIN_DIR . 'includes/meta-handler.php';
require_once DETIT_PLUGIN_DIR . 'admin/dashboard.php';
require_once DETIT_PLUGIN_DIR . 'admin/product-panel.php';
require_once DETIT_PLUGIN_DIR . 'admin/bulk-tools.php';
require_once DETIT_PLUGIN_DIR . 'api/audit-endpoint.php';
require_once DETIT_PLUGIN_DIR . 'api/generator-endpoint.php';
require_once DETIT_PLUGIN_DIR . 'api/scan-endpoint.php';


// Dependency check 
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}




function detit_activate()
{
    // Create necessary database tables
    global $wpdb;
    $table_name = $wpdb->prefix . 'detit_products';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		product_id bigint(20) NOT NULL,
		seo_score int DEFAULT 0,
		last_audit datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY product_id (product_id)
	) $charset_collate";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'detit_activate');

function detit_deactivate()
{
    // Cleanup options
    delete_option('detit_api_key');
    delete_option('detit_scan_status');
}

register_deactivation_hook(__FILE__, 'detit_deactivate');

function detit_uninstall()
{
    // Drop database table
    global $wpdb;
    $table_name = $wpdb->prefix . 'detit_products';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Cleanup options
    delete_option('detit_api_key');
    delete_option('detit_scan_status');
}

register_uninstall_hook(__FILE__, 'detit_uninstall');

function detit_admin_menu()
{
    add_menu_page(
        'DetIt SEO',
        'DetIt SEO',
        'manage_options',
        'detit-dashboard',
        'detit_dashboard_page',
        'dashicons-chart-bar',
        6
    );

    add_submenu_page(
        'detit-dashboard',
        'Bulk Tools',
        'Bulk Tools',
        'manage_options',
        'detit-bulk-tools',
        'detit_bulk_tools_page'
    );
}
add_action('admin_menu', 'detit_admin_menu');
