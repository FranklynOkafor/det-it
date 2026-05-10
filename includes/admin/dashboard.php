<?php

namespace DetIt\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard
{
    public function register_menu()
    {
        add_menu_page(
            esc_html__('DetIt Settings', 'detit'),        // Page title
            esc_html__('DetIt Settings', 'detit'),        // Menu title
            'manage_options',        // Capability
            'detit-settings',        // Slug
            [$this, 'render'],       // Callback
            'dashicons-edit',        // Icon
            25                       // Position
        );
    }

    public function render()
    {
        // First handle any settings form submissions
        $settings = new \DetIt\Admin\Settings\DetIt_Settings_Page();
        $settings->handle_submit();

        // Render the settings page
        $settings->render_page();
    }
}
