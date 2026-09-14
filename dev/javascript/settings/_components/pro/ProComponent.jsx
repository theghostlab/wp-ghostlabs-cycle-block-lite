import HeroBand from './HeroBand';
import ComparisonTable from './ComparisonTable';

const { __ } = window.wp.i18n;

const BUY_PRO_URL =
	window.GHOSTLABS_CYCLE_BLOCK_CONFIG?.upgrade?.urls?.[ 'settings-table' ];

const ProComponent = () => (
	<>
		<HeroBand />

		<div
				data-gl-card
				className="overflow-hidden rounded-[10px] border border-gray-200 bg-white shadow-[0_12px_32px_rgba(44,26,67,0.08)]"
			>
				<ComparisonTable />

				<div className="flex items-center justify-between gap-6 border-t border-gray-100 bg-gray-50 px-8 py-5">
					<div>
						<p className="text-sm font-semibold text-gray-900">
							{ __(
								'Ready when you are',
								'ghostlabs-cycle-block-lite'
							) }
						</p>
						<p className="mt-0.5 text-sm text-gray-500">
							{ __(
								'Pricing and the full feature tour live on the product page.',
								'ghostlabs-cycle-block-lite'
							) }
						</p>
					</div>

					{ BUY_PRO_URL && (
					<a
						data-gl-table-cta
						className="shrink-0 rounded-md bg-primary-500 px-6 py-3 text-base font-semibold text-white transition hover:bg-primary-400"
						href={ BUY_PRO_URL }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __(
							'See Cycle Block Pro',
							'ghostlabs-cycle-block-lite'
						) }
						{ }
						<span className="sr-only">
							{ ' ' }
							{ __(
								'(opens in a new tab)',
								'ghostlabs-cycle-block-lite'
							) }
						</span>
						<span aria-hidden="true"> →</span>
					</a>
					) }
				</div>
		</div>
	</>
);

export default ProComponent;
