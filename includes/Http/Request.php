<?php

namespace DetIt\Http;

/**
 * Class Request
 *
 * Provide safe, sanitized access to user input, preventing direct
 * usage of superglobals like $_POST, $_GET, or $_REQUEST.
 */
class Request {
    /**
     * Get a raw value from the request, checking POST first, then GET.
     *
     * @param string $key The parameter key.
     * @return mixed|null The raw value, or null if not found.
     */
    private static function getRaw(string $key) {
        if (isset($_POST[$key])) {
            return wp_unslash($_POST[$key]);
        }
        
        if (isset($_GET[$key])) {
            return wp_unslash($_GET[$key]);
        }

        return null;
    }

    /**
     * Retrieve and sanitize a text input.
     *
     * @param string $key     The parameter key.
     * @param string $default The default value if not found.
     * @return string The sanitized text string.
     */
    public static function text(string $key, string $default = ''): string {
        $value = self::getRaw($key);
        return $value !== null ? sanitize_text_field($value) : $default;
    }

    /**
     * Retrieve and sanitize a textarea input.
     *
     * @param string $key     The parameter key.
     * @param string $default The default value if not found.
     * @return string The sanitized textarea string.
     */
    public static function textarea(string $key, string $default = ''): string {
        $value = self::getRaw($key);
        return $value !== null ? sanitize_textarea_field($value) : $default;
    }

    /**
     * Retrieve and sanitize an integer input.
     *
     * @param string $key     The parameter key.
     * @param int    $default The default value if not found.
     * @return int The absolute integer value.
     */
    public static function int(string $key, int $default = 0): int {
        $value = self::getRaw($key);
        return $value !== null ? absint($value) : $default;
    }

    /**
     * Retrieve and sanitize a boolean input.
     *
     * @param string $key     The parameter key.
     * @param bool   $default The default value if not found.
     * @return bool True if the input evaluates to true, false otherwise.
     */
    public static function bool(string $key, bool $default = false): bool {
        $value = self::getRaw($key);
        if ($value === null) {
            return $default;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Retrieve and sanitize a URL input.
     *
     * @param string $key     The parameter key.
     * @param string $default The default value if not found.
     * @return string The sanitized URL string.
     */
    public static function url(string $key, string $default = ''): string {
        $value = self::getRaw($key);
        return $value !== null ? esc_url_raw($value) : $default;
    }

    /**
     * Retrieve and sanitize an array of text values.
     *
     * @param string $key The parameter key.
     * @return array The array of sanitized text strings.
     */
    public static function textArray(string $key): array {
        $value = self::getRaw($key);
        
        if (!is_array($value)) {
            return [];
        }

        return array_map('sanitize_text_field', $value);
    }
}
