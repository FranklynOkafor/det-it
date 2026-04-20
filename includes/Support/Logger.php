<?php

namespace DetIt\Support;

/**
 * Class Logger
 *
 * Centralized logging interface for the entire plugin.
 */
class Logger
{

    /**
     * Log an error message.
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void
    {
        if (self::should_log('error')) {
            self::write_log('error', $message, $context);
        }
    }

    /**
     * Log a warning message.
     *
     * @param string $message
     * @param array $context
     */
    public static function warning(string $message, array $context = []): void
    {
        if (self::should_log('warning')) {
            self::write_log('warning', $message, $context);
        }
    }

    /**
     * Log an info message.
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void
    {
        if (self::should_log('info')) {
            self::write_log('info', $message, $context);
        }
    }

    /**
     * Log a debug message.
     *
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void
    {
        if (self::should_log('debug')) {
            self::write_log('debug', $message, $context);
        }
    }

    /**
     * Check if the message should be logged based on settings.
     *
     * @param string $level The log level being attempted.
     * @return bool
     */
    private static function should_log(string $level): bool
    {
        $debug_mode = get_option('detit_debug_mode', false);
        if (!$debug_mode) {
            return false;
        }

        $threshold = get_option('detit_log_level', 'error');

        $levels = [
            'error'   => 1,
            'warning' => 2,
            'info'    => 3,
            'debug'   => 4,
        ];

        $threshold_value = $levels[$threshold] ?? 1;
        $level_value = $levels[$level] ?? 1;

        return $level_value <= $threshold_value;
    }

    /**
     * Sanitize context by removing sensitive fields and truncating size.
     *
     * @param array $context
     * @return array
     */
    private static function sanitize_context(array $context): array
    {
        $sensitive_keys = ['password', 'token', 'secret', 'api_key', 'authorization', 'nonce'];

        $sanitized = [];
        $count = 0;

        foreach ($context as $key => $value) {
            if ($count >= 10) {
                $sanitized['__truncated'] = true;
                break;
            }

            $is_sensitive = false;
            foreach ($sensitive_keys as $sensitive_key) {
                if (stripos((string) $key, $sensitive_key) !== false) {
                    $is_sensitive = true;
                    break;
                }
            }

            if (!$is_sensitive) {
                $sanitized[$key] = is_scalar($value) || is_null($value) ? $value : wp_json_encode($value);
                $count++;
            } else {
                $sanitized[$key] = '[REDACTED]';
                $count++;
            }
        }

        return $sanitized;
    }

    /**
     * Automatically attach system metadata.
     *
     * @param array $context
     * @return array
     */
    private static function enrich_context(array $context): array
    {
        $request_type = 'frontend';

        if (defined('WP_CLI') && constant('WP_CLI')) {
            $request_type = 'cli';
        } elseif (wp_doing_cron()) {
            $request_type = 'cron';
        } elseif (wp_doing_ajax()) {
            $request_type = 'ajax';
        } elseif (is_admin()) {
            $request_type = 'admin';
        }

        $enrichment = [
            'plugin_version' => defined('DETIT_VERSION') ? constant('DETIT_VERSION') : 'unknown',
            'wp_version'     => get_bloginfo('version'),
            'php_version'    => phpversion(),
            'environment'    => wp_get_environment_type(),
            'request_type'   => $request_type,
            'memory_usage'   => size_format(memory_get_usage(true)),
        ];

        $correlation_id = Correlation::get_id();
        if ($correlation_id) {
            $enrichment['run_id'] = $correlation_id;
        }

        return array_merge($enrichment, $context);
    }

    /**
     * Write the log to WooCommerce logger or fallback.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private static function write_log(string $level, string $message, array $context): void
    {
        $sanitized_context = self::sanitize_context($context);
        $enriched_context = self::enrich_context($sanitized_context);
        $enriched_context['source'] = 'detit';

        if (class_exists('WooCommerce') && function_exists('wc_get_logger')) {
            $logger = wc_get_logger();
            if ($logger) {
                $logger->log($level, $message, $enriched_context);
                return;
            }
        }

        // Fallback to error_log
        $log_entry = sprintf(
            "[DetIt][%s] %s | Context: %s",
            strtoupper($level),
            $message,
            wp_json_encode($enriched_context)
        );

        error_log($log_entry);
    }
}
