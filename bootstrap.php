<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/autoload.php';
} else {
	add_action( 'admin_notices', static function () {
		echo '<div class="error"><p>Cycle Block plugin error: Composer autoloader not found. Please run <code>composer install</code>.</p></div>';
	} );

	return;
}

$ghostlabs_cycle_block_packages = glob( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/ghostlabs/*', GLOB_ONLYDIR ) ?: [];

sort( $ghostlabs_cycle_block_packages, SORT_STRING );

foreach ( $ghostlabs_cycle_block_packages as $ghostlabs_cycle_block_dir ) {
	$ghostlabs_cycle_block_loader = sprintf( '%s/%s.php', $ghostlabs_cycle_block_dir, basename( $ghostlabs_cycle_block_dir ) );

	if ( file_exists( $ghostlabs_cycle_block_loader ) ) {
		require_once $ghostlabs_cycle_block_loader;
	}
}

unset( $ghostlabs_cycle_block_packages, $ghostlabs_cycle_block_dir, $ghostlabs_cycle_block_loader );

use GHOSTLABS\CYCLE_BLOCK\Kernel;
use GHOSTLABS\CYCLE_BLOCK\Services\ActivatorDeactivator;
use GHOSTLABS\CYCLE_BLOCK\Services\DebugLog;
use GHOSTLABS\CYCLE_BLOCK\Services\TranslationNotice;

// since 4.6. The call used to sit here behind `translationsDir()` and a `phpcs:ignore`,

TranslationNotice::register();

$ghostlabs_cycle_block_entry = GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . basename( GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG );

register_activation_hook( $ghostlabs_cycle_block_entry, [ ActivatorDeactivator::class, 'activate' ] );
register_deactivation_hook( $ghostlabs_cycle_block_entry, [ ActivatorDeactivator::class, 'deactivate' ] );

add_action( 'wp_initialize_site', [ ActivatorDeactivator::class, 'onNewSite' ], 20, 1 );

unset( $ghostlabs_cycle_block_entry );

add_action( 'plugins_loaded', static function () {
	try {
		Kernel::getInstance()->init()->run();
	} catch ( \Throwable $e ) {
		DebugLog::write( $e->getMessage() );
	}
}, 10 );
