import { createRoot } from 'react-dom/client';
import OrientationCard from './_components/OrientationCard';
import ProComponent from './_components/pro/ProComponent';
import UpgradePanel from './_components/upgrade/UpgradePanel';

const { __ } = window.wp.i18n;

const { rootElementId, upgrade } = window.GHOSTLABS_CYCLE_BLOCK_CONFIG;

const SettingsScreen = () => (
	<div id="ghostlabs">
		{ }
		<div className="flex items-center gap-3 bg-white px-10 pb-4 pt-[22px]">
			<h1 className="text-[22px] font-bold leading-tight text-[#1d2327]">
				{ __( 'Cycle Block', 'ghostlabs-cycle-block-lite' ) }
			</h1>
			{ }
			<span className="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-600">
				{ __( 'Free', 'ghostlabs-cycle-block-lite' ) }
			</span>
		</div>

		{ }
		{ }
		<div
			data-gl-body
			className="mt-5 flex max-w-[920px] flex-col gap-2 px-10 pb-12"
		>
			<OrientationCard />

			{ }
			<UpgradePanel
				accountUrl={ upgrade?.accountUrl }
				uploadUrl={ upgrade?.uploadUrl }
			/>

			<ProComponent />
		</div>
	</div>
);

document.addEventListener( 'DOMContentLoaded', () => {
	const root = document.getElementById( rootElementId );

	if ( root ) {
		createRoot( root ).render( <SettingsScreen /> );
	}
} );
