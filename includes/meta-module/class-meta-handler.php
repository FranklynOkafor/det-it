<?php
/**
 * Meta Handler
 *
 * Single entry point for reading and writing product SEO meta data.
 * Delegates reads/writes to the correct SEO plugin adapter based on
 * what SEO_Detector reports, falling back to DetIt's own meta keys
 * when no supported SEO plugin is active.
 *
 * Meta fields managed:
 *   - Meta description  (_detit_meta_description)
 *   - Meta title        (_detit_meta_title)
 *   - Focus keyword     (_detit_focus_keyword)
 *   - SEO score         (_detit_seo_score)
 *   - Last scan date    (_detit_last_scan)
 *
 * @package DetIt
 * @since   1.0.0
 */

namespace DetIt\Meta;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Handler {

    /**
     * DetIt's own meta keys (fallback + internal data).
     */
    const KEY_META_DESC    = '_detit_meta_description';
    const KEY_META_TITLE   = '_detit_meta_title';
    const KEY_FOCUS_KW     = '_detit_focus_keyword';
    const KEY_SEO_SCORE    = '_detit_seo_score';
    const KEY_LAST_SCAN    = '_detit_last_scan';

    /**
     * SEO plugin meta keys — description.
     */
    const YOAST_DESC_KEY   = '_yoast_wpseo_metadesc';
    const YOAST_TITLE_KEY  = '_yoast_wpseo_title';
    const YOAST_KW_KEY     = '_yoast_wpseo_focuskw';

    const RM_DESC_KEY      = 'rank_math_description';
    const RM_TITLE_KEY     = 'rank_math_title';
    const RM_KW_KEY        = 'rank_math_focus_keyword';

    const AIOSEO_DESC_KEY  = '_aioseo_description';
    const AIOSEO_TITLE_KEY = '_aioseo_title';
    const AIOSEO_KW_KEY    = '_aioseo_keyphrases';

    // -------------------------------------------------------------------------
    // Public API — Meta Description
    // -------------------------------------------------------------------------

    /**
     * Get the meta description for a product.
     *
     * Reads from the active SEO plugin's field first. Falls back to
     * DetIt's own meta key if nothing is found there.
     *
     * @param  int $product_id WooCommerce product post ID.
     * @return string          Meta description, or empty string.
     */
    public static function get_meta_description( int $product_id ): string {
        $plugin = SEO_Detector::detect();

        // Try the SEO plugin field first.
        $value = self::read_desc_from_plugin( $product_id, $plugin );

        // Fall back to DetIt's own storage if nothing found.
        if ( '' === $value ) {
            $value = (string) get_post_meta( $product_id, self::KEY_META_DESC, true );
        }

        return sanitize_textarea_field( $value );
    }

    /**
     * Save the meta description for a product.
     *
     * Writes to the active SEO plugin's field so it is visible in that
     * plugin's own UI. Also writes to DetIt's own key so we always have
     * a canonical copy regardless of which plugin is active later.
     *
     * @param  int    $product_id WooCommerce product post ID.
     * @param  string $value      Raw meta description text.
     * @return bool               True on success.
     */
    public static function save_meta_description( int $product_id, string $value ): bool {
        $value  = sanitize_textarea_field( $value );
        $plugin = SEO_Detector::detect();

        // Write to the SEO plugin's field.
        self::write_desc_to_plugin( $product_id, $plugin, $value );

        // Always keep DetIt's own copy up-to-date.
        return (bool) update_post_meta( $product_id, self::KEY_META_DESC, $value );
    }

    // -------------------------------------------------------------------------
    // Public API — Meta Title
    // -------------------------------------------------------------------------

    /**
     * Get the meta title for a product.
     *
     * @param  int $product_id
     * @return string
     */
    public static function get_meta_title( int $product_id ): string {
        $plugin = SEO_Detector::detect();
        $value  = self::read_title_from_plugin( $product_id, $plugin );

        if ( '' === $value ) {
            $value = (string) get_post_meta( $product_id, self::KEY_META_TITLE, true );
        }

        return sanitize_text_field( $value );
    }

    /**
     * Save the meta title for a product.
     *
     * @param  int    $product_id
     * @param  string $value
     * @return bool
     */
    public static function save_meta_title( int $product_id, string $value ): bool {
        $value  = sanitize_text_field( $value );
        $plugin = SEO_Detector::detect();

        self::write_title_to_plugin( $product_id, $plugin, $value );

        return (bool) update_post_meta( $product_id, self::KEY_META_TITLE, $value );
    }

    // -------------------------------------------------------------------------
    // Public API — Focus Keyword
    // -------------------------------------------------------------------------

    /**
     * Get the focus keyword for a product.
     *
     * @param  int $product_id
     * @return string
     */
    public static function get_focus_keyword( int $product_id ): string {
        $plugin = SEO_Detector::detect();
        $value  = self::read_kw_from_plugin( $product_id, $plugin );

        if ( '' === $value ) {
            $value = (string) get_post_meta( $product_id, self::KEY_FOCUS_KW, true );
        }

        return sanitize_text_field( $value );
    }

    /**
     * Save the focus keyword for a product.
     *
     * @param  int    $product_id
     * @param  string $value
     * @return bool
     */
    public static function save_focus_keyword( int $product_id, string $value ): bool {
        $value  = sanitize_text_field( $value );
        $plugin = SEO_Detector::detect();

        self::write_kw_to_plugin( $product_id, $plugin, $value );

        return (bool) update_post_meta( $product_id, self::KEY_FOCUS_KW, $value );
    }

