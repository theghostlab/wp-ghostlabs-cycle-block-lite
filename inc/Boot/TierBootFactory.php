<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Resolves the edition's tier boot object by name.
 *
 * Edition `x` -> `Boot\XBoot`. Each tier folder ships its own,
 * so the class is present exactly when its tier was overlaid — which makes the
 * lookup itself the check. No `class_exists()` at any call site, and no
 * filesystem walk: one autoload, resolved through the same two-root PSR-4 map
 * that finds everything else.
 */
final class TierBootFactory
{
    public static function for(string $edition): TierBootInterface
    {
        $class = __NAMESPACE__ . '\\' . ucfirst(strtolower($edition)) . 'Boot';

        if (class_exists($class) && is_subclass_of($class, TierBootInterface::class)) {
            /** @var TierBootInterface */
            return new $class();
        }

        return new MissingTierBoot();
    }

    /**
     * The edition this build was assembled as.
     *
     * Read from the constant the entry header defines rather than passed in, so
     * Kernel does not have to know the constant's name either.
     */
    public static function current(): TierBootInterface
    {
        return self::for(
            defined('GHOSTLABS_CYCLE_BLOCK_EDITION') ? (string) GHOSTLABS_CYCLE_BLOCK_EDITION : ''
        );
    }
}
