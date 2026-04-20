<?php

namespace DetIt\Support;

/**
 * Class Timer
 *
 * Utility for measuring execution duration.
 */
class Timer {
    /**
     * @var array Active timers.
     */
    private static array $timers = [];

    /**
     * Start a timer.
     *
     * @param string $label The timer label.
     */
    public static function start(string $label): void {
        self::$timers[$label] = microtime(true);
    }

    /**
     * Stop a timer.
     *
     * @param string $label The timer label.
     */
    public static function stop(string $label): void {
        if (!isset(self::$timers[$label])) {
            return;
        }
        $duration = microtime(true) - self::$timers[$label];
        self::$timers[$label . '_duration'] = $duration;
        unset(self::$timers[$label]);
    }

    /**
     * Get the duration of a stopped timer as a formatted string.
     *
     * @param string $label The timer label.
     * @return string
     */
    public static function duration(string $label): string {
        $key = $label . '_duration';
        if (!isset(self::$timers[$key])) {
            // Check if it's currently running
            if (isset(self::$timers[$label])) {
                $duration = microtime(true) - self::$timers[$label];
                return round($duration, 3) . 's';
            }
            return '0.000s';
        }

        return round(self::$timers[$key], 3) . 's';
    }
}