    // -------------------------------------------------------------------------
    // Public API — DetIt Internal Fields
    // -------------------------------------------------------------------------

    /**
     * Get the cached SEO score for a product.
     *
     * @param  int $product_id
     * @return int Score 0–100, or 0 if never scanned.
     */
    public static function get_seo_score( int $product_id ): int {
        return (int) get_post_meta( $product_id, self::KEY_SEO_SCORE, true );
    }

    /**
     * Save the SEO score for a product.
     *
     * @param  int $product_id
     * @param  int $score       0–100.
     * @return bool
     */
    public static function save_seo_score( int $product_id, int $score ): bool {
        $score = max( 0, min( 100, $score ) );
        return (bool) update_post_meta( $product_id, self::KEY_SEO_SCORE, $score );
    }

    /**
     * Get the last scan timestamp for a product.
     *
     * @param  int $product_id
     * @return int Unix timestamp, or 0 if never scanned.
     */
    public static function get_last_scan( int $product_id ): int {
        return (int) get_post_meta( $product_id, self::KEY_LAST_SCAN, true );
    }

    /**
     * Record the current time as the last scan timestamp.
     *
     * @param  int $product_id
     * @return bool
     */
    public static function touch_last_scan( int $product_id ): bool {
        return (bool) update_post_meta( $product_id, self::KEY_LAST_SCAN, time() );
    }

    // -------------------------------------------------------------------------
    // Private — SEO plugin read helpers
    // -------------------------------------------------------------------------

    private static function read_desc_from_plugin( int $pid, string $plugin ): string {
        return match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => (string) get_post_meta( $pid, self::YOAST_DESC_KEY, true ),
            SEO_Detector::PLUGIN_RANKMATH => (string) get_post_meta( $pid, self::RM_DESC_KEY, true ),
            SEO_Detector::PLUGIN_AIOSEO   => (string) get_post_meta( $pid, self::AIOSEO_DESC_KEY, true ),
            default                        => '',
        };
    }

    private static function read_title_from_plugin( int $pid, string $plugin ): string {
        return match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => (string) get_post_meta( $pid, self::YOAST_TITLE_KEY, true ),
            SEO_Detector::PLUGIN_RANKMATH => (string) get_post_meta( $pid, self::RM_TITLE_KEY, true ),
            SEO_Detector::PLUGIN_AIOSEO   => (string) get_post_meta( $pid, self::AIOSEO_TITLE_KEY, true ),
            default                        => '',
        };
    }

    private static function read_kw_from_plugin( int $pid, string $plugin ): string {
        return match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => (string) get_post_meta( $pid, self::YOAST_KW_KEY, true ),
            SEO_Detector::PLUGIN_RANKMATH => (string) get_post_meta( $pid, self::RM_KW_KEY, true ),
            SEO_Detector::PLUGIN_AIOSEO   => self::parse_aioseo_keyphrase( $pid ),
            default                        => '',
        };
    }

    // -------------------------------------------------------------------------
    // Private — SEO plugin write helpers
    // -------------------------------------------------------------------------

    private static function write_desc_to_plugin( int $pid, string $plugin, string $value ): void {
        match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => update_post_meta( $pid, self::YOAST_DESC_KEY, $value ),
            SEO_Detector::PLUGIN_RANKMATH => update_post_meta( $pid, self::RM_DESC_KEY, $value ),
            SEO_Detector::PLUGIN_AIOSEO   => update_post_meta( $pid, self::AIOSEO_DESC_KEY, $value ),
            default                        => null,
        };
    }

    private static function write_title_to_plugin( int $pid, string $plugin, string $value ): void {
        match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => update_post_meta( $pid, self::YOAST_TITLE_KEY, $value ),
            SEO_Detector::PLUGIN_RANKMATH => update_post_meta( $pid, self::RM_TITLE_KEY, $value ),
            SEO_Detector::PLUGIN_AIOSEO   => update_post_meta( $pid, self::AIOSEO_TITLE_KEY, $value ),
            default                        => null,
        };
    }

    private static function write_kw_to_plugin( int $pid, string $plugin, string $value ): void {
        match ( $plugin ) {
            SEO_Detector::PLUGIN_YOAST    => update_post_meta( $pid, self::YOAST_KW_KEY, $value ),
            SEO_Detector::PLUGIN_RANKMATH => update_post_meta( $pid, self::RM_KW_KEY, $value ),
            // AIOSEO stores keyphrases as a JSON array — we wrap our value to match its format.
            SEO_Detector::PLUGIN_AIOSEO   => update_post_meta( $pid, self::AIOSEO_KW_KEY, wp_json_encode( [ [ 'keyphrase' => $value ] ] ) ),
            default                        => null,
        };
    }

    // -------------------------------------------------------------------------
    // Private — AIOSEO-specific helpers
    // -------------------------------------------------------------------------

    /**
     * AIOSEO stores keyphrases as a JSON array of objects.
     * Extract the first keyphrase string for compatibility.
     *
     * @param  int $pid
     * @return string
     */
    private static function parse_aioseo_keyphrase( int $pid ): string {
        $raw = get_post_meta( $pid, self::AIOSEO_KW_KEY, true );
        if ( empty( $raw ) ) {
            return '';
        }

        $data = json_decode( $raw, true );

        if ( ! is_array( $data ) || empty( $data[0]['keyphrase'] ) ) {
            return '';
        }

        return (string) $data[0]['keyphrase'];
    }
}
