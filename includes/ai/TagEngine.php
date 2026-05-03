<?php

namespace DetIt\ContentGenerator;

if (!defined('ABSPATH')) exit;

class TagEngine
{
    public function getRelevantTags(string $title, string $description): array
    {
        // Step 1: Fetch ALL global tags from the site
        $terms = get_terms([
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        // Normalize tags to lowercase
        $tag_names = array_map(fn($t) => strtolower(trim($t->name)), $terms);

        // Step 2: Build product text for matching
        $product_text = strtolower($title . ' ' . $description);

        // Step 3: Filter relevance
        $relevant = array_filter($tag_names, function ($tag) use ($product_text) {
            return str_contains($product_text, $tag);
        });

        // Reset array keys to ensure it's an indexed array
        $relevant = array_values($relevant);

        // If we found 6 or more, we'll cap it at 6 exactly.
        if (count($relevant) >= 6) {
            return array_slice($relevant, 0, 6);
        }

        // Otherwise return whatever we found so AI can use them as candidates and generate more.
        return $relevant;
    }
}
