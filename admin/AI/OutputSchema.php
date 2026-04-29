<?php

namespace Detit\ContentGenerator;

if (!defined('ABSPATH')) exit;

/**
 * Defines the strict JSON output schema the AI must return.
 *
 * Keeping the schema in its own class means:
 *  - PromptBuilder can embed it in prompts via get_schema_json()
 *  - ResponseParser (future) can validate responses against get_schema()
 *  - The shape of output is a single source of truth
 */
class OutputSchema
{

    /**
     * The canonical schema as a PHP array.
     * Use this for validation / parsing downstream.
     *
     * @return array
     */
    public static function get_schema(): array
    {
        return [
            'type'                 => 'object',
            'additionalProperties' => false,
            'required'             => [
                'title',
                'short_description',
                'description',
                'seo',
                'tags',
            ],
            'properties' => [

                'title' => [
                    'type'        => 'string',
                    'description' => 'Optimised product title. Clear, keyword-rich, max 70 characters.',
                    'maxLength'   => 70,
                ],

                'short_description' => [
                    'type'        => 'string',
                    'description' => 'Compelling 1–2 sentence hook shown on shop/archive pages. Plain text, no HTML. Max 160 characters.',
                    'maxLength'   => 160,
                ],

                'description' => [
                    'type'        => 'string',
                    'description' => 'Full product description in HTML. Use <p>, <ul>/<li>, and <strong> only. Min 80 words.',
                    'minLength'   => 80,
                ],

                'seo' => [
                    'type'                 => 'object',
                    'additionalProperties' => false,
                    'required'             => ['meta_title', 'meta_description', 'focus_keyword'],
                    'properties'           => [

                        'meta_title' => [
                            'type'        => 'string',
                            'description' => 'SEO <title> tag. Include the primary keyword near the start. Max 60 characters.',
                            'maxLength'   => 60,
                        ],

                        'meta_description' => [
                            'type'        => 'string',
                            'description' => 'SEO meta description. Summarise the product and include a CTA. Max 155 characters.',
                            'maxLength'   => 155,
                        ],

                        'focus_keyword' => [
                            'type'        => 'string',
                            'description' => 'Single primary keyword or keyphrase for this product page.',
                        ],
                    ],
                ],

                'tags' => [
                    'type'        => 'array',
                    'description' => 'Suggested product tags. 3–8 short, relevant terms.',
                    'items'       => ['type' => 'string'],
                    'minItems'    => 3,
                    'maxItems'    => 8,
                ],
            ],
        ];
    }

    /**
     * Returns the schema as a pretty-printed JSON string for embedding in prompts.
     *
     * @return string
     */
    public static function get_schema_json(): string
    {
        return json_encode(self::get_schema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Returns a minimal example of a valid response, useful for few-shot prompting
     * or documentation purposes.
     *
     * @return array
     */
    public static function get_example(): array
    {
        return [
            'title'             => 'Organic Cotton Crew-Neck T-Shirt – Unisex',
            'short_description' => 'Breathable, GOTS-certified organic cotton tee. Preshrunk, ethically made, available in 12 colours.',
            'description'       => '<p>Meet your new favourite everyday tee. Cut from <strong>100% GOTS-certified organic cotton</strong>, this crew-neck is softer, more breathable, and kinder to the planet than conventional alternatives.</p><ul><li>Preshrunk fabric — keeps its shape wash after wash</li><li>Ethically manufactured under fair-trade conditions</li><li>Unisex sizing from XS to 3XL</li><li>Available in 12 seasonal colours</li></ul><p>Whether you\'re dressing it up or down, this tee delivers effortless comfort from morning to night.</p>',
            'seo'               => [
                'meta_title'       => 'Organic Cotton Crew-Neck T-Shirt | Unisex & Ethically Made',
                'meta_description' => 'Shop our GOTS-certified organic cotton tee. Preshrunk, ethically made, in 12 colours and sizes XS–3XL. Free UK delivery over £40.',
                'focus_keyword'    => 'organic cotton t-shirt',
            ],
            'tags' => [
                'organic cotton',
                'sustainable fashion',
                'unisex t-shirt',
                'ethical clothing',
                'crew neck',
            ],
        ];
    }
}
