<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

final class TierBootFactory
{
    public static function for(string $edition): TierBootInterface
    {
        $class = __NAMESPACE__ . '\\' . ucfirst(strtolower($edition)) . 'Boot';

        if (class_exists($class) && is_subclass_of($class, TierBootInterface::class)) {
            
            return new $class();
        }

        return new MissingTierBoot();
    }

    public static function current(): TierBootInterface
    {
        return self::for(
            defined('GHOSTLABS_CYCLE_BLOCK_EDITION') ? (string) GHOSTLABS_CYCLE_BLOCK_EDITION : ''
        );
    }
}
