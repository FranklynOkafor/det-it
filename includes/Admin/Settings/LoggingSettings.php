<?php

namespace DetIt\Admin\Settings;

/**
 * Class LoggingSettings
 *
 * Registers plugin settings for the logging subsystem.
 */
class LoggingSettings {

    /**
     * Register settings using the WordPress Settings API.
     */
    public static function register(): void {
        add_action('admin_init', [self::class, 'init_settings']);
    }

    /**
     * Initialize settings.
     */
    public static function init_settings(): void {
        register_setting(
            'detit_settings',
            'detit_debug_mode',
            [
                'type' => 'boolean',
                'description' => 'Enable or disable debug logging.',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false,
            ]
        );

        register_setting(
            'detit_settings',
            'detit_log_level',
            [
                'type' => 'string',
                'description' => 'Set the verbosity level of the logger.',
                'sanitize_callback' => [self::class, 'sanitize_log_level'],
                'default' => 'error',
            ]
        );
    }

    /**
     * Sanitize log level input.
     *
     * @param mixed $level The inputted level.
     * @return string The sanitized level.
     */
    public static function sanitize_log_level($level): string {
        $allowed = ['error', 'warning', 'info', 'debug'];
        $level = sanitize_text_field($level);
        return in_array($level, $allowed, true) ? $level : 'error';
    }
}
