<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class detit_RankMath_Adapter implements SEO_Adapter_Interface
{
    const KEY_TITLE = 'rank_math_title';
    const KEY_DESC  = 'rank_math_description';
    const KEY_KW    = 'rank_math_focus_keyword';

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
