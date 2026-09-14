<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GhostLabs\WPKit\Contracts\MutableOptionsServiceInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

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
