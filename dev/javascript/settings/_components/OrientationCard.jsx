const { __ } = window.wp.i18n;

const OrientationCard = () => (
	<section
		data-gl-orientation
		className="rounded-[10px] border border-gray-200 bg-white px-7 py-6"
	>
		{ }
		<h2 className="text-base font-semibold text-gray-900">
			{ __( 'Where your settings live', 'ghostlabs-cycle-block-lite' ) }
		</h2>

		<p className="mt-2 max-w-[900px] text-sm leading-relaxed text-gray-500">
			{ __(
				'Every setting lives on the block itself. Add a Cycle block to a post or page, and the schedule, shuffle, order and repeat controls are in the block sidebar — there is nothing to configure here.',
				'ghostlabs-cycle-block-lite'
			) }
		</p>

		<p className="mt-2.5 max-w-[900px] text-sm leading-relaxed text-gray-500">
			{ __(
				'Cycle Block Pro adds per-variation analytics — what it includes is below.',
				'ghostlabs-cycle-block-lite'
			) }
		</p>
	</section>
);

export default OrientationCard;
