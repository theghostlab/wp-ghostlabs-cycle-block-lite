<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Contracts;

use GHOSTLABS\CYCLE_BLOCK\Services\Loader;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The one object an edition supplies to describe itself to the Kernel.
 *
 * Everything Kernel wires is common to every tier; everything that is not lives
 * behind this interface. Each edition ships exactly one implementation, named by
 * convention after the edition constant — edition `x` resolves `Boot\XBoot` — so
 * the lookup is a single autoload rather than a directory walk.
 *
 * That distinction matters: `Utils::getClasses()` `require_once`s every PHP file
 * it passes, and Kernel deliberately defers that walk to `rest_api_init` so an
 * ordinary front-end request never pays for it. Discovering the tier boot the
 * same way would have put the walk back on every page load.
 */
interface TierBootInterface
{
    /**
     * Whether this edition's gated hooks may run at all.
     */
    public function gate(): GateInterface;

    /**
     * Register the services only this edition has.
     *
     * Hooks queued on `$loader` are gated — they fire only if `gate()` allows.
     * Anything that must work even while the gate is denying — a settings screen
     * is the usual case, since that is where a site fixes whatever the gate is
     * unhappy about — should be registered with a direct `add_action()` here
     * instead, exactly as Kernel does for the same reason.
     */
    public function boot(Loader $loader): void;

    /**
     * dbDelta-ready CREATE statements for tables only this edition uses.
     *
     * `DBSetup` finds its own work with `get_class_methods($this)`, so anything
     * declared on that shared class runs in *every* edition — which is how an
     * edition ended up creating a table it had no code to write to.
     *
     * Asked for here rather than filtered, deliberately. `boot()` runs on
     * `plugins_loaded`, which has already fired by the time an activation hook
     * runs, so a filter registered there would miss activation completely:
     * `install()` would create the shared tables, stamp the version option, and
     * `maybeUpgrade()` would then see a current version and never correct it,
     * leaving the edition permanently without its own tables. `TierBootFactory`
     * resolves on the edition constant and so answers in any request phase,
     * activation included.
     *
     * Statements must be dbDelta-conformant in the same way the shared ones are:
     * no `IF NOT EXISTS`, two spaces after `PRIMARY KEY`, named unique keys.
     * `TierSchemaTest` pins that.
     *
     * @param string $tablePrefix Full prefix, `$wpdb->prefix` + the plugin's own.
     * @param string $charsetCollate Result of `$wpdb->get_charset_collate()`.
     * @return list<string>
     */
    public function schemaStatements(string $tablePrefix, string $charsetCollate): array;

    /**
     * Legacy unique indexes to drop from this edition's own tables, as
     * `table-suffix => index-name`.
     *
     * The counterpart to `schemaStatements()`: an edition owns its tables, so it
     * owns their migrations too. Keeping this map on the shared `DBSetup` meant
     * every edition named — and ran `SHOW INDEX` against — tables belonging to an
     * edition it is not.
     *
     * dbDelta only ever ADDs, so an index it can no longer produce has to be
     * dropped explicitly. Idempotent by construction: a fresh install never
     * creates these, and the caller skips any table that does not exist.
     *
     * @return array<string, string>
     */
    public function legacyIndexes(): array;
}
