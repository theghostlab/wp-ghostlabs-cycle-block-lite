<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpgradeEndpoints
{
    
    private const MARKETING_BASE = 'https://theghostlab.io';

    private const MARKETING_PATH = '/cycle-block';

    public static function marketingUrl(string $path): string
    {
        return self::MARKETING_BASE . $path;
    }

    public static function buyProUrl(UpsellSource $source): string
    {

        return self::MARKETING_BASE . self::MARKETING_PATH . '?from=' . $source->value;
    }

    public static function buyProUrls(array $sources): array
    {
        $urls = [];

        foreach ($sources as $source) {
            $urls[$source->value] = self::buyProUrl($source);
        }

        return $urls;
    }
}
