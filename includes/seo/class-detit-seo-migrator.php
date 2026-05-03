<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class SEO_Migrator
{
    const BATCH_SIZE = 50;

    public static function boot(): void
    {
        add_action('activated_plugin', [self::class, 'handle_plugin_change']);
        add_action('deactivated_plugin', [self::class, 'handle_plugin_change']);
        add_action('admin_init', [self::class, 'process_migration']);
    }

    /**
     * Triggered when any plugin is activated or deactivated.
     * We re-detect the active SEO plugin and if it changed, flag for migration.
     */
    public static function handle_plugin_change(): void
    {
        // Reset cache to ensure fresh detection
        SEO_Manager::reset_cache();
        $current_plugin = SEO_Manager::detect();
        $synced_plugin  = get_option('detit_synced_plugin', SEO_Manager::PLUGIN_NONE);

        if ($current_plugin !== $synced_plugin) {
            update_option('detit_needs_migration', 'yes');
            update_option('detit_migration_offset', 0);
            update_option('detit_synced_plugin', $current_plugin);
        }
    }

    /**
     * Processes a batch of products to sync their DetIt meta into the newly active SEO plugin.
     * Hooked on admin_init.
     */
    public static function process_migration(): void
    {
        if (get_option('detit_needs_migration') !== 'yes') {
            return;
        }

        $adapter = SEO_Manager::get_adapter();
        if (!$adapter) {
            // No supported plugin active, nothing to migrate to.
            delete_option('detit_needs_migration');
            delete_option('detit_migration_offset');
            return;
        }

        $offset = (int) get_option('detit_migration_offset', 0);

        // Find products that have DetIt meta
        $query = new \WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'any',
            'posts_per_page' => self::BATCH_SIZE,
            'offset'         => $offset,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_detit_meta_title',
                    'compare' => 'EXISTS',
                ],
                [
                    'key'     => '_detit_meta_description',
                    'compare' => 'EXISTS',
                ],
            ],
        ]);

        if (empty($query->posts)) {
            // Migration complete
            delete_option('detit_needs_migration');
            delete_option('detit_migration_offset');
            return;
        }

        foreach ($query->posts as $post_id) {
            $title = (string) get_post_meta($post_id, '_detit_meta_title', true);
            $desc  = (string) get_post_meta($post_id, '_detit_meta_description', true);
            $kw    = (string) get_post_meta($post_id, '_detit_focus_keyword', true);

            $adapter->set_meta($post_id, $title, $desc, $kw);
        }

        update_option('detit_migration_offset', $offset + self::BATCH_SIZE);
    }
}
