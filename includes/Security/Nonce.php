<?php

namespace DetIt\Security;

/**
 * Class Nonce
 *
 * Standardize nonce creation and verification across forms and AJAX.
 */
class Nonce {
    /**
     * Create a nonce for a specific action.
     *
     * @param string $action The action name.
     * @return string The generated nonce.
     */
    public static function create(string $action): string {
        return wp_create_nonce($action);
    }

    /**
     * Generate a hidden form field for a nonce.
     *
     * @param string $action The action name.
     * @param string $name   The name of the nonce field.
     * @return string The HTML for the hidden nonce field.
     */
    public static function field(string $action, string $name = '_wpnonce'): string {
        return wp_nonce_field($action, $name, true, false);
    }

    /**
     * Verify a nonce for a specific action.
     *
     * @param string $nonce  The nonce to verify.
     * @param string $action The action name.
     * @return bool True if the nonce is valid, false otherwise.
     */
    public static function verify(string $nonce, string $action): bool {
        return wp_verify_nonce($nonce, $action) !== false;
    }

    /**
     * Verify an AJAX request's nonce.
     *
     * @param string $action The action name.
     * @param string $field  The name of the nonce field in the request.
     * @return bool True if the nonce is valid, false otherwise.
     */
    public static function verifyAjax(string $action, string $field = '_wpnonce'): bool {
        return check_ajax_referer($action, $field, false) !== false;
    }

    /**
     * Strictly enforce a nonce verification, dying if it fails.
     *
     * @param string $nonce  The nonce to verify.
     * @param string $action The action name.
     * @return void
     */
    public static function enforce(string $nonce, string $action): void {
        if (!self::verify($nonce, $action)) {
            wp_die('Invalid security token');
        }
    }
}
