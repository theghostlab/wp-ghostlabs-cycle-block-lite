<?php
/**
 * Cycle Block — Bootstrap
 *
 * Loaded by every tier's plugin header after the constants are defined. Lives in
 * shared/ so the build copies it to the assembled plugin root.
 *
 * The header that requires this file has already defined:
 *   GHOSTLABS_CYCLE_BLOCK_EDITION, _VERSION, _PLUGIN_PATH, _PLUGIN_URL, _PLUGIN_SLUG
 *
 * @package GhostLabsCycleBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress 6.7 emits a _load_textdomain_just_in_time notice for translations
 * requested before init. Suppress only that notice; everything else is passed
 * through to the previous handler.
 */
set_error_handler( static function ( $errno, $errstr, $errfile, $errline ) {
	return str_contains( $errstr, '_load_textdomain_just_in_time' );
}, E_USER_NOTICE | E_NOTICE );

/**
 * Load the Composer autoloader (PSR-4: GHOSTLABS\CYCLE_BLOCK\ -> inc/).
 *
 * Resolved from the entry file's directory, NOT from __DIR__. In an assembled
 * build the two are the same, but when a tier folder runs in place this file is
 * still in the monorepo's shared/ while vendor/ lives beside the entry file.
 */
if ( file_exists( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/autoload.php';
} else {
	add_action( 'admin_notices', static function () {
		echo '<div class="error"><p>Cycle Block plugin error: Composer autoloader not found. Please run <code>composer install</code>.</p></div>';
	} );

	return;
}

/**
 * Load the GhostLabs packages through their version gates.
 *
 * Both declare an empty Composer `autoload` block on purpose: several GhostLabs
 * plugins bundle these namespaces at different versions, so instead of every
 * copy registering itself, each registers its version with its own registry and
 * only the newest one's autoloader is initialised — on `plugins_loaded` at
 * priority 0, before Kernel boots at 10.
 *
 * `wp-kit` carries DTOMapper and the DTO/options/REST contracts, and every
 * edition needs it because the *shared* layer is built on it. It is GPL, which
 * is what lets a tier ship to WordPress.org. Editions may vendor further
 * packages of their own.
 *
 * **Whatever is vendored is what loads.** The list is read from the directory
 * rather than written here, so this file never names a package an edition does
 * not ship — the set of packages present is exactly what distinguishes the
 * editions, and hardcoding the union of them puts every edition's dependencies
 * in every edition's source.
 *
 * Kit first. Not load-bearing — autoloading is lazy, and nothing resolves a
 * class until Kernel constructs one at priority 10 — but it documents the
 * dependency direction, and it is the order the other packages' own
 * "is kit present?" diagnostics assume.
 */
$ghostlabs_cycle_block_vendor = GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/ghostlabs/';

$ghostlabs_cycle_block_packages = glob( $ghostlabs_cycle_block_vendor . '*', GLOB_ONLYDIR ) ?: [];

usort(
	$ghostlabs_cycle_block_packages,
	static function ( $a, $b ) {
		// wp-kit first; everything else keeps its natural order.
		return ( basename( $b ) === 'wp-kit' ) <=> ( basename( $a ) === 'wp-kit' )
			?: strcmp( $a, $b );
	}
);

foreach ( $ghostlabs_cycle_block_packages as $ghostlabs_cycle_block_dir ) {
	$ghostlabs_cycle_block_loader = sprintf( '%s/%s.php', $ghostlabs_cycle_block_dir, basename( $ghostlabs_cycle_block_dir ) );

	if ( file_exists( $ghostlabs_cycle_block_loader ) ) {
		require_once $ghostlabs_cycle_block_loader;
	}
}

unset( $ghostlabs_cycle_block_vendor, $ghostlabs_cycle_block_packages, $ghostlabs_cycle_block_dir, $ghostlabs_cycle_block_loader );

// Load text domain.
add_action( 'init', static function () {
	load_plugin_textdomain(
		'ghostlabs-cycle-block-lite',
		false,
		dirname( GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG ) . '/languages'
	);
} );

use GHOSTLABS\CYCLE_BLOCK\Kernel;
use GHOSTLABS\CYCLE_BLOCK\Services\ActivatorDeactivator;

/**
 * The entry file is `ghostlabs-cycle-block.php` in an assembled build and
 * `cycle-block.php` when running a tier folder in place, so derive it from the
 * slug the header defined rather than hardcoding a filename.
 */
$ghostlabs_cycle_block_entry = GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . basename( GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG );

register_activation_hook( $ghostlabs_cycle_block_entry, [ ActivatorDeactivator::class, 'activate' ] );
register_deactivation_hook( $ghostlabs_cycle_block_entry, [ ActivatorDeactivator::class, 'deactivate' ] );

unset( $ghostlabs_cycle_block_entry );

// Boot the plugin.
add_action( 'plugins_loaded', static function () {
	try {
		Kernel::getInstance()->init()->run();
	} catch ( \Throwable $e ) {
		error_log( '[Cycle Block] ' . $e->getMessage() );
	}
}, 10 );
