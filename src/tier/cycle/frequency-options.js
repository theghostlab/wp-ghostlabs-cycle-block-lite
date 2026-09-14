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
	proSchedule,
} from '@shared/blocks/cycle/components/frequency/intervals';

const FREE_INTERVALS = [
	proSchedule( ANNUALLY ),
	proSchedule( QUARTERLY ),
	MONTHLY,
	proSchedule( BI_WEEKLY ),
	WEEKLY,
	DAILY,
	proSchedule( HOURLY ),
	proSchedule( EVERY_MINUTE ),
	proSchedule( INSTANTLY ),
];

export default FREE_INTERVALS;
