<?php
namespace Detit\Admin;

if (!defined('ABSPATH')) exit;

class AdminAssets
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue($hook)
    {

        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'product') {
            return;
        }

        wp_enqueue_script(
            'detit-admin',
            DETIT_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('detit-admin', 'detitData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('detit_nonce')
        ]);
    }
}
