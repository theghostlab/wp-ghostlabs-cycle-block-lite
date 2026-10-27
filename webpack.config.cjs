const path = require( 'path' );
const fs = require( 'fs' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const MINIFY = process.env.GHOSTLABS_MINIFY === '1';

const withSassOutputStyle = ( rules ) =>
	rules.map( ( rule ) => {
		if ( Array.isArray( rule.oneOf ) ) {
			return { ...rule, oneOf: withSassOutputStyle( rule.oneOf ) };
		}

		if ( ! Array.isArray( rule.use ) ) {
			return rule;
		}

		return {
			...rule,
			use: rule.use.map( ( entry ) => {
				const loader = typeof entry === 'string' ? entry : entry?.loader;

				if ( ! loader || ! loader.includes( 'sass-loader' ) ) {
					return entry;
				}

				const base = typeof entry === 'string' ? { loader: entry } : entry;
				const options = base.options ?? {};

				return {
					...base,
					options: {
						...options,
						sassOptions: {
							...options.sassOptions,
							outputStyle: MINIFY ? 'compressed' : 'expanded',
						},
					},
				};
			} ),
		};
	} );

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
		minimize: MINIFY,
	},
	module: {
		...defaultConfig.module,
		rules: withSassOutputStyle( defaultConfig.module.rules ),
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
