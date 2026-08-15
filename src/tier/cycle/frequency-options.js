import {
	ANNUALLY,
	QUARTERLY,
	MONTHLY,
	BI_WEEKLY,
	WEEKLY,
	DAILY,
	HOURLY,
	EVERY_MINUTE,
	INSTANTLY,
	proOnly,
} from '@shared/blocks/cycle/components/frequency/intervals';

/**
 * This edition's answer to `@tier/cycle/frequency-options`.
 *
 * The three schedules it honours — Daily, Weekly, Monthly — with the rest named
 * beside them, disabled and marked as belonging to the paid edition. Showing
 * what sits behind the wall converts better than hiding it, and besides the
 * settings screen this is the only place that can.
 *
 * Additive against what this listing already published, which offered Daily and
 * Weekly and nothing else: Monthly is new there, and no existing install can
 * hold a value this list drops.
 *
 * **The full range in order, longest to shortest, with the honoured three in
 * their true places rather than grouped.** An author reads one coherent range
 * and can see exactly where their options sit inside it — that Monthly is as
 * slow as they go and Daily as fast. Segregating the locked entries into a block
 * at the end would read as a feature list rather than as the control it is.
 *
 * `block.json` defaults the attribute to `7-d`, which is enabled here. A default
 * landing on a disabled entry would render the select showing a schedule the
 * edition refuses to honour.
 */
const FREE_INTERVALS = [
	proOnly( ANNUALLY ),
	proOnly( QUARTERLY ),
	MONTHLY,
	proOnly( BI_WEEKLY ),
	WEEKLY,
	DAILY,
	proOnly( HOURLY ),
	proOnly( EVERY_MINUTE ),
	proOnly( INSTANTLY ),
];

export default FREE_INTERVALS;
