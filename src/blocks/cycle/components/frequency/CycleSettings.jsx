import {__} from '@wordpress/i18n';
import {Flex, FlexBlock, Panel, PanelRow, SelectControl} from '@wordpress/components';
import {useState} from '@wordpress/element';

const frequencyOptions = () => {

    const selections = [ ...(typeof b === 'object' ? b : []), {
        label: __("Daily", 'theghostlab'),
        value: '1-d'
    },{
        label: __("Weekly", 'theghostlab'),
        value: '7-d'
    }];

    return selections;
}

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
                            label={__("Cycle", 'theghostlab')}
                            value={ cycle }
                            options={ frequencyOptions() }
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
                            help={__(`Set when to display another variation.`, 'theghostlab')}
                        />
                    </FlexBlock>
                </Flex>
            </PanelRow>
        </Panel>
    )
}