import {__} from '@wordpress/i18n';
import {Flex, FlexBlock, Panel, PanelRow, SelectControl} from '@wordpress/components';
import {intervalOptions} from './intervals';

import tierIntervals from "@tier/cycle/frequency-options";

import ScheduleUpsell from "@tier/cycle/schedule-upsell";

const parseCycle = value => value.split('-');

const storedCycle = ({interval, frequency}) => `${interval}-${frequency}`;

const UNSET = '';

export default ({attributes, setAttributes}) => {

    const stored = storedCycle(attributes.update);

    const known = tierIntervals.some(interval => interval.value === stored);

    const options = known
        ? intervalOptions(tierIntervals)
        : [
            {label: __("Choose a schedule", 'ghostlabs-cycle-block-lite'), value: UNSET, disabled: true},
            ...intervalOptions(tierIntervals)
        ];

    return (
        <Panel className="theghostlab-cycle-schedule">
            <PanelRow>
                <Flex>
                    <FlexBlock>
                        <SelectControl
                            __next40pxDefaultSize
                            label={__("Cycle", 'ghostlabs-cycle-block-lite')}
                            value={ known ? stored : UNSET }
                            options={ options }
                            onChange={ value => {
                                if ( value === UNSET ) return;

                                const [i, f] = parseCycle(value);

                                setAttributes({
                                    update: {
                                        ...attributes.update,
                                        frequency: f,
                                        interval: parseInt(i),
                                        date: Date.now()
                                    }
                                });
                            }}
                            help={ known
                                ? __("Set when to display another variation.", 'ghostlabs-cycle-block-lite')
                                : __("This block is not rotating. Choose a schedule to start it.", 'ghostlabs-cycle-block-lite')
                            }
                        />
                    </FlexBlock>
                </Flex>
            </PanelRow>
            { }
            <ScheduleUpsell />
        </Panel>
    )
}
