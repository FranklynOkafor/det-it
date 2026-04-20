<?php
/**
 * SEO Plugin Detector
 *
 * Detects which SEO plugin (if any) is active on the site.
 * Used by the Meta Handler to decide where to read/write meta fields.
 *
 * Compatibility priority:
 *   1. Yoast SEO
 *   2. Rank Math
 *   3. All in One SEO
 *   4. DetIt fallback
 *
 * @package DetIt
 * @since   1.0.0
 */

namespace DetIt\Meta;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SEO_Detector {

    /**
     * Plugin identifiers.
     */
    const PLUGIN_YOAST   = 'yoast';
    const PLUGIN_RANKMATH = 'rankmath';
    const PLUGIN_AIOSEO  = 'aioseo';
    const PLUGIN_NONE    = 'none';

    /**
     * Cached detection result.
     *
     * @var string|null
     */
    private static $detected = null;

    /**
     * Detect which SEO plugin is active.
     *
     * Returns the first match in priority order. Result is cached for the
     * lifetime of the request so detection only runs once per page load.
     *
     * @return string One of the PLUGIN_* constants.
     */
    public static function detect(): string {
        if ( null !== self::$detected ) {
            return self::$detected;
        }

        if ( self::is_yoast_active() ) {
            self::$detected = self::PLUGIN_YOAST;
        } elseif ( self::is_rankmath_active() ) {
            self::$detected = self::PLUGIN_RANKMATH;
        } elseif ( self::is_aioseo_active() ) {
            self::$detected = self::PLUGIN_AIOSEO;
        } else {
            self::$detected = self::PLUGIN_NONE;
        }

        /**
         * Filter the detected SEO plugin.
         *
         * Useful for unit testing or overriding detection.
         *
         * @param string $detected The detected plugin identifier (PLUGIN_* constant).
         */
        self::$detected = apply_filters( 'detit_seo_plugin_detected', self::$detected );

        return self::$detected;
    }

    /**
     * Returns true when any supported SEO plugin is active.
     *
     * @return bool
     */
    public static function has_seo_plugin(): bool {
        return self::detect() !== self::PLUGIN_NONE;
    }

    /**
     * Reset the cached result.
     *
     * Useful in unit tests or after plugin activation/deactivation.
     *
     * @return void
     */
    public static function reset_cache(): void {
        self::$detected = null;
    }

    // -------------------------------------------------------------------------
    // Private detection helpers
    // -------------------------------------------------------------------------

    /**
     * Check for Yoast SEO.
     *
     * We check for the class rather than the plugin file so the detection
     * works whether Yoast is loaded as a plugin or as a composer dependency.
     *
     * @return bool
     */
    private static function is_yoast_active(): bool {
        return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' );
    }

    /**
     * Check for Rank Math.
     *
     * @return bool
     */
    private static function is_rankmath_active(): bool {
        return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
    }

    /**
     * Check for All in One SEO.
     *
     * @return bool
     */
    private static function is_aioseo_active(): bool {
        return defined( 'AIOSEO_VERSION' ) || class_exists( 'AIOSEO\Plugin\AIOSEO' );
    }
}
