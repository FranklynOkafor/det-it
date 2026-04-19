<?php

namespace DetIt\Security;

/**
 * Class Permission
 *
 * Provide reusable capability checks for controllers and handlers.
 */
class Permission {
    /**
     * Check if the current user has permission for a specific operation.
     *
     * @param string $operation The operation constant from Capability class.
     * @return bool True if the user has permission, false otherwise.
     */
    public static function check(string $operation): bool {
        return Capability::currentUserCan($operation);
    }

    /**
     * Enforce permission for a specific operation, dying if it fails.
     *
     * @param string $operation The operation constant from Capability class.
     * @return void
     */
    public static function enforce(string $operation): void {
        if (!self::check($operation)) {
            wp_die('Unauthorized action');
        }
    }

    /**
     * Convenience method to enforce the MANAGE_SETTINGS permission.
     *
     * @return void
     */
    public static function enforceManageSettings(): void {
        self::enforce(Capability::MANAGE_SETTINGS);
    }
}
