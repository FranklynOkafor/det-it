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
define( 'DETIT_FILE', __FILE__ );



// Dependency check 
function detit_woocommerce_active()
{
  return class_exists('WooCommerce');
}

function detit_init()
{

  if (! detit_woocommerce_active()) {
    add_action('admin_notices', 'detit_wc_missing_notice');
    return;
  }

  // Load plugin services
  require_once DETIT_PLUGIN_DIR . 'includes/loader.php';
  \DetIt\Loader::init();

  load_plugin_textdomain('detit', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('plugins_loaded', 'detit_init');


function detit_wc_missing_notice()
{
?>
  <div class="notice notice-error">
    <p><strong>DetIt:</strong> This plugin requires WooCommerce to be active.</p>
  </div>
<?php
}

// Text domain for Translation
load_plugin_textdomain('detit', false, dirname(plugin_basename(__FILE__)) . '/languages');

function detit_activate()
{
  // Check dependency
  if (! is_plugin_active('woocommerce/woocommerce.php')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die('DetIt requires WooCommerce to be installed and active.');
  }

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



// Menu
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
