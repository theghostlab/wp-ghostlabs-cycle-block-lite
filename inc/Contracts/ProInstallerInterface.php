<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The seam between the upgrade orchestration and core's install machinery.
 *
 * The real implementation wraps Plugin_Upgrader and WP_Filesystem — behavior
 * that belongs to WordPress core and is exercised end-to-end by the E2E suite.
 * The orchestration around it is what the unit suite owns, through this
 * contract.
 */
interface ProInstallerInterface
{
    /**
     * Can this environment install a plugin at all?
     *
     * @return ?array{noticeCode: string, message: string} Null when it can;
     *         a structured failure (with manual-install guidance) when not.
     */
    public function preflight(): ?array;

    /**
     * Install the package and answer the installed plugin's basename.
     *
     * An already-present Pro install (a retry after a partial run) is a
     * success: the basename comes back without downloading anything.
     *
     * @return array{ok: bool, basename: ?string, noticeCode: string, message: string}
     */
    public function install(string $zipUrl): array;
}
