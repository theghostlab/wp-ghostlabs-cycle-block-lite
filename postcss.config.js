const minify = process.env.GHOSTLABS_MINIFY === '1';

const plugins = [ require( 'autoprefixer' ) ];

if ( minify ) {
	plugins.push(
		require( 'cssnano' )( {
			preset: [
				'default',
				{
					discardComments: {
						removeAll: true,
					},
				},
			],
		} )
	);
}

module.exports = { plugins };
