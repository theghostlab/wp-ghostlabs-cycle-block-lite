<?php

namespace THEGHOSTLAB\CYCLE\Services;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;

class PreviousSettings
{
    static public function getSettings(int $postId, string $blockId)
    {
        $default = [
            'randomize' => 'true',
            'repeat' => 'false'
        ];

        $previousSettingsTransient = PluginInfo::previousSettingsTransient($postId, $blockId);
        $previousSettings = get_transient($previousSettingsTransient) ?? $default;

        if( empty($previousSettings['repeat']) ) {
            $previousSettings['repeat'] = 'false';
        }

        if( empty($previousSettings['randomize']) ) {
            $previousSettings['randomize'] = 'true';
        }

        return $previousSettings;
    }

    static public function setSettings(array $settings, int $postId, string $blockId)
    {
        $previousSettingsTransient = PluginInfo::previousSettingsTransient($postId, $blockId);

        if ( false === ( $previousSettings = get_transient( $previousSettingsTransient ) ) ) {
            set_transient( $previousSettingsTransient, $settings, HOUR_IN_SECONDS );
        } else {
            $previousSettings = array_merge($previousSettings, $settings);
            set_transient( $previousSettingsTransient, $previousSettings, HOUR_IN_SECONDS );
        }
    }
}