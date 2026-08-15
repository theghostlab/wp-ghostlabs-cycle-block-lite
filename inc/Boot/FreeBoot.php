<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;
use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;
use GHOSTLABS\CYCLE_BLOCK\Gate\OpenGate;
use GHOSTLABS\CYCLE_BLOCK\Services\Loader;
use GHOSTLABS\CYCLE_BLOCK\Services\SettingsService;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This edition's wiring — which is almost none, and that is the point.
 *
 * Everything this edition does is in shared/: the two blocks, the schedules it
 * offers, shuffle and sequence order, the schedule tables and the server-side
 * render. Kernel registers all of it, so this class exists to answer the
 * questions Kernel asks rather than to register services.
 *
 * The gate is open because there is nothing to check. This is a null object, not
 * a missing one: Kernel always has a gate to ask, so it never has to know which
 * edition is running.
 */
final class FreeBoot implements TierBootInterface
{
    public function gate(): GateInterface
    {
        return new OpenGate();
    }

    public function boot(Loader $loader): void
    {
        $this->defineSettingsScreen();
    }

    /**
     * None. The three schedule tables are shared, and `DBSetup` creates those;
     * this edition adds none of its own.
     *
     * Installs assembled before 2026-08-13 carry one extra table, because the
     * shared `DBSetup` declared it and `get_class_methods()` ran it in every
     * editions. It is left in place rather than dropped: this plugin has no
     * migration routine and never drops tables, so residual data outlives the
     * code that made it — the same call already taken for the orphaned
     * `utm_sets` table.
     *
     * @return list<string>
     */
    public function schemaStatements(string $tablePrefix, string $charsetCollate): array
    {
        return [];
    }

    /**
     * None — this edition adds no tables, so it has none to migrate.
     *
     * @return array<string, string>
     */
    public function legacyIndexes(): array
    {
        return [];
    }

    /**
     * Settings → Cycle Block: one tab, and it is the upgrade path.
     *
     * Direct `add_action` rather than the Loader, which is how every edition
     * registers its own screen: a settings page has to be reachable even when
     * the gate is denying, since that is where a site fixes whatever the gate is
     * unhappy about. Here the gate is always open, but keeping the same
     * registration shape means the screen appears at the same point in the
     * request in every edition — one less difference to reason about when a
     * shared change misbehaves in only one of them.
     *
     * The Custom Events upsell panel in the editor sidebar needs no counterpart
     * here: it is supplied through the `@tier` alias at build time.
     */
    private function defineSettingsScreen(): void
    {
        $settings = new SettingsService();

        add_action('admin_menu', [$settings, 'page']);
        add_action('admin_enqueue_scripts', [$settings, 'scripts']);
    }
}
