import {__} from '@wordpress/i18n';
import {Flex, FlexBlock, Panel, PanelRow, SelectControl} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {intervalOptions} from './intervals';

// Resolved by webpack's `@tier` alias to the compiling edition's src/tier/.
// Each edition supplies the schedules it sells. Which those are
// sells is a tier question, so the list is tier-supplied rather than filtered
// here against a verdict this component cannot see in every edition.
import tierIntervals from "@tier/cycle/frequency-options";

const parseCycle = value => value.split('-');

export default ({attributes, setAttributes}) => {

    const {
        interval,
        frequency
    } = attributes.update;

    const [cycle, setCycle] = useState(`${interval}-${frequency}`)

    return (
        <Panel>
            <PanelRow>
                <Flex>
                    <FlexBlock>
                        <SelectControl
                            __next40pxDefaultSize
                            label={__("Cycle", 'ghostlabs-cycle-block-lite')}
                            value={ cycle }
                            options={ intervalOptions(tierIntervals) }
                            onChange={ value => {

                                setCycle(value);

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
        </Panel>
    )
}