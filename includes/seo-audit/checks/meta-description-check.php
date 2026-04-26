<?php

if (!defined('ABSPATH')) {
    exit;
}

use DetIt\Meta\Meta_Handler;

class MetaDescriptionCheck
{
    const MIN_LENGTH = 120;
    const MAX_LENGTH = 160;

    public function check(array $product_data): array
    {
        $product_id = $product_data['id'];
        $issues = [];

        $description = Meta_Handler::get_meta_description($product_id);
        $keyword     = Meta_Handler::get_focus_keyword($product_id);
        $trimmed_desc = trim($description);

        // 1. Missing
        if ($trimmed_desc === '') {
            $issues[] = 'missing_meta_description';
            return $issues; // stop here, no point checking further
        }

        $length = mb_strlen($trimmed_desc);

        // 2. Too short
        if ($length < self::MIN_LENGTH) {
            $issues[] = 'short_meta_description';
        }

        // 3. Too long
        if ($length > self::MAX_LENGTH) {
            $issues[] = 'long_meta_description';
        }

        // 4. Keyword inclusion
        if ($keyword && mb_stripos($trimmed_desc, $keyword) === false) {
            $issues[] = 'missing_keyword_in_meta_description';
        }

        return $issues;
    }
}