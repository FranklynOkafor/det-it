<?php

namespace DetIt\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LoggingSettings
 *
 * Registers plugin settings for the logging subsystem.
 */
class LoggingSettings {

	/**
	 * Register settings using the WordPress Settings API.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_init', array( self::class, 'init_settings' ) );
	}

	/**
	 * Initialize logging settings.
	 *
	 * @return void
	 */
	public static function init_settings(): void {
		register_setting(
			'detit_settings',
			'detit_debug_mode',
			array(
				'type'              => 'boolean',
				'description'       => 'Enable or disable debug logging.',
				'sanitize_callback' => array( self::class, 'sanitize_debug_mode' ),
				'default'           => false,
			)
		);

		register_setting(
			'detit_settings',
			'detit_log_level',
			array(
				'type'              => 'string',
				'description'       => 'Set the verbosity level of the logger.',
				'sanitize_callback' => array( self::class, 'sanitize_log_level' ),
				'default'           => 'error',
			)
		);
	}

	/**
	 * Sanitize debug mode input.
	 *
	 * @param mixed $value Debug mode input value.
	 * @return bool
	 */
	public static function sanitize_debug_mode( $value ): bool {
		return (bool) rest_sanitize_boolean( $value );
	}

	/**
	 * Sanitize log level input.
	 *
	 * @param mixed $level Input log level.
	 * @return string
	 */
	public static function sanitize_log_level( $level ): string {
		$allowed_levels = array( 'error', 'warning', 'info', 'debug' );
		$level          = strtolower( sanitize_text_field( (string) $level ) );

		if ( in_array( $level, $allowed_levels, true ) ) {
			return $level;
		}

		return 'error';
	}
}
