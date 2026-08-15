<?php

namespace GhostLabs\WPKit\Contracts;

/**
 * The half of a plugin's configuration that carries no licence concept.
 *
 * This is the narrow contract wp-kit services type against. It was carved out of
 * `ghostlabs/wp-shared`'s `ConfigInterface`; the four members that stayed behind
 * (`getCacheKey()`, `getTransientKey()`, `getUpdateUrl()`, `getBlacklist()`)
 * exist only for licence verification and self-hosted updates, and now live in
 * the licence package's `ConfigInterface`, which extends this one.
 *
 * Consumers implement that wider interface as before and satisfy this one by
 * inheritance, so nothing in a paid plugin needs a second adapter — and a free
 * plugin gets the identity contract without the licence package.
 *
 * Deliberately no `@see` to that interface by name: nothing under wp-kit's src/
 * may mention the licence namespace, not even in prose. The dependency runs one
 * way, `tests/DependencyDirectionTest.php` enforces it by grep, and a doc
 * reference is exactly the kind of "harmless" hit that erodes the check.
 */
interface PluginIdentityInterface
{
    /**
     * The plugin's main file path relative to the plugins directory.
     * e.g. "my-plugin/my-plugin.php"
     */
    public function getPluginFile(): string;

    /**
     * The slug used in WordPress updater checks.
     * Typically the plugin filename without the .php extension.
     */
    public function getUpdaterSlug(): string;

    /**
     * The current plugin version string.
     */
    public function getVersion(): string;

    /**
     * The REST API namespace for this plugin's routes.
     * e.g. "ghostlabs/glide/pro/v1"
     */
    public function getRestNamespace(): string;

    public function getDomain(): string;

    public function getSettingsMenuSlug(): string;
}
