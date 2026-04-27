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
            'DetIt Settings',        // Page title
            'DetIt',                 // Menu title
            'manage_options',        // Capability
            'detit-dashboard',       // Slug
            [$this, 'render'],       // Callback
            'dashicons-edit',        // Icon
            25                       // Position
        );
    }

    public function render()
    {
        // First handle any onboarding form submissions
        $onboarding = new \DetIt\Admin\Onboarding\Detit_Onboarding_Controller();
        $onboarding->handle_submit();

        // Render the onboarding/settings page
        // The controller will dynamically adjust titles based on completion status
        $onboarding->render_page();
    }
}