<?php

namespace Detit\ContentGenerator;

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

    return [
        'store_name'        => get_option('detit_store_name', ''),
        'store_description' => get_option('detit_store_description', ''),
        'target_audience'   => get_option('detit_target_audience', ''),
        'tone'              => get_option('detit_tone', ''),
    ];
}
}