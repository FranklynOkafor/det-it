<?php

namespace Detit\ContentGenerator;

if (!defined('ABSPATH')) exit;

class ContextBuilder {

    public function build($product_data, $store_data) {

        return [
            'product' => $product_data,
            'store'   => $store_data,
            'meta'    => [
                'timestamp' => current_time('mysql'),
            ]
        ];
    }
}