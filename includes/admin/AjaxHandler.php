<?php

namespace DetIt\Admin;

use DetIt\ContentGenerator\ContentGenerator;

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

        $product_id = $this->check_authorization();

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

        $product_id = $this->check_authorization();
        $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        $value = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';

        if (!$field) {
            wp_send_json_error(['message' => 'Invalid data provided']);
        }

        try {
            $this->save_field($product_id, $field, $value);
            
            // Trigger SEO sync if an SEO field was saved
            if (str_starts_with($field, 'seo.')) {
                if (class_exists('\DetIt\SEO\SEO_Sync')) {
                    \DetIt\SEO\SEO_Sync::push_to_plugin($product_id);
                }
            }
            
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function add_all_fields()
    {
        check_ajax_referer('detit_nonce', 'nonce');

        $product_id = $this->check_authorization();
        $fields_json = isset($_POST['fields']) ? wp_unslash($_POST['fields']) : '';

        if (empty($fields_json)) {
            wp_send_json_error(['message' => 'Invalid data provided']);
        }

        $fields = json_decode($fields_json, true);
        if (!is_array($fields)) {
            wp_send_json_error(['message' => 'Invalid fields format']);
        }

        try {
            $seo_updated = false;
            foreach ($fields as $field => $value) {
                $this->save_field($product_id, sanitize_text_field($field), $value);
                if (str_starts_with($field, 'seo.')) {
                    $seo_updated = true;
                }
            }
            
            if ($seo_updated && class_exists('\DetIt\SEO\SEO_Sync')) {
                \DetIt\SEO\SEO_Sync::push_to_plugin($product_id);
            }
            
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Verifies that the request contains a valid product ID and the current user
     * has permission to edit it. Prevents privilege escalation.
     *
     * @return int The sanitized product ID.
     */
    private function check_authorization()
    {
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

        if (!$product_id || !current_user_can('edit_post', $product_id)) {
            wp_send_json_error(
                ['message' => esc_html__('Unauthorized action.', 'detit')],
                403
            );
        }

        return $product_id;
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
                $tags = array_filter(array_map('trim', explode(',', $clean_value)));
                $term_ids = [];

                foreach ($tags as $tag_name) {
                    $term = term_exists($tag_name, 'product_tag');
                    if (!$term) {
                        $inserted = wp_insert_term($tag_name, 'product_tag');
                        if (!is_wp_error($inserted)) {
                            $term_ids[] = (int) $inserted['term_id'];
                        }
                    } else {
                        $term_ids[] = (int) (is_array($term) ? $term['term_id'] : $term);
                    }
                }

                wp_set_object_terms($product_id, $term_ids, 'product_tag');
                break;
            case 'seo.meta_title':
                update_post_meta($product_id, '_detit_meta_title', $clean_value);
                break;
            case 'seo.meta_description':
                update_post_meta($product_id, '_detit_meta_description', $clean_value);
                break;
            case 'seo.focus_keyword':
                update_post_meta($product_id, '_detit_focus_keyword', $clean_value);
                break;
            default:
                update_post_meta($product_id, sanitize_text_field($field), $clean_value);
                break;
        }
    }
}
