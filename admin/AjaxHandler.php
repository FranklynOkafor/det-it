<?php
namespace Detit\Admin;

if (!defined('ABSPATH')) exit;

class AjaxHandler
{

    public function __construct()
    {
        add_action('wp_ajax_detit_generate', [$this, 'handle']);
    }

    public function handle()
    {

        check_ajax_referer('detit_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if (!$product_id) {
            wp_send_json_error(['message' => 'Invalid product ID']);
        }

        // Placeholder logic (next phase will replace this)
        wp_send_json_success([
            'message' => 'DetIt process initialized for product ID ' . $product_id
        ]);
    }
}
