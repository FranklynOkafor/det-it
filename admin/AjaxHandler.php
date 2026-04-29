<?php

namespace Detit\Admin;

use Detit\ContentGenerator\ContentGenerator;

if (!defined('ABSPATH')) exit;

class AjaxHandler
{

    public function __construct()
    {
        add_action('wp_ajax_detit_generate', [$this, 'handle']);
    }

    public function handle()
    {
        // Allow enough time for the Gemini API round-trip
        set_time_limit(120);

        check_ajax_referer('detit_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if (!$product_id) {
            wp_send_json_error(['message' => 'Invalid product ID']);
        }

        try {
            $generator = new ContentGenerator();
            $result    = $generator->generate($product_id);

            wp_send_json_success([
                'result' => $result,
            ]);

        } catch (\RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
