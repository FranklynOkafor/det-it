<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class detit_AIOSEO_Adapter implements SEO_Adapter_Interface
{
    const KEY_TITLE = '_aioseo_title';
    const KEY_DESC  = '_aioseo_description';
    const KEY_KW    = '_aioseo_keyphrases';

    public function get_title(int $post_id): string
    {
        return (string) get_post_meta($post_id, self::KEY_TITLE, true);
    }

    public function get_description(int $post_id): string
    {
        return (string) get_post_meta($post_id, self::KEY_DESC, true);
    }

    public function get_keyword(int $post_id): string
    {
        $raw = get_post_meta($post_id, self::KEY_KW, true);
        if (empty($raw)) {
            return '';
        }

        $data = json_decode($raw, true);

        if (!is_array($data) || empty($data[0]['keyphrase'])) {
            return '';
        }

        return (string) $data[0]['keyphrase'];
    }

    public function set_meta(int $post_id, string $title, string $description, string $keyword): void
    {
        update_post_meta($post_id, self::KEY_TITLE, $title);
        update_post_meta($post_id, self::KEY_DESC, $description);
        
        // AIOSEO stores keyphrases as a JSON array
        $keyphrase_data = wp_json_encode([['keyphrase' => $keyword]]);
        update_post_meta($post_id, self::KEY_KW, $keyphrase_data);
    }
}
