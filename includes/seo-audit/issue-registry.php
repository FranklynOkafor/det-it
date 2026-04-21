<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * SEO Issue Registry
 * 
 * Defines the taxonomy of all SEO issues, their severity, and scoring weights.
 */
return [
    // META
    'missing_meta_description' => [
        'severity' => 'critical',
        'weight' => 12,
        'category' => 'meta',
    ],
    'meta_description_too_short' => [
        'severity' => 'medium',
        'weight' => 6,
        'category' => 'meta',
    ],

    // KEYWORD
    'missing_focus_keyword' => [
        'severity' => 'critical',
        'weight' => 12,
        'category' => 'keyword',
    ],

    // TITLE
    'missing_product_title' => [
        'severity' => 'critical',
        'weight' => 12,
        'category' => 'title',
    ],

    // SHORT DESCRIPTION
    'missing_short_description' => [
        'severity' => 'critical',
        'weight' => 12,
        'category' => 'short_description',
    ],
    'short_description_too_short' => [
        'severity' => 'medium',
        'weight' => 6,
        'category' => 'short_description',
    ],
    'short_description_not_seo_optimized' => [
        'severity' => 'low',
        'weight' => 3,
        'category' => 'short_description',
    ],

    // LONG DESCRIPTION
    'missing_long_description' => [
        'severity' => 'critical',
        'weight' => 12,
        'category' => 'long_description',
    ],
    'long_description_too_short' => [
        'severity' => 'medium',
        'weight' => 6,
        'category' => 'long_description',
    ],
    'long_description_not_structured' => [
        'severity' => 'medium',
        'weight' => 6,
        'category' => 'long_description',
    ],

    // CONTENT STRUCTURE
    'no_headings_in_description' => [
        'severity' => 'medium',
        'weight' => 6,
        'category' => 'content_structure',
    ],
    'no_bullet_lists' => [
        'severity' => 'low',
        'weight' => 3,
        'category' => 'content_structure',
    ],
];
