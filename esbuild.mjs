import * as esbuild from 'esbuild';

const isProduction = process.env.NODE_ENV === 'production';
const watch = process.argv.slice( 2 ).includes( '--watch' );

const ctx = await esbuild.context( {
	color: true,
	entryPoints: [ 'dev/javascript/settings/settings.jsx' ],
	entryNames: '[dir]/[name]-bundle',
	bundle: true,
	outbase: 'dev/javascript',
	outdir: 'assets/js',
	minify: process.env.GHOSTLABS_MINIFY === '1',
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
