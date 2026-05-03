<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

class SEO_Manager
{
    const PLUGIN_YOAST   = 'yoast';
    const PLUGIN_RANKMATH = 'rankmath';
    const PLUGIN_AIOSEO  = 'aioseo';
    const PLUGIN_NONE    = 'none';

    private static ?string $detected = null;

    /**
     * Detects which SEO plugin is active.
     */
    public static function detect(): string
    {
        if (null !== self::$detected) {
            return self::$detected;
        }

        if (self::is_yoast_active()) {
            self::$detected = self::PLUGIN_YOAST;
        } elseif (self::is_rankmath_active()) {
            self::$detected = self::PLUGIN_RANKMATH;
        } elseif (self::is_aioseo_active()) {
            self::$detected = self::PLUGIN_AIOSEO;
        } else {
            self::$detected = self::PLUGIN_NONE;
        }

        return self::$detected;
    }

    /**
     * Returns true when any supported SEO plugin is active.
     */
    public static function has_seo_plugin(): bool
    {
        return self::detect() !== self::PLUGIN_NONE;
    }

    /**
     * Returns an instance of the adapter for the currently active SEO plugin.
     * Returns null if no supported plugin is active.
     */
    public static function get_adapter(): ?SEO_Adapter_Interface
    {
        $plugin = self::detect();

        return match ($plugin) {
            self::PLUGIN_YOAST    => new detit_Yoast_Adapter(),
            self::PLUGIN_RANKMATH => new detit_RankMath_Adapter(),
            self::PLUGIN_AIOSEO   => new detit_AIOSEO_Adapter(),
            default                => null,
        };
    }

    /**
     * Reset the cached result.
     */
    public static function reset_cache(): void
    {
        self::$detected = null;
    }

    private static function is_yoast_active(): bool
    {
        return defined('WPSEO_VERSION') || class_exists('WPSEO_Frontend');
    }

    private static function is_rankmath_active(): bool
    {
        return defined('RANK_MATH_VERSION') || class_exists('RankMath');
    }

    private static function is_aioseo_active(): bool
    {
        return defined('AIOSEO_VERSION') || class_exists('AIOSEO\Plugin\AIOSEO');
    }
}
