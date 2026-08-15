import { createRoot } from 'react-dom/client';
import {
	HeaderContainer,
	HeaderTitle,
	Navigation,
	NavigationItem,
} from './_components/ui';
import ProComponent from './_components/pro/ProComponent';

// The GLOBAL wp.i18n, never an import — esbuild bundles for real, and a bundled
// @wordpress/i18n keeps its own empty locale registry that
// wp_set_script_translations() can never reach. The wp-i18n dep on the enqueue
// guarantees the global exists before this module evaluates.
const { __, sprintf } = window.wp.i18n;

const { rootElementId } = window.GHOSTLABS_CYCLE_BLOCK_CONFIG;

/**
 * This edition's settings screen: one tab, and it is the upsell. There are no
 * options to store, so there is nothing here to configure.
 *
 * ⚠️ **Composed from local primitives rather than mounted through the shared
 * `mountSettingsApp`, and that is deliberate — it is the whole reason this file
 * looks different from every sibling plugin's.**
 *
 * `SettingsApp` builds the shell's full machinery unconditionally, including the
 * parts this edition does not use. Being unused is not the same as being absent:
 * a static import is a static import, so a bundler has to include whatever
 * `SettingsApp` references whether or not it ever runs. Mounting the shell here
 * pulled a whole subsystem this edition does not use into its compiled bundle,
 * where it was unreachable at runtime and perfectly readable to anyone who
 * opened the file. That matters because this edition is published to
 * WordPress.org with its source.
 *
 * Importing only the presentational primitives makes those modules unreachable
 * from this entry point, so the bundler drops them. It is the *import graph*
 * that does the work here, not a flag — a runtime toggle inside `SettingsApp`
 * would have changed nothing, because the reference would still be there.
 *
 * The cost is that this screen no longer inherits the shell's tab machine, so if
 * a second tab is ever added it needs real tab state rather than the constant
 * below. With one tab, state would be ceremony.
 *
 * `restNamespace` is deliberately not read: nothing on this screen calls the
 * REST API, and passing it was only ever feeding the endpoint builder that came
 * with the shell.
 */
const TAB_TITLE = __( 'Cycle Block Pro', 'ghostlabs-cycle-block-lite' );

const SettingsScreen = () => (
	<>
		<HeaderContainer>
			<HeaderTitle>
				{ sprintf(
					/* translators: %s: the active settings tab title. */
					__( 'Cycle Block Settings: %s', 'ghostlabs-cycle-block-lite' ),
					TAB_TITLE
				) }
			</HeaderTitle>
		</HeaderContainer>

		<Navigation>
			<NavigationItem active>{ TAB_TITLE }</NavigationItem>
		</Navigation>

		<ProComponent />
	</>
);

// On DOMContentLoaded, matching how the shared package's own `mountSettingsApp`
// mounts. The script is enqueued in the footer, so the root div is already
// parsed in practice — but the shell has always waited, and an admin screen that
// mounts at a different point in the lifecycle than every sibling plugin's is a
// difference nobody would think to check when one of them misbehaves.
document.addEventListener( 'DOMContentLoaded', () => {
	const root = document.getElementById( rootElementId );

	if ( root ) {
		createRoot( root ).render( <SettingsScreen /> );
	}
} );
