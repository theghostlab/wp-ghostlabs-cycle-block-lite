const { __ } = window.wp.i18n;

const MUTED_ICON = 'text-gray-500';

const Included = ( { muted = false } ) => (
	<>
		<svg
			className={ `mx-auto h-5 w-5 ${
				muted ? MUTED_ICON : 'text-primary-500'
			}` }
			viewBox="0 0 20 20"
			fill="currentColor"
			aria-hidden="true"
			focusable="false"
		>
			<path
				fillRule="evenodd"
				d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
				clipRule="evenodd"
			/>
		</svg>
		<span className="sr-only">
			{ __( 'Included', 'ghostlabs-cycle-block-lite' ) }
		</span>
	</>
);

const NotIncluded = () => (
	<>
		<svg
			className={ `mx-auto h-5 w-5 ${ MUTED_ICON }` }
			viewBox="0 0 20 20"
			fill="currentColor"
			aria-hidden="true"
			focusable="false"
		>
			<path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
		</svg>
		<span className="sr-only">
			{ __( 'Not included', 'ghostlabs-cycle-block-lite' ) }
		</span>
	</>
);

const ROWS = window.GHOSTLABS_CYCLE_BLOCK_CONFIG?.tiers ?? [];

const cell = ( value, muted ) => {
	if ( value === true ) {
		return <Included muted={ muted } />;
	}

	if ( value === false ) {
		return <NotIncluded />;
	}

	return value;
};

const ComparisonTable = () => (
	<table data-gl-comparison className="w-full border-collapse text-left">
		<caption className="sr-only">
			{ __(
				'What the free edition includes, and what Cycle Block Pro adds',
				'ghostlabs-cycle-block-lite'
			) }
		</caption>
		<thead>
			<tr className="bg-gray-50">
				<th scope="col" className="px-8 py-4">
					<span className="sr-only">
						{ __( 'Feature', 'ghostlabs-cycle-block-lite' ) }
					</span>
				</th>
				<th
					scope="col"
					className="w-[210px] px-4 py-4 text-center text-lg font-semibold text-gray-500"
				>
					{ __( 'Free', 'ghostlabs-cycle-block-lite' ) }
				</th>
				<th
					scope="col"
					className="w-[210px] px-4 py-4 text-center text-lg font-semibold text-primary-500"
				>
					{ __( 'Pro', 'ghostlabs-cycle-block-lite' ) }
				</th>
			</tr>
		</thead>
		<tbody>
			{ ROWS.map( ( { key, label, free, pro } ) => (
				<tr key={ key } className="border-t border-gray-100">
					<th
						scope="row"
						className="px-8 py-4 text-sm font-normal text-gray-700"
					>
						{ label }
					</th>
					<td className="px-4 py-4 text-center text-sm text-gray-500">
						{ cell( free, true ) }
					</td>
					<td className="px-4 py-4 text-center text-sm font-semibold text-primary-500">
						{ cell( pro, false ) }
					</td>
				</tr>
			) ) }
		</tbody>
	</table>
);

export default ComparisonTable;
