<?php

namespace DetIt\ContentGenerator;

if (!defined('ABSPATH')) exit;

class ContextBuilder
{

    public function build($product_data, $store_data)
    {
        $tagEngine = new TagEngine();

        $relevant_tags = $tagEngine->getRelevantTags(
            $product_data['title'] ?? '',
            $product_data['description'] ?? ''
        );

        $context = [
            'product'       => $product_data,
            'store'         => $store_data,
            'relevant_tags' => $relevant_tags,
            'meta'          => [
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
