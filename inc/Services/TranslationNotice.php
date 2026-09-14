<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

final class TranslationNotice
{
    
    private const FUNCTION = '_load_textdomain_just_in_time';

    public static function register(): void
    {
        add_filter('doing_it_wrong_trigger_error', [self::class, 'shouldTrigger'], 10, 2);
    }

    public static function shouldTrigger(mixed $trigger, string $functionName = ''): bool
    {
        if ($functionName === self::FUNCTION) {
            return false;
        }

        return (bool) $trigger;
    }
}
