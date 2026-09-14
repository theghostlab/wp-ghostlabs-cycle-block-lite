import { __ } from '@wordpress/i18n';
import { Button, __experimentalText as Text } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const appendParamsToUrl = ( url, params ) => {
	const urlObject = new URL( url );

	for ( const [ key, value ] of Object.entries( params ) ) {
		urlObject.searchParams.append( key, value );
	}

	return urlObject.toString();
};

const PreviewControl = ( { blockId, postId, parentBlockId, previewLink } ) => (
	<>
		<Button
			variant="secondary"
			style={ { width: '100%' } }
			onClick={ () => {
				apiFetch( {
					path: `/${ theghostlab_cycle_vars.restNamespace }/block/clear-test-preview`,
					method: 'POST',
					data: { blockId, postId },
				} )
					.then( () => wp.data.dispatch( 'core/editor' ).savePost() )
					.then( () => {
						const link =
							wp.data
								.select( 'core/editor' )
								.getEditedPostPreviewLink() || previewLink;

						if ( ! link ) return;

						window.open(
							appendParamsToUrl( link, {
								preview: true,
								ghostlabs_testing: parentBlockId,
							} ),
							'_blank',
							'noopener,noreferrer'
						);
					} )
					.catch( ( error ) => {
						console.error( error );
					} );
			} }
		>
			<div
				style={ {
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					width: '100%',
					gap: '.35rem',
				} }
			>
				<span>
					{ __( 'Preview content cycling', 'ghostlabs-cycle-block-lite' ) }
				</span>
				<svg
					viewBox="0 0 24 24"
					width="20"
					height="20"
					fill="none"
					aria-hidden="true"
					focusable="false"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M5 6a1 1 0 011-1h4a1 1 0 100-2H6a3 3 0 00-3 3v12a3 3 0 003 3h12a3 3 0 003-3v-4a1 1 0 10-2 0v4a1 1 0 01-1 1H6a1 1 0 01-1-1V6zm10-3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 001.414 1.414L19 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"
						fill="currentColor"
					/>
				</svg>
			</div>
		</Button>
		<Text variant="muted" lineHeight="18px" size="12px">
			{ __(
				'In the preview, the cycle interval is disabled, which lets you refresh the page to cycle through variations.',
				'ghostlabs-cycle-block-lite'
			) }
		</Text>
	</>
);

export default PreviewControl;
