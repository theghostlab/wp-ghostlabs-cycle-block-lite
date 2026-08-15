<?php
/**
 * GhostLabs WP-Kit — Version-gated loader.
 *
 * Multiple GhostLabs plugins bundle this library. Each plugin requires this
 * file during bootstrap. Only the newest registered version's autoloader is
 * initialised, preventing class-definition conflicts when two or more
 * plugins ship different versions of the same namespace.
 *
 * Pattern borrowed from woocommerce/action-scheduler.
 *
 * Two properties of this design are load bearing:
 *
 *   - The registry elects a copy, not a set. Losing copies never register an
 *     autoloader and there is no fallback chain, so the winning copy must be a
 *     superset of every other copy on the site. That is why releases here are
 *     additive-only: a class removed in version N is a fatal error in every
 *     plugin on the site still compiled against N-1, including plugins whose
 *     own composer.lock pins the older version. Composer resolves per plugin;
 *     PHP resolves per site.
 *   - Registration happens at include time; election happens on `plugins_loaded`
 *     at priority 0. Keep the require in the consumer's bootstrap rather than
 *     inside a hook — every copy on the site must have registered before any
 *     initializer runs, and plugin files are all included before
 *     `plugins_loaded` fires.
 *
 * Never touch a `GhostLabs\WPKit\` class at include time. A `use` statement is
 * free, but instantiating, `implements`-resolving or reflecting one before
 * priority 0 fatals no matter how correct this file is.
 *
 * @package GhostLabsWPKit
 */

if ( ! function_exists( 'add_action' ) ) {
	require_once __DIR__ . '/autoload.php';
	return;
}

if ( ! class_exists( 'GhostLabsWPKitVersions', false ) ) {

	class GhostLabsWPKitVersions {

		private static ?self $instance = null;

		/** @var array<string, callable> */
		private array $versions = [];

		public function register( string $version, callable $initializer ): void {
			if ( ! isset( $this->versions[ $version ] ) ) {
				$this->versions[ $version ] = $initializer;
			}
		}

		public function latestVersion(): ?string {
			$keys = array_keys( $this->versions );
			if ( empty( $keys ) ) {
				return null;
			}
			usort( $keys, 'version_compare' );
			return end( $keys );
		}

		public static function instance(): self {
			return self::$instance ??= new self();
		}

		public static function initializeLatest(): void {
			$self   = self::instance();
			$latest = $self->latestVersion();

			if ( $latest !== null && isset( $self->versions[ $latest ] ) ) {
				call_user_func( $self->versions[ $latest ] );
			}
		}
	}

	add_action( 'plugins_loaded', [ 'GhostLabsWPKitVersions', 'initializeLatest' ], 0 );
}

/*
 * The version string below must equal the git tag. `version_compare` over a
 * typo ("1.0" against "1.0.0", a stray "v") silently elects the wrong copy and
 * nothing else in the system notices. `tests/LoaderVersionTest.php` pins it.
 */
GhostLabsWPKitVersions::instance()->register( '1.0.1', function () {
	require_once __DIR__ . '/autoload.php';
} );
