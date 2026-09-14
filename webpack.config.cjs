const path = require( 'path' );
const fs = require( 'fs' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const SOURCE_ROOTS = [
	path.resolve( __dirname, 'src' ),
	path.resolve( __dirname, '../shared/src' ),
].filter( ( dir ) => fs.existsSync( dir ) );

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

const entry = {};
for ( const root of [ ...SOURCE_ROOTS ].reverse() ) {
	Object.assign( entry, entriesFor( root ) );
}

const copyPlugin = defaultConfig.plugins.find(
	( plugin ) => plugin.constructor && plugin.constructor.name === 'CopyPlugin'
);

const sharedSrc = path.resolve( __dirname, '../shared/src' );

if ( copyPlugin && fs.existsSync( sharedSrc ) ) {
	copyPlugin.patterns.unshift(
		{ from: '**/block.json', context: sharedSrc, noErrorOnMissing: true },
		{ from: '**/*.php', context: sharedSrc, noErrorOnMissing: true }
	);
}

module.exports = {
	...defaultConfig,
	entry,
	optimization: {
		...defaultConfig.optimization,
		minimize: false,
	},
	resolve: {
		...defaultConfig.resolve,
		modules: [
			path.resolve( __dirname, 'node_modules' ),
			'node_modules',
		],
		alias: {
			...defaultConfig.resolve?.alias,
			'@tier': path.resolve( __dirname, 'src/tier' ),

			'@shared': fs.existsSync( path.resolve( __dirname, '../shared/src' ) )
				? path.resolve( __dirname, '../shared/src' )
				: path.resolve( __dirname, 'src' ),
		},
	},
};
