/**
 * Gutenberg block build (@wordpress/scripts → webpack).
 *
 *   shared/src/blocks/**  ─┐
 *                          ├─→ build/blocks/**
 *   pro-only/src/blocks/** ┘
 *
 * The two source roots are layered *here*, not by build.sh. The build assembles
 * the plugin from already-compiled output, so by the time it merges shared/ and
 * {tier}-only/ the JS is long since bundled — webpack is the only place the
 * layering can happen for block source.
 *
 * Tier-local wins on collision, the same precedence the build's overlay gives.
 *
 * Where a shared module needs something only one edition has, it imports through
 * the `@tier` alias (see resolve.alias below) rather than reaching across roots.
 */
const path = require( 'path' );
const fs = require( 'fs' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/** Tier root first: an entry it defines shadows the shared one of the same name. */
const SOURCE_ROOTS = [
	path.resolve( __dirname, 'src' ),
	path.resolve( __dirname, '../shared/src' ),
].filter( ( dir ) => fs.existsSync( dir ) );

/**
 * Every `file:`-prefixed script a block.json declares, as webpack entries keyed
 * the way wp-scripts keys them — `blocks/<name>/<file>`, so the output lands
 * beside the copied block.json and `file:index.js` still resolves.
 *
 * Read from block.json rather than assumed from filenames: a block that adds a
 * `script` or a second `viewScript` should build without anyone remembering to
 * edit this file.
 */
function entriesFor( root ) {
	const blocksDir = path.join( root, 'blocks' );

	if ( ! fs.existsSync( blocksDir ) ) {
		return {};
	}

	const entries = {};

	for ( const name of fs.readdirSync( blocksDir ) ) {
		const manifest = path.join( blocksDir, name, 'block.json' );

		if ( ! fs.existsSync( manifest ) ) {
			continue;
		}

		const block = JSON.parse( fs.readFileSync( manifest, 'utf8' ) );
		const fields = [ 'editorScript', 'script', 'viewScript', 'viewScriptModule' ];

		for ( const field of fields ) {
			const declared = [].concat( block[ field ] ?? [] );

			for ( const value of declared ) {
				// Handles are strings too ("cycle-helper-js"); only file: refs build.
				if ( typeof value !== 'string' || ! value.startsWith( 'file:' ) ) {
					continue;
				}

				const file = value.replace( /^file:\.?\/?/, '' );
				const key = `blocks/${ name }/${ file.replace( /\.[jt]sx?$/, '' ) }`;

				entries[ key ] = path.join( blocksDir, name, file );
			}
		}
	}

	return entries;
}

// Later roots must not overwrite earlier ones, so assign shared first and let
// the tier root land on top.
const entry = {};
for ( const root of [ ...SOURCE_ROOTS ].reverse() ) {
	Object.assign( entry, entriesFor( root ) );
}

/**
 * block.json (and any sidecar PHP) has to be copied from both roots.
 *
 * The default config already copies from `src`; push a second pattern for the
 * shared root rather than constructing a new CopyPlugin, which would mean
 * depending on copy-webpack-plugin directly — pnpm's strict node_modules would
 * refuse to resolve it as a hoisted transitive.
 */
const copyPlugin = defaultConfig.plugins.find(
	( plugin ) => plugin.constructor && plugin.constructor.name === 'CopyPlugin'
);

const sharedSrc = path.resolve( __dirname, '../shared/src' );

if ( copyPlugin && fs.existsSync( sharedSrc ) ) {
	// Unshifted, so the tier's own patterns are applied last and win on collision.
	copyPlugin.patterns.unshift(
		{ from: '**/block.json', context: sharedSrc, noErrorOnMissing: true },
		{ from: '**/*.php', context: sharedSrc, noErrorOnMissing: true }
	);
}

module.exports = {
	...defaultConfig,
	entry,
	resolve: {
		...defaultConfig.resolve,
		/**
		 * Node resolves a bare import by walking up from the *importing* file, so
		 * a module under shared/src/ walks past the monorepo root and never sees
		 * the tier's node_modules — `ulid` and `@wordpress/icons` both fail there
		 * while resolving fine from src/.
		 *
		 * Naming the tier's node_modules explicitly fixes it, and keeps every
		 * edition compiling shared source against its own installed dependency
		 * versions rather than a copy hoisted somewhere shared.
		 */
		modules: [
			path.resolve( __dirname, 'node_modules' ),
			'node_modules',
		],
		alias: {
			...defaultConfig.resolve?.alias,
			/**
			 * The seam between shared block source and the edition compiling it.
			 *
			 * A shared module that needs something only one tier ships imports
			 * `@tier/...`; each tier folder answers with its own `src/tier/`.
			 * That keeps the tier check at the import, resolved at build time,
			 * instead of shipping both implementations and branching at runtime.
			 */
			'@tier': path.resolve( __dirname, 'src/tier' ),

			/**
			 * The other direction: a tier module reaching back into shared source.
			 *
			 * A tier control built on a shared component keeps that component's
			 * accessibility contract instead of growing a second one. Without
			 * this the import is `../../../../shared/src/...`, which silently
			 * breaks the moment anything moves a directory.
			 *
			 * Falls back to this root when there is no sibling `shared/`. That is
			 * the source-distribution layout, where assembly has already merged
			 * both roots into one `src/` — the same "is there a sibling shared?"
			 * question `SOURCE_ROOTS` above and `Services\SourceRoots` in PHP both
			 * ask, so all three agree about which layout they are in.
			 */
			'@shared': fs.existsSync( path.resolve( __dirname, '../shared/src' ) )
				? path.resolve( __dirname, '../shared/src' )
				: path.resolve( __dirname, 'src' ),
		},
	},
};
