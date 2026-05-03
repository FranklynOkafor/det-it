<?php

/**
 * Plugin Name: DetIt – WooCommerce AI Content Generator
 * Description: Generates optimized product content (titles, descriptions, tags) using AI.
 * Version: 0.2.0
 * Author: DetIt Team
 * License: GPL2+
 * Text Domain: DetIt
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('DETIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DETIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DETIT_VERSION', '0.2.0');
define('DETIT_FILE', __FILE__);

/**
 * Dependency check: Ensure WooCommerce is active.
 */
function detit_woocommerce_active() {
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    return class_exists('WooCommerce') || is_plugin_active('woocommerce/woocommerce.php');
}

/**
 * Initialize the plugin.
 */
function detit_init() {
    if (!detit_woocommerce_active()) {
        add_action('admin_notices', 'detit_wc_missing_notice');
        return;
    }

    // 1. Core AI Components
    require_once DETIT_PLUGIN_DIR . 'includes/ai/api-auth.php';
    require_once DETIT_PLUGIN_DIR . 'includes/ai/OutputSchema.php';
    require_once DETIT_PLUGIN_DIR . 'includes/ai/DataCollector.php';
    require_once DETIT_PLUGIN_DIR . 'includes/ai/TagEngine.php';
    require_once DETIT_PLUGIN_DIR . 'includes/ai/ContextBuilder.php';
    require_once DETIT_PLUGIN_DIR . 'includes/ai/PromptBuilder.php';
    require_once DETIT_PLUGIN_DIR . 'includes/ai/ContentGenerator.php';

    // 2. SEO Integration
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-seo-adapter-interface.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-yoast-adapter.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-rankmath-adapter.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-aioseo-adapter.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-seo-manager.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-seo-sync.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo/class-detit-seo-migrator.php';

    \DetIt\SEO\SEO_Sync::boot();
    \DetIt\SEO\SEO_Migrator::boot();

    // 3. Admin Functionality
    require_once DETIT_PLUGIN_DIR . 'includes/admin/AdminAssets.php';
    require_once DETIT_PLUGIN_DIR . 'includes/admin/AjaxHandler.php';
    require_once DETIT_PLUGIN_DIR . 'includes/admin/ProductMetaBox.php';
    require_once DETIT_PLUGIN_DIR . 'includes/admin/dashboard.php';
    require_once DETIT_PLUGIN_DIR . 'includes/admin/onboarding/onboarding-fields.php';
    require_once DETIT_PLUGIN_DIR . 'includes/admin/onboarding/onboarding-save.php';
    require_once DETIT_PLUGIN_DIR . 'includes/admin/onboarding/onboarding-controller.php';

    // Initialize Admin Classes
    new \DetIt\Admin\AdminAssets();
    new \DetIt\Admin\AjaxHandler();
    new \DetIt\Admin\ProductMetaBox();

    if (is_admin()) {
        $dashboard = new \DetIt\Admin\Dashboard();
        add_action('admin_menu', [$dashboard, 'register_menu']);
    }

    load_plugin_textdomain('detit', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'detit_init');

/**
 * Admin notice if WooCommerce is missing.
 */
function detit_wc_missing_notice() {
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

/**
 * Activation Hook.
 */
function detit_activate() {
    if (!detit_woocommerce_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('DetIt requires WooCommerce to be installed and active.');
    }
    set_transient('detit_activation_redirect', true, 30);
}
register_activation_hook(__FILE__, 'detit_activate');

/**
 * Deactivation Hook.
 */
function detit_deactivate() {
    delete_option('detit_api_key');
}
register_deactivation_hook(__FILE__, 'detit_deactivate');

/**
 * Handle activation redirect.
 */
add_action('admin_init', function() {
    if (get_transient('detit_activation_redirect')) {
        delete_transient('detit_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_safe_redirect(admin_url('admin.php?page=detit-dashboard'));
            exit;
        }
    }
});
