<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class detit_Yoast_Adapter implements SEO_Adapter_Interface
{
    const KEY_TITLE = '_yoast_wpseo_title';
    const KEY_DESC  = '_yoast_wpseo_metadesc';
    const KEY_KW    = '_yoast_wpseo_focuskw';

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
        return (string) get_post_meta($post_id, self::KEY_KW, true);
    }

    public function set_meta(int $post_id, string $title, string $description, string $keyword): void
    {
        update_post_meta($post_id, self::KEY_TITLE, $title);
        update_post_meta($post_id, self::KEY_DESC, $description);
        update_post_meta($post_id, self::KEY_KW, $keyword);
    }
}
