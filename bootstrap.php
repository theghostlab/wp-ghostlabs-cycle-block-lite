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

$ghostlabs_cycle_block_packages = array_merge(
	glob( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'vendor/ghostlabs/*', GLOB_ONLYDIR ) ?: [],
	glob( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'lib/*', GLOB_ONLYDIR ) ?: []
);

usort(
	$ghostlabs_cycle_block_packages,
	static function ( $a, $b ) {
		
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

unset( $ghostlabs_cycle_block_packages, $ghostlabs_cycle_block_dir, $ghostlabs_cycle_block_loader );

use GHOSTLABS\CYCLE_BLOCK\Boot\TierBootFactory;
use GHOSTLABS\CYCLE_BLOCK\Kernel;
use GHOSTLABS\CYCLE_BLOCK\Services\ActivatorDeactivator;
use GHOSTLABS\CYCLE_BLOCK\Services\DebugLog;
use GHOSTLABS\CYCLE_BLOCK\Services\TranslationNotice;

add_action( 'init', static function () {
	$ghostlabs_cycle_block_languages = TierBootFactory::current()->translationsDir();

	if ( $ghostlabs_cycle_block_languages === null ) {
		return;
	}

	// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Unreachable in the WordPress.org edition: its tier boot answers null above and returns before this line. This file is shared with an edition distributed outside .org, which has no language-pack installer behind it and whose shipped .mo files are read only because of this call.
	load_plugin_textdomain(
		'ghostlabs-cycle-block-lite',
		false,
		dirname( GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG ) . '/' . $ghostlabs_cycle_block_languages
	);
} );

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
