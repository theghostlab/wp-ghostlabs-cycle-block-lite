<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

class PluginMeta {

    /**
     * plugin_action_links_{file} filter callback.
     *
     * Every edition gets a Settings link; some have more to say about it than
     * others. The two filters below are how an edition says it — each defaults to
     * the plain behaviour, so this class never names anything only one edition
     * ships.
     *
     * @param array<string, string> $links_array
     * @param string $plugin_file_name
     * @param array<string, mixed> $plugin_data
     * @param string $context
     * @return array<int|string, string>
     */
    public function actionSettings($links_array, $plugin_file_name, $plugin_data, $context) {

        /**
         * Should the Settings link be emphasised to pull the user into it?
         *
         * An edition with something for the user to resolve answers true, which
         * is the nudge to go and resolve it. With nothing to resolve, the default
         * `false` is the whole of the behaviour.
         */
        $needsAttention = (bool) apply_filters('theghostlab_cycle_settings_link_needs_attention', false);

        $settings = $this->settingsLink($needsAttention);

        array_unshift( $links_array, $settings );

        /**
         * Extra action links. An edition may append its own; by default there
         * are none.
         *
         * Applied after the unshift so callbacks see the finished row and never
         * have to know which index the Settings link landed on.
         */
        return apply_filters('theghostlab_cycle_plugin_action_links', $links_array, $context);
    }

    /**
     * The Settings link, optionally emphasised.
     */
    public function settingsLink(bool $needsAttention = false): string
    {
        $path  = sprintf("options-general.php?page=%s&tab=home", PluginConfig::$settingsMenuSlug);
        $url   = admin_url($path);
        $label = __( 'Settings', 'ghostlabs-cycle-block-lite');
        $style = $needsAttention ? ' style="color: #3858e9; font-weight: 700;"' : '';

        return sprintf(
            '<a href="%s" aria-label="%s"%s>%s</a>',
            $url,
            $label,
            $style,
            $label
        );
    }
}
