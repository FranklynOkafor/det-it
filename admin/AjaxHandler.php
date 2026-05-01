<?php

namespace Detit\Admin;

use Detit\ContentGenerator\ContentGenerator;

if (!defined('ABSPATH')) exit;

class AjaxHandler
{

    public function __construct()
    {
        add_action('wp_ajax_detit_generate', [$this, 'handle']);
        add_action('wp_ajax_detit_add_single_field', [$this, 'add_single_field']);
        add_action('wp_ajax_detit_add_all_fields', [$this, 'add_all_fields']);
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

    public function add_single_field()
    {
        check_ajax_referer('detit_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        $value = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';

        if (!$product_id || !$field) {
            wp_send_json_error(['message' => 'Invalid data provided']);
        }

        try {
            $this->save_field($product_id, $field, $value);
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function add_all_fields()
    {
        check_ajax_referer('detit_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $fields_json = isset($_POST['fields']) ? wp_unslash($_POST['fields']) : '';

        if (!$product_id || empty($fields_json)) {
            wp_send_json_error(['message' => 'Invalid data provided']);
        }

        $fields = json_decode($fields_json, true);
        if (!is_array($fields)) {
            wp_send_json_error(['message' => 'Invalid fields format']);
        }

        try {
            foreach ($fields as $field => $value) {
                $this->save_field($product_id, sanitize_text_field($field), $value);
            }
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    private function save_field($product_id, $field, $value)
    {
        $clean_value = wp_kses_post($value);

        switch ($field) {
            case 'title':
                wp_update_post(['ID' => $product_id, 'post_title' => $clean_value]);
                break;
            case 'description':
                wp_update_post(['ID' => $product_id, 'post_content' => $clean_value]);
                break;
            case 'short_description':
                wp_update_post(['ID' => $product_id, 'post_excerpt' => $clean_value]);
                break;
            case 'tags':
                $tags = array_map('trim', explode(',', $clean_value));
                wp_set_post_terms($product_id, $tags, 'product_tag', false);
                break;
            default:
                update_post_meta($product_id, sanitize_text_field($field), $clean_value);
                break;
        }
    }
}
