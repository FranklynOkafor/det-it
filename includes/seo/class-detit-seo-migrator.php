<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class SEO_Migrator
{
    const BATCH_SIZE = 15;

    public static function boot(): void
    {
        add_action('activated_plugin', [self::class, 'handle_plugin_change']);
        add_action('deactivated_plugin', [self::class, 'handle_plugin_change']);
        
        // Lightweight check on admin_init
        add_action('admin_init', [self::class, 'maybe_schedule_migration']);
        
        // Background process hook
        add_action('detit_seo_migration_batch', [self::class, 'process_migration_batch']);
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
     * Checks if a migration is needed and schedules it via Action Scheduler,
     * or runs a synchronous fallback batch if Action Scheduler is unavailable.
     * Hooked on admin_init.
     */
    public static function maybe_schedule_migration(): void
    {
        if (get_option('detit_needs_migration') !== 'yes') {
            return;
        }

        // Use WooCommerce Action Scheduler if available
        if (function_exists('as_next_scheduled_action') && function_exists('as_enqueue_async_action')) {
            if (!as_next_scheduled_action('detit_seo_migration_batch')) {
                as_enqueue_async_action('detit_seo_migration_batch');
            }
        } else {
            // Fallback: Process synchronously using a transient lock to avoid concurrent requests freezing the admin
            if (false === get_transient('detit_seo_migration_lock')) {
                set_transient('detit_seo_migration_lock', true, 60);
                self::process_migration_batch();
            }
        }
    }

    /**
     * Processes a batch of products to sync their DetIt meta into the newly active SEO plugin.
     * Hooked on detit_seo_migration_batch for async processing, or called directly as fallback.
     */
    public static function process_migration_batch(): void
    {
        if (get_option('detit_needs_migration') !== 'yes') {
            delete_transient('detit_seo_migration_lock');
            return;
        }

        $adapter = SEO_Manager::get_adapter();
        if (!$adapter) {
            // No supported plugin active, nothing to migrate to.
            delete_option('detit_needs_migration');
            delete_option('detit_migration_offset');
            delete_transient('detit_seo_migration_lock');
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
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Acceptable for background batch migration.
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
            delete_transient('detit_seo_migration_lock');
            return;
        }

        foreach ($query->posts as $post_id) {
            $title = (string) get_post_meta($post_id, '_detit_meta_title', true);
            $desc  = (string) get_post_meta($post_id, '_detit_meta_description', true);
            $kw    = (string) get_post_meta($post_id, '_detit_focus_keyword', true);

            $adapter->set_meta($post_id, $title, $desc, $kw);
        }

        update_option('detit_migration_offset', $offset + self::BATCH_SIZE);

        // Enqueue next batch if using Action Scheduler
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('detit_seo_migration_batch');
        } else {
            // Release lock for fallback
            delete_transient('detit_seo_migration_lock');
        }
    }
}
