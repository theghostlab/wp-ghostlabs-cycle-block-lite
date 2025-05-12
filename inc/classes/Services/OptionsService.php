<?php

namespace THEGHOSTLAB\CYCLE\Services;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;

class OptionsService
{
	public function delete(string $key): ?bool {
		$options = get_option(PluginInfo::$domain);

		if( empty($options) ) {
			return null;
		}

		unset($options[PluginInfo::$pluginSlug][$key]);

		return update_option(PluginInfo::$domain, $options);
	}

    public function get(string $key) {
        $options = get_option(PluginInfo::$domain);

	    if( !$options || !array_key_exists(PluginInfo::$pluginSlug, $options) ) {
		    $options = [];
            $options[PluginInfo::$pluginSlug] = [];
            update_option(PluginInfo::$domain, $options);
            return null;
        }

        if( !array_key_exists($key, $options[PluginInfo::$pluginSlug]) ) {
            return null;
        }

        return $options[PluginInfo::$pluginSlug][$key];
    }

    public function save(string $key, $data): bool
    {
        $options = get_option(PluginInfo::$domain);

        if( empty($options) ) {
            $options = [
                PluginInfo::$pluginSlug => [
                    $key => $data,
                ]
            ];
        } else {
            $options[PluginInfo::$pluginSlug][$key] = $data;
        }

        return update_option(PluginInfo::$domain, $options);
    }
}