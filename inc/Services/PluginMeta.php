<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

class PluginMeta {

    public function actionSettings($links_array, $plugin_file_name, $plugin_data, $context) {

        $needsAttention = (bool) apply_filters('theghostlab_cycle_settings_link_needs_attention', false);

        $settings = $this->settingsLink($needsAttention);

        array_unshift( $links_array, $settings );

        return apply_filters('theghostlab_cycle_plugin_action_links', $links_array, $context);
    }

    public function rowMeta($meta, $plugin_file) {

        if ( $plugin_file !== GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG ) {
            return $meta;
        }

        return apply_filters('theghostlab_cycle_plugin_row_meta', $meta);
    }

    public function settingsLink(bool $needsAttention = false): string
    {
        $path  = sprintf("options-general.php?page=%s&tab=home", PluginConfig::$settingsMenuSlug);
        $url   = admin_url($path);
        $label = __( 'Settings', 'ghostlabs-cycle-block-lite');
        $style = $needsAttention ? ' style="color: #3858e9; font-weight: 700;"' : '';

        return sprintf(
            '<a href="%s" aria-label="%s"%s>%s</a>',
            esc_url($url),
            esc_attr($label),
            $style,
            esc_html($label)
        );
    }
}
