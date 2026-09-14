<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

final class SourceRoots
{
    
    public static function all(): array
    {
        $plugin = rtrim(GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH, '/\\');
        $roots  = [$plugin];
        $shared = dirname($plugin) . '/shared';

        if (is_dir($shared)) {
            $roots[] = $shared;
        }

        return $roots;
    }

    public static function asset(string $relative): ?array
    {
        $relative = ltrim($relative, '/');
        $plugin   = rtrim(GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH, '/\\');
        $url      = rtrim(GHOSTLABS_CYCLE_BLOCK_PLUGIN_URL, '/');

        if (is_file($plugin . '/' . $relative)) {
            return [
                'path' => $plugin . '/' . $relative,
                'url'  => $url . '/' . $relative,
            ];
        }

        $shared = dirname($plugin) . '/shared/' . $relative;

        if (is_file($shared)) {
            return [
                'path' => $shared,

                'url'  => dirname($url) . '/shared/' . $relative,
            ];
        }

        return null;
    }

    public static function dirs(string $sub): array
    {
        $sub   = trim($sub, '/');
        $found = [];

        foreach (self::all() as $root) {
            $dir = $root . '/' . $sub;

            if (is_dir($dir)) {
                $found[] = $dir;
            }
        }

        return $found;
    }
}
