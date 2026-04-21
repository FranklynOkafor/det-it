<?php

namespace DetIt\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Correlation
 *
 * Manages unique correlation IDs to trace a single scan run across multiple logs.
 */
class Correlation {

	/**
	 * Active correlation ID.
	 *
	 * @var string|null
	 */
	private static ?string $active_id = null;

	/**
	 * Start a new scan run.
	 *
	 * @return void
	 */
	public static function start_run(): void {
		$random_suffix = base_convert( (string) wp_rand( 604661, 60466175 ), 10, 36 );
		$random_suffix = strtolower( substr( str_pad( $random_suffix, 5, '0', STR_PAD_LEFT ), 0, 5 ) );

		self::$active_id = 'detit_scan_' . time() . '_' . $random_suffix;
	}

	/**
	 * Get the active correlation ID.
	 *
	 * @return string|null
	 */
	public static function get_id(): ?string {
		return self::$active_id;
	}

	/**
	 * End the current run and clear the correlation ID.
	 *
	 * @return void
	 */
	public static function end_run(): void {
		self::$active_id = null;
	}
}
