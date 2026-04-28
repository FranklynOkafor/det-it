<?php

namespace DetIt;

use Detit\Admin\ProductMetaBox;
use Detit\Admin\AdminAssets;
use Detit\Admin\AjaxHandler;

class Loader
{

    private static array $actions = [];
    private static array $filters = [];

    public static function init()
    {
        self::load_modules();
        self::register_hooks();
        self::run();
    }

    private static function load_modules(): void
    {
        // Support
        require_once DETIT_PLUGIN_DIR . 'includes/Support/Correlation.php';
        require_once DETIT_PLUGIN_DIR . 'includes/Support/Timer.php';
        require_once DETIT_PLUGIN_DIR . 'includes/Support/Logger.php';

        // Content Generation
        require_once DETIT_PLUGIN_DIR . 'includes/content-generation/bootstrap.php';
        \DetIt\ContentGenerator\boot();

        // SEO Audit
        require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/bootstrap.php';
        \DetIt\SeoAudit\boot();

        // Admin Actions
        require_once DETIT_PLUGIN_DIR . 'admin/AdminAssets.php';
        require_once DETIT_PLUGIN_DIR . 'admin/AjaxHandler.php';
        require_once DETIT_PLUGIN_DIR . 'admin/ProductMetaBox.php';

        new \DetIt\Admin\ProductMetaBox();
        new \DetIt\Admin\AdminAssets();
        new \DetIt\Admin\AjaxHandler();


        // Admin
        require_once DETIT_PLUGIN_DIR . 'admin/onboarding/onboarding-fields.php';
        require_once DETIT_PLUGIN_DIR . 'admin/onboarding/onboarding-save.php';
        require_once DETIT_PLUGIN_DIR . 'admin/onboarding/onboarding-controller.php';
        require_once DETIT_PLUGIN_DIR . 'admin/dashboard.php';
        require_once DETIT_PLUGIN_DIR . 'admin/product-panel.php';
        require_once DETIT_PLUGIN_DIR . 'admin/bulk-tools.php';
        require_once DETIT_PLUGIN_DIR . 'admin/Settings/LoggingSettings.php';


        // API
        require_once DETIT_PLUGIN_DIR . 'api/audit-endpoint.php';
        require_once DETIT_PLUGIN_DIR . 'api/generator-endpoint.php';
        require_once DETIT_PLUGIN_DIR . 'api/scan-endpoint.php';
    }

    public static function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        self::$actions[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        ];
    }

    public static function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        self::$filters[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        ];
    }

    private static function register_hooks(): void
    {
        // Admin hooks
        if (is_admin()) {

            \DetIt\Admin\Settings\LoggingSettings::register();

            $dashboard = new \DetIt\Admin\Dashboard();

            self::add_action(
                'admin_menu',
                $dashboard,
                'register_menu'
            );
        }

        // Public hooks


    }

    private static function run(): void
    {
        foreach (self::$actions as $hook) {

            add_action(
                $hook['hook'],
                [$hook['component'], $hook['callback']],
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach (self::$filters as $hook) {

            add_filter(
                $hook['hook'],
                [$hook['component'], $hook['callback']],
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
