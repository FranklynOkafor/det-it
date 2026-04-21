<?php

namespace DetIt\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Timer
 *
 * Utility for measuring execution duration.
 */
class Timer {

	/**
	 * Active timer start times.
	 *
	 * @var array<string, float>
	 */
	private static array $start_times = array();

	/**
	 * Completed timer durations.
	 *
	 * @var array<string, float>
	 */
	private static array $durations = array();

	/**
	 * Start a timer.
	 *
	 * @param string $label Timer label.
	 * @return void
	 */
	public static function start( string $label ): void {
		self::$start_times[ $label ] = microtime( true );
		unset( self::$durations[ $label ] );
	}

	/**
	 * Stop a timer.
	 *
	 * @param string $label Timer label.
	 * @return void
	 */
	public static function stop( string $label ): void {
		if ( ! isset( self::$start_times[ $label ] ) ) {
			return;
		}

		self::$durations[ $label ] = microtime( true ) - self::$start_times[ $label ];
		unset( self::$start_times[ $label ] );
	}

	/**
	 * Get a timer duration as a formatted string.
	 *
	 * @param string $label Timer label.
	 * @return string
	 */
	public static function duration( string $label ): string {
		if ( isset( self::$durations[ $label ] ) ) {
			return self::format_duration( self::$durations[ $label ] );
		}

		if ( isset( self::$start_times[ $label ] ) ) {
			return self::format_duration( microtime( true ) - self::$start_times[ $label ] );
		}

		return self::format_duration( 0.0 );
	}

	/**
	 * Format a duration for structured logs.
	 *
	 * @param float $seconds Duration in seconds.
	 * @return string
	 */
	private static function format_duration( float $seconds ): string {
		$formatted = number_format( $seconds, 3, '.', '' );
		$formatted = rtrim( rtrim( $formatted, '0' ), '.' );

		if ( '' === $formatted ) {
			$formatted = '0';
		}

		return $formatted . 's';
	}
}
