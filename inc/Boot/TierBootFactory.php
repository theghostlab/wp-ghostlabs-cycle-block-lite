<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

final class TierBootFactory
{
    
    public static function named(string $class): TierBootInterface
    {
        if ($class !== '' && class_exists($class) && is_subclass_of($class, TierBootInterface::class)) {
            
            return new $class();
        }

        return new MissingTierBoot();
    }

    public static function current(): TierBootInterface
    {
        return self::named(defined('GHOSTLABS_CYCLE_BLOCK_BOOT') ? (string) GHOSTLABS_CYCLE_BLOCK_BOOT : '');
    }
}
