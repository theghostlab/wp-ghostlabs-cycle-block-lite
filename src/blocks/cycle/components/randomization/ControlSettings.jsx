import { __ } from '@wordpress/i18n';
import {
    __experimentalHStack as HStack,
    Panel,
    PanelRow,
    __experimentalText as Text,
} from '@wordpress/components';
import ShuffleToggle from "./_components/ShuffleToggle";
import ShuffleIcon from "./_components/ShuffleIcon";

import RepeatToggle from "./_components/RepeatToggle";
import PreviewControl from "./_components/PreviewControl";

const messages = {
    random: __('Variations will be shuffled at the beginning of each cycle. Cycle will finish on the last variation.', 'ghostlabs-cycle-block-lite'),
    randomRepeat: __('Variations will be shuffled at the beginning of each cycle. Cycle will repeat indefinitely.', 'ghostlabs-cycle-block-lite'),
    manual: __('Variations will cycle in sequence.  Cycle will finish on the last variation.', 'ghostlabs-cycle-block-lite'),
    manualRepeat: __('Variations will cycle in sequence. Cycle will repeat indefinitely.', 'ghostlabs-cycle-block-lite'),
}

const help = {
    random: messages.random,
    'random-repeat': messages.randomRepeat,
    'repeat-random': messages.randomRepeat,
    manual: messages.manual,
    'manual-repeat': messages.manualRepeat,
    'repeat-manual': messages.manualRepeat,
}

const setControlHelp = (x,y) => {

    if( x === 'random' && y ) {
        return `random-repeat`;
    }

    if( x === 'random' && !y ) {
        return 'random'
    }

    if( x === 'manual' && y ) {
        return `manual-repeat`;
    }

    if( x === 'manual' && !y ) {
        return `manual`
    }
}

export default ({attributes, blockId, setAttributes, parentBlockId, previewLink}) => {

    const {
        randomize,
        repeat,
    } = attributes;

    const postId = wp.data.select("core/editor").getCurrentPostId();

    return (
        <Panel>
            <PanelRow>
                <div className={'theghostlab-cycle-controls'}>
                    <Text
                        upperCase={true}
                        weight={500}
                        size={'11px'}
                        lineHeight={'1.4'}
                        className={'theghostlab-cycle-controls__label'}
                    >{__('Controls', 'ghostlabs-cycle-block-lite')}</Text>

                    <HStack
                        jusify={'space-between'}
                    >
                        <ShuffleToggle
                            key={'toggle-shuffle'}
                            label={__('Shuffle', 'ghostlabs-cycle-block-lite')}
                            icon={ShuffleIcon}
                            value={randomize.setting === 'random'}
                            callback={value => {

                                setAttributes({
                                    previousSettings: {
                                        ...attributes.previousSettings,
                                        randomize: !value
                                    },
                                    randomize: {
                                        ...attributes.randomize,
                                        setting: value ? 'random' : 'manual',
                                    }
                                });
                            }}
                        />

                        <RepeatToggle
                            key={'toggle-repeat'}
                            attributes={attributes}
                            setAttributes={setAttributes}
                        />
                    </HStack>

                    <Text
                        variant={"muted"}
                        size={'12px'}
                        lineHeight={'1.5'}
                    >{help[setControlHelp(randomize.setting, repeat)]}</Text>

                    <PreviewControl
                        blockId={blockId}
                        postId={postId}
                        parentBlockId={parentBlockId}
                        previewLink={previewLink}
                    />
                </div>
            </PanelRow>
        </Panel>
    )
}