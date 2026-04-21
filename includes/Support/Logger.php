<?php

namespace DetIt\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Logger
 *
 * Centralized logging interface for the entire plugin.
 */
class Logger {

	/**
	 * Logger source name.
	 *
	 * @var string
	 */
	private const SOURCE = 'detit';

	/**
	 * Log level priorities.
	 *
	 * @var array<string, int>
	 */
	private static array $levels = array(
		'error'   => 0,
		'warning' => 1,
		'info'    => 2,
		'debug'   => 3,
	);

	/**
	 * Cached debug mode value.
	 *
	 * @var bool|null
	 */
	private static ?bool $debug_mode = null;

	/**
	 * Cached log level threshold.
	 *
	 * @var string|null
	 */
	private static ?string $log_level = null;

	/**
	 * Log an error message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Structured context.
	 * @return void
	 */
	public static function error( string $message, array $context = array() ): void {
		if ( ! self::should_log( 'error' ) ) {
			return;
		}

		self::write_log( 'error', $message, $context );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Structured context.
	 * @return void
	 */
	public static function warning( string $message, array $context = array() ): void {
		if ( ! self::should_log( 'warning' ) ) {
			return;
		}

		self::write_log( 'warning', $message, $context );
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Structured context.
	 * @return void
	 */
	public static function info( string $message, array $context = array() ): void {
		if ( ! self::should_log( 'info' ) ) {
			return;
		}

		self::write_log( 'info', $message, $context );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Structured context.
	 * @return void
	 */
	public static function debug( string $message, array $context = array() ): void {
		if ( ! self::should_log( 'debug' ) ) {
			return;
		}

		self::write_log( 'debug', $message, $context );
	}

	/**
	 * Determine whether the requested level should be logged.
	 *
	 * @param string $level Requested log level.
	 * @return bool
	 */
	private static function should_log( string $level ): bool {
		if ( ! isset( self::$levels[ $level ] ) ) {
			return false;
		}

		if ( null === self::$debug_mode ) {
			self::$debug_mode = (bool) get_option( 'detit_debug_mode', false );
		}

		if ( ! self::$debug_mode ) {
			return false;
		}

		if ( null === self::$log_level ) {
			$stored_level = (string) get_option( 'detit_log_level', 'error' );
			self::$log_level = isset( self::$levels[ $stored_level ] ) ? $stored_level : 'error';
		}

		return self::$levels[ $level ] <= self::$levels[ self::$log_level ];
	}

	/**
	 * Sanitize logging context.
	 *
	 * Removes sensitive keys and limits user-provided context to 10 keys.
	 *
	 * @param array $context Raw log context.
	 * @return array
	 */
	private static function sanitize_context( array $context ): array {
		$sanitized = array();

		foreach ( $context as $key => $value ) {
			$normalized_key = is_string( $key ) ? $key : (string) $key;

			if ( self::is_sensitive_key( $normalized_key ) ) {
				continue;
			}

			if ( 10 <= count( $sanitized ) ) {
				break;
			}

			$sanitized[ $normalized_key ] = self::sanitize_value( $normalized_key, $value );
		}

		return $sanitized;
	}

	/**
	 * Enrich a log context with environment metadata.
	 *
	 * @param array $context Sanitized log context.
	 * @return array
	 */
	private static function enrich_context( array $context ): array {
		$enriched_context = array(
			'plugin_version' => defined( 'DETIT_VERSION' ) ? DETIT_VERSION : 'unknown',
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => phpversion(),
			'environment'    => wp_get_environment_type(),
			'request_type'   => self::get_request_type(),
			'memory_usage'   => memory_get_usage( true ),
		);

		$run_id = Correlation::get_id();

		if ( ! empty( $run_id ) && ! isset( $context['run_id'] ) ) {
			$enriched_context['run_id'] = $run_id;
		}

		return array_merge( $enriched_context, $context );
	}

	/**
	 * Write a log entry to the available logger.
	 *
	 * @param string $level Log level.
	 * @param string $message Log message.
	 * @param array  $context Raw log context.
	 * @return void
	 */
	private static function write_log( string $level, string $message, array $context ): void {
		$context           = self::enrich_context( self::sanitize_context( $context ) );
		$logger_context    = array_merge( $context, array( 'source' => self::SOURCE ) );
		$encoded_context   = wp_json_encode( $context );
		$fallback_message  = sprintf( '[DetIt][%s] %s %s', $level, $message, $encoded_context );

		if ( function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();

			if ( is_object( $logger ) && method_exists( $logger, $level ) ) {
				$logger->{$level}( $message, $logger_context );
				return;
			}
		}

		error_log( $fallback_message );
	}

	/**
	 * Determine whether a context key is sensitive.
	 *
	 * @param string $key Context key.
	 * @return bool
	 */
	private static function is_sensitive_key( string $key ): bool {
		$sensitive_fragments = array(
			'password',
			'token',
			'secret',
			'api_key',
			'authorization',
			'nonce',
		);

		foreach ( $sensitive_fragments as $fragment ) {
			if ( false !== stripos( $key, $fragment ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitize a context value for safe logging.
	 *
	 * @param string $key Context key.
	 * @param mixed  $value Context value.
	 * @return mixed
	 */
	private static function sanitize_value( string $key, $value ) {
		if ( is_scalar( $value ) || null === $value ) {
			if ( is_string( $value ) ) {
				if ( self::contains_restricted_data( $key ) ) {
					return '[omitted]';
				}

				if ( 200 < strlen( $value ) ) {
					return '[string_length:' . strlen( $value ) . ']';
				}
			}

			return $value;
		}

		if ( is_array( $value ) ) {
			return array(
				'count' => count( $value ),
			);
		}

		if ( is_object( $value ) ) {
			if ( method_exists( $value, 'get_id' ) ) {
				return (int) $value->get_id();
			}

			if ( $value instanceof \WP_Post ) {
				return (int) $value->ID;
			}

			return get_class( $value );
		}

		return gettype( $value );
	}

	/**
	 * Determine whether a context key may hold restricted content.
	 *
	 * @param string $key Context key.
	 * @return bool
	 */
	private static function contains_restricted_data( string $key ): bool {
		$restricted_fragments = array(
			'content',
			'description',
			'customer',
			'email',
			'address',
			'phone',
		);

		foreach ( $restricted_fragments as $fragment ) {
			if ( false !== stripos( $key, $fragment ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect the current request type.
	 *
	 * @return string
	 */
	private static function get_request_type(): string {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return 'cli';
		}

		if ( wp_doing_cron() ) {
			return 'cron';
		}

		if ( wp_doing_ajax() ) {
			return 'ajax';
		}

		if ( is_admin() ) {
			return 'admin';
		}

		return 'frontend';
	}
}
