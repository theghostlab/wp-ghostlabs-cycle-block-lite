<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GhostLabs\WPKit\Contracts\MutableOptionsServiceInterface;

/**
 * Plugin settings, stored in one wp_options row.
 *
 * The shape is nested — `ghostlabs_cycle_block => ['ghostlabs-cycle-block' => [...]]`
 * — where the sibling plugins store a flat array. It stays that way: the plugin
 * has no migration routine, so flattening it would orphan every existing
 * install's stored settings the moment it upgraded.
 */
class OptionsService implements MutableOptionsServiceInterface
{
	public function delete(string $key): ?bool {

		$options = get_option(PluginConfig::$domain);

		if( empty($options) ) {
			return null;
		}

		unset($options[PluginConfig::$pluginSlug][$key]);

		return update_option(PluginConfig::$domain, $options);
	}

    public function get(string $key): mixed {
        $options = get_option(PluginConfig::$domain);

	    if( !$options || !array_key_exists(PluginConfig::$pluginSlug, $options) ) {
		    $options = [];
            $options[PluginConfig::$pluginSlug] = [];
            update_option(PluginConfig::$domain, $options);
            return null;
        }

        if( !array_key_exists($key, $options[PluginConfig::$pluginSlug]) ) {
            return null;
        }

        return $options[PluginConfig::$pluginSlug][$key];
    }

    public function save(string $key, mixed $data): bool
    {
        $options = get_option(PluginConfig::$domain);

        if( empty($options) ) {
            $options = [
                PluginConfig::$pluginSlug => [
                    $key => $data,
                ]
            ];
        } else {
            $options[PluginConfig::$pluginSlug][$key] = $data;
        }

        return update_option(PluginConfig::$domain, $options);
    }
}
