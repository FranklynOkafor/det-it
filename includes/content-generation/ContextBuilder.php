<?php

namespace Detit\ContentGenerator;

if (!defined('ABSPATH')) exit;

class ContextBuilder {

    public function build($product_data, $store_data) {

        $context = [
            'product' => $product_data,
            'store'   => $store_data,
            'meta'    => [
                'timestamp' => current_time('mysql'),
            ],
        ];

        // Attach the ready-to-send prompt so callers don't need to
        // instantiate PromptBuilder themselves.
        $builder           = new PromptBuilder();
        $context['prompt'] = $builder->build($context);

        return $context;
    }
}