<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeEndpoints;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpgradePluginMeta
{
    
    private const DOCS_PATH = '/docs/cycle-block';

    private const SUPPORT_URL = 'https://wordpress.org/support/plugin/ghostlabs-cycle-block-lite/';

    public function rowMeta(array $meta): array
    {
        $meta[] = $this->rowLink(
            UpgradeEndpoints::marketingUrl(self::DOCS_PATH),
            __('Docs', 'ghostlabs-cycle-block-lite'),
            __('Cycle Block documentation', 'ghostlabs-cycle-block-lite')
        );

        $meta[] = $this->rowLink(
            self::SUPPORT_URL,
            __('Support', 'ghostlabs-cycle-block-lite'),
            __('Cycle Block support forum', 'ghostlabs-cycle-block-lite')
        );

        return $meta;
    }

    private function rowLink(string $url, string $label, string $accessibleName): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url($url),
            esc_attr($accessibleName),
            esc_html($label)
        );
    }

    public function links(array $links): array
    {
        $links[] = $this->upgradeLink();

        return $links;
    }

    private function upgradeLink(): string
    {

        $url = UpgradeEndpoints::buyProUrl(UpsellSource::PluginsRow);

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" style="font-weight: 700;">%s<span class="screen-reader-text"> %s</span></a>',
            esc_url($url),
            esc_html__('Upgrade to Pro', 'ghostlabs-cycle-block-lite'),
            esc_html__('(opens in a new tab)', 'ghostlabs-cycle-block-lite')
        );
    }
}
