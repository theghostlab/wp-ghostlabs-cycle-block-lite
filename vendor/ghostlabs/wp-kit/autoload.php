<?php
/**
 * PSR-4 autoloader for GhostLabs\WPKit\.
 *
 * Loaded by wp-kit.php after the version gate selects the newest copy.
 *
 * Note the `file_exists()` guard: a class the winning copy does not have
 * produces no diagnostic from this package at all — just PHP's own
 * `Error: Class "…" not found` from wherever it was first touched. That is the
 * reason releases must be additive-only (see wp-kit.php).
 *
 * @package GhostLabsWPKit
 */

spl_autoload_register( function ( string $class ): void {
	$prefix = 'GhostLabs\\WPKit\\';

	if ( ! str_starts_with( $class, $prefix ) ) {
		return;
	}

	$relative = substr( $class, strlen( $prefix ) );
	$file     = __DIR__ . '/src/' . str_replace( '\\', '/', $relative ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );
