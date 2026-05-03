<?php

namespace DetIt\ContentGenerator;

if (!defined('ABSPATH')) exit;

class DataCollector {

    public function get_product_data($product_id) {

        $product = wc_get_product($product_id);

        if (!$product) {
            return null;
        }

        return [
            'id'          => $product_id,
            'title'       => $this->get_title($product),
            'description' => $this->get_description($product),
            'short_desc'  => $this->get_short_description($product),
            'categories'  => $this->get_categories($product_id),
            'tags'        => $this->get_tags($product_id),
            'images'      => $this->get_images($product_id),
        ];
    }

    private function get_title($product) {
        return $product->get_name() ?: 'Untitled Product';
    }

    private function get_description($product) {
        return $product->get_description() ?: '';
    }

    private function get_short_description($product) {
        return $product->get_short_description() ?: '';
    }

    private function get_categories($product_id) {
        $terms = get_the_terms($product_id, 'product_cat');

        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }

        return wp_list_pluck($terms, 'name');
    }

    private function get_tags($product_id) {
        $terms = get_the_terms($product_id, 'product_tag');

        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }

        return wp_list_pluck($terms, 'name');
    }

    private function get_images($product_id) {

        $image_ids = [];

        // Featured image
        $featured_id = get_post_thumbnail_id($product_id);
        if ($featured_id) {
            $image_ids[] = wp_get_attachment_url($featured_id);
        }

        // Gallery images
        $gallery_ids = get_post_meta($product_id, '_product_image_gallery', true);

        if ($gallery_ids) {
            $ids = explode(',', $gallery_ids);

            foreach ($ids as $id) {
                $url = wp_get_attachment_url($id);
                if ($url) {
                    $image_ids[] = $url;
                }
            }
        }

        return $image_ids;
    }

    public function get_store_data() {

        $settings = get_option('detit_context', []);
        
        if (!is_array($settings)) {
            $settings = [];
        }

        $store_name = $settings['store_name'] ?? '';
        
        // Determine industry/niche
        $industry = $settings['industry_niche'] ?? '';
        if ($industry === 'other' && !empty($settings['industry_niche_custom'])) {
            $industry = $settings['industry_niche_custom'];
        }

        // Determine store type
        $store_type = $settings['store_type'] ?? '';

        // Build a store description based on context settings
        $desc_parts = [];
        if ($store_type) {
            $desc_parts[] = ucfirst($store_type) . ' store';
        }
        if ($industry) {
            $desc_parts[] = 'in the ' . str_replace('_', ' ', $industry) . ' industry';
        }
        $store_description = implode(' ', $desc_parts);

        // Determine target audience
        $audience_type = $settings['target_audience_type'] ?? '';
        if ($audience_type === 'other' && !empty($settings['target_audience_type_custom'])) {
            $audience_type = $settings['target_audience_type_custom'];
        }
        $audience_detail = $settings['target_audience_detail'] ?? '';
        $target_audience = trim(str_replace('_', ' ', $audience_type) . ' ' . $audience_detail);

        $tone = $settings['content_tone'] ?? '';

        return [
            'store_name'        => $store_name,
            'store_description' => $store_description,
            'target_audience'   => $target_audience,
            'tone'              => $tone,
        ];
    }
}
