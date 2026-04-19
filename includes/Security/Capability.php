<?php

namespace DetIt\Security;

/**
 * Class Capability
 *
 * Centralized capability definitions used by the plugin.
 */
class Capability {
    public const RUN_AUDIT = 'run_audit';
    public const APPLY_FIXES = 'apply_fixes';
    public const VIEW_REPORTS = 'view_reports';
    public const MANAGE_SETTINGS = 'manage_settings';

    /**
     * Get the mapped WordPress capability for a given operation.
     *
     * @param string $operation The plugin operation.
     * @return string The mapped WordPress capability.
     */
    public static function getCapability(string $operation): string {
        // Fallback capability if WooCommerce is not active
        $defaultCapability = 'manage_options';

        // Check if WooCommerce is active (class exists is a safe check)
        if (class_exists('WooCommerce')) {
            $defaultCapability = 'manage_woocommerce';
        }

        // Currently all operations map to the same base capability
        // In the future, this mapping can be expanded to allow more granular control
        $capabilities = [
            self::RUN_AUDIT       => $defaultCapability,
            self::APPLY_FIXES     => $defaultCapability,
            self::VIEW_REPORTS    => $defaultCapability,
            self::MANAGE_SETTINGS => $defaultCapability,
        ];

        return $capabilities[$operation] ?? $defaultCapability;
    }

    /**
     * Check if the current user has permission to perform a specific operation.
     *
     * @param string $operation The plugin operation.
     * @return bool True if the user can perform the operation, false otherwise.
     */
    public static function currentUserCan(string $operation): bool {
        $capability = self::getCapability($operation);
        return current_user_can($capability);
    }
}
