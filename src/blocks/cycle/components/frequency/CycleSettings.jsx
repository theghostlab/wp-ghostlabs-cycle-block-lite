import {__} from '@wordpress/i18n';
import {Flex, FlexBlock, Panel, PanelRow, SelectControl} from '@wordpress/components';
import {useEffect, useRef, useState} from '@wordpress/element';
import {intervalOptions, isProSchedule, nearestHonoured, scheduleName} from './intervals';

import tierIntervals from "@tier/cycle/frequency-options";

import ScheduleNotice from "@tier/cycle/schedule-notice";

const REFUSAL_DELAY = 200;

const parseCycle = value => value.split('-');

const storedCycle = ({interval, frequency}) => `${interval}-${frequency}`;

export default ({attributes, setAttributes}) => {

    const stored = storedCycle(attributes.update);

    const running = isProSchedule(stored, tierIntervals)
        ? nearestHonoured(stored, tierIntervals)
        : stored;

    const [pending, setPending] = useState(null);

    const [refused, setRefused] = useState(null);

    const [notice, setNotice] = useState(
        () => isProSchedule(stored, tierIntervals) ? {picked: stored, isStored: true} : null
    );

    const timer = useRef(null);

    useEffect(() => () => clearTimeout(timer.current), []);

    const pointerDriven = useRef(false);

    const releaseFocus = field => {
        if (!pointerDriven.current) return;

        if (!field || document.activeElement !== field) return;

        field.blur();
    };

    const refuse = (value, field) => {
        setPending(value);
        setNotice({picked: value, isStored: false});

        setRefused(null);

        clearTimeout(timer.current);

        timer.current = setTimeout(() => {
            setRefused(value);
            releaseFocus(field);
        }, REFUSAL_DELAY);
    };

    const showingRefused = refused !== null && pending === refused;

    return (
        <Panel className="theghostlab-cycle-schedule">
            <PanelRow>
                <Flex>
                    <FlexBlock
                        onPointerDownCapture={ () => { pointerDriven.current = true; } }
                        onKeyDownCapture={ () => { pointerDriven.current = false; } }
                    >
                        <SelectControl
                            __next40pxDefaultSize
                            label={__("Cycle", 'ghostlabs-cycle-block-lite')}
                            className={ showingRefused ? 'theghostlab-cycle-select--refused' : undefined }
                            value={ pending ?? stored }
                            options={ intervalOptions(tierIntervals, refused ? [refused] : []) }
                            onChange={ (value, extra) => {

                                const field = extra?.event?.target ?? document.activeElement;

                                if ( isProSchedule(value, tierIntervals) ) {
                                    refuse(
                                        value,
                                        field instanceof HTMLSelectElement ? field : null
                                    );

                                    return;
                                }

                                clearTimeout(timer.current);
                                setPending(null);
                                setNotice(null);

                                setRefused(null);

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
                            help={__(`Set when to display another variation.`, 'ghostlabs-cycle-block-lite')}
                        />
                    </FlexBlock>
                </Flex>
            </PanelRow>
            { }
            { notice && (
                <ScheduleNotice
                    picked={ scheduleName(notice.picked, tierIntervals) }
                    running={ scheduleName(running, tierIntervals) }
                    isStored={ notice.isStored }
                    onDismiss={ () => setNotice(null) }
                />
            ) }
        </Panel>
    )
}
