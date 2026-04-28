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
define('DETIT_FILE', __FILE__);



// Dependency check 
function detit_woocommerce_active()
{
  if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }
  return class_exists('WooCommerce') || is_plugin_active('woocommerce/woocommerce.php');
}

function detit_init()
{

  if (! detit_woocommerce_active()) {
    add_action('admin_notices', 'detit_wc_missing_notice');
    return;
  }

  // Load plugin services
  require_once DETIT_PLUGIN_DIR . 'includes/loader.php';
  require_once DETIT_PLUGIN_DIR . 'includes/plugin.php';

  function run_detit()
  {

    $plugin = new \DetIt\Plugin();
    $plugin->run();
  }

  run_detit();

  load_plugin_textdomain('detit', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('plugins_loaded', 'detit_init');


function detit_wc_missing_notice()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  $plugin_path = 'woocommerce/woocommerce.php';
  $is_installed = file_exists(WP_PLUGIN_DIR . '/' . $plugin_path);
  $action_url = $is_installed 
    ? wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $plugin_path), 'activate-plugin_' . $plugin_path)
    : wp_nonce_url(admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');

  $action_text = $is_installed ? 'Activate WooCommerce' : 'Install WooCommerce';
?>
  <div class="notice notice-error is-dismissible">
    <p><strong>DetIt:</strong> This plugin requires WooCommerce to be installed and active.</p>
    <p><a href="<?php echo esc_url($action_url); ?>" class="button button-primary"><?php echo esc_html($action_text); ?></a></p>
  </div>
<?php
}

// Text domain for Translation
load_plugin_textdomain('detit', false, dirname(plugin_basename(__FILE__)) . '/languages');

function detit_activate()
{
  // Check dependency
  if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }

  if (!is_plugin_active('woocommerce/woocommerce.php') && !class_exists('WooCommerce')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die('DetIt requires WooCommerce to be installed and active. <br><br><a href="' . admin_url('plugins.php') . '" class="button button-primary">Back to Plugins</a>');
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

  set_transient('detit_activation_redirect', true, 30);
}

register_activation_hook(__FILE__, 'detit_activate');

function detit_deactivate()
{
  // Cleanup options
  delete_option('detit_api_key');
  delete_option('detit_scan_status');
}

register_deactivation_hook(__FILE__, 'detit_deactivate');



// UI code safely migrated to wrapped admin OOP classes.

add_action('admin_init', function() {
    if (get_transient('detit_activation_redirect')) {
        delete_transient('detit_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_safe_redirect(admin_url('admin.php?page=detit-dashboard'));
            exit;
        }
    }
});


