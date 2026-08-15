import * as esbuild from 'esbuild';

/**
 * Settings-app bundler — free tier.
 *
 * dev/javascript/settings/settings.jsx -> assets/js/settings/settings-bundle.js
 *
 * Deliberately leaner than the paid tier's, which wraps the build in a
 * plugin that timestamps each run and raises a desktop notification on failure.
 * That costs `date-fns` and `node-notifier` as dependencies for developer
 * convenience alone; this tier has one entry point and its failures are read off
 * the exit code, so it carries neither.
 *
 * esbuild bundles for real, unlike the webpack block build which treats
 * @wordpress/* as externals — so everything imported from here must be a
 * declared dependency. pnpm's strict node_modules will fail the build rather
 * than silently resolve a hoisted transitive.
 */
const isProduction = process.env.NODE_ENV === 'production';
const watch = process.argv.slice( 2 ).includes( '--watch' );

const ctx = await esbuild.context( {
	color: true,
	entryPoints: [ 'dev/javascript/settings/settings.jsx' ],
	entryNames: '[dir]/[name]-bundle',
	bundle: true,
	outbase: 'dev/javascript',
	outdir: 'assets/js',
	minify: isProduction,
	sourcemap: ! isProduction,
	treeShaking: true,
	pure: [ 'console.log' ],
	loader: {
		'.jpg': 'dataurl',
		'.png': 'dataurl',
		'.svg': 'text',
		'.js': 'jsx',
	},
} );

if ( watch ) {
	await ctx.watch();
	console.log( 'watching settings app…' );
} else {
	await ctx.rebuild();
	await ctx.dispose();
}
