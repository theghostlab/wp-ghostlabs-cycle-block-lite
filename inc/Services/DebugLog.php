<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

final class DebugLog
{
    
    public const FILTER = 'theghostlab_cycle_debug_logging';

    private const PREFIX = '[Cycle Block] ';

    public static function write(string $message): void
    {
        if (! self::enabled()) {
            return;
        }

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- The plugin's single diagnostic sink, gated by self::enabled() above. See the class docblock.
        error_log(self::PREFIX . $message);
    }

    public static function enabled(): bool
    {
        $default = defined('WP_DEBUG') && WP_DEBUG;

        return (bool) apply_filters('theghostlab_cycle_debug_logging', $default);
    }
}
