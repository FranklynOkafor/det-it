<?php

namespace DetIt\Support;

/**
 * Class Correlation
 *
 * Manages unique correlation IDs to trace a single scan run across multiple logs.
 */
class Correlation {
    /**
     * @var string|null Active correlation ID.
     */
    private static ?string $active_id = null;

    /**
     * Start a new run and generate a correlation ID.
     */
    public static function start_run(): void {
        self::$active_id = 'detit_scan_' . time() . '_' . substr(md5(uniqid('', true)), 0, 5);
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
     */
    public static function end_run(): void {
        self::$active_id = null;
    }
}
