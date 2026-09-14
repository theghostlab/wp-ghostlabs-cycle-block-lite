const { __ } = window.wp.i18n;

const BUY_PRO_URL =
	window.GHOSTLABS_CYCLE_BLOCK_CONFIG?.upgrade?.urls?.settings;

const HeroBand = () => (
	<section
		data-gl-hero
		className="gl-hero relative -mb-3 mt-10 overflow-hidden"
	>
		{ }
		<div className="gl-hero__texture absolute inset-0" aria-hidden="true" />
		{ }
		<div className="gl-hero__sweep absolute inset-0" aria-hidden="true" />

		<div className="relative px-10 pb-14 pt-10 text-center">
			<p className="text-[10px] font-bold uppercase tracking-[1px] text-primary-25">
				{ __( 'Cycle Block Pro', 'ghostlabs-cycle-block-lite' ) }
			</p>

			{ }
			<h2 className="mt-2.5 text-4xl font-bold leading-tight text-white">
				{ __(
					'See which variation actually performs.',
					'ghostlabs-cycle-block-lite'
				) }
			</h2>

			<p className="mx-auto mt-3 max-w-[720px] text-base leading-relaxed text-primary-25">
				{ __(
					'Every schedule, shuffle and preview you already have — plus a view and a click event per variation, sent to every analytics tool on your page at once: Google Analytics, Matomo, Plausible, Umami.',
					'ghostlabs-cycle-block-lite'
				) }
			</p>

			{ BUY_PRO_URL && (
			<a
				className="mt-6 inline-block rounded-full border-2 border-primary-400 bg-white px-12 py-2.5 text-xl font-semibold text-primary-500 shadow-md ring-2 ring-white transition hover:text-primary-400 focus-visible:ring-4 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-700"
				href={ BUY_PRO_URL }
				target="_blank"
				rel="noopener noreferrer"
			>
				{ __( 'See Cycle Block Pro', 'ghostlabs-cycle-block-lite' ) }
				{ }
				<span className="sr-only">
					{ ' ' }
					{ __( '(opens in a new tab)', 'ghostlabs-cycle-block-lite' ) }
				</span>
				<span aria-hidden="true"> →</span>
			</a>
			) }
		</div>
	</section>
);

export default HeroBand;
