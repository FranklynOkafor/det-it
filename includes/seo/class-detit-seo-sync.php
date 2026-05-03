<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class SEO_Sync
{
    private static bool $is_syncing = false;

    public static function boot(): void
    {
        // Pull changes from Plugin to DetIt on save
        add_action('save_post_product', [self::class, 'pull_from_plugin'], 99, 2);
    }

    /**
     * Push DetIt canonical meta into the active SEO plugin.
     * Should be called right after DetIt metadata is generated or saved.
     *
     * @param int $post_id
     */
    public static function push_to_plugin(int $post_id): void
    {
        if (self::$is_syncing) {
            return;
        }

        $adapter = SEO_Manager::get_adapter();
        if (!$adapter) {
            return;
        }

        self::$is_syncing = true;

        $title = (string) get_post_meta($post_id, '_detit_meta_title', true);
        $desc  = (string) get_post_meta($post_id, '_detit_meta_description', true);
        $kw    = (string) get_post_meta($post_id, '_detit_focus_keyword', true);

        $adapter->set_meta($post_id, $title, $desc, $kw);

        update_post_meta($post_id, '_detit_last_source', 'DetIt');
        update_post_meta($post_id, '_detit_last_synced_at', time());

        self::$is_syncing = false;
    }

    /**
     * Pull meta from the active SEO plugin into DetIt if the user edited it there.
     * Hooked to save_post_product at a late priority.
     *
     * @param int      $post_id
     * @param \WP_Post $post
     */
    public static function pull_from_plugin(int $post_id, \WP_Post $post): void
    {
        if (self::$is_syncing || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $adapter = SEO_Manager::get_adapter();
        if (!$adapter) {
            return;
        }

        self::$is_syncing = true;

        $plugin_title = $adapter->get_title($post_id);
        $plugin_desc  = $adapter->get_description($post_id);
        $plugin_kw    = $adapter->get_keyword($post_id);

        $detit_title = (string) get_post_meta($post_id, '_detit_meta_title', true);
        $detit_desc  = (string) get_post_meta($post_id, '_detit_meta_description', true);
        $detit_kw    = (string) get_post_meta($post_id, '_detit_focus_keyword', true);

        $changed = false;

        // Never overwrite DetIt with empty values unless explicitly doing so
        if ($plugin_title !== '' && $plugin_title !== $detit_title) {
            update_post_meta($post_id, '_detit_meta_title', $plugin_title);
            $changed = true;
        }

        if ($plugin_desc !== '' && $plugin_desc !== $detit_desc) {
            update_post_meta($post_id, '_detit_meta_description', $plugin_desc);
            $changed = true;
        }

        if ($plugin_kw !== '' && $plugin_kw !== $detit_kw) {
            update_post_meta($post_id, '_detit_focus_keyword', $plugin_kw);
            $changed = true;
        }

        if ($changed) {
            update_post_meta($post_id, '_detit_last_source', 'plugin');
            update_post_meta($post_id, '_detit_last_synced_at', time());
        }

        self::$is_syncing = false;
    }
}
