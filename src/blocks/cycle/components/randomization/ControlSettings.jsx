import {ROUTE_NAMESPACE} from "../../../../../javascript/constants";
import { __ } from '@wordpress/i18n';
import {
    Button,
    __experimentalHStack as HStack,
    Panel,
    PanelRow,
    __experimentalText as Text,
    Icon
} from '@wordpress/components';
import ShuffleToggle from "./_components/ShuffleToggle";
import ShuffleIcon from "./_components/ShuffleIcon";
import RepeatIcon from "./_components/RepeatIcon";

const ROUTES = {
    CLEAR_TEST_PREVIEW: `${ROUTE_NAMESPACE}/clear-test-preview`
}

const messages = {
    random: __('Variations will be shuffled at the beginning of each cycle. Cycle will finish on the last variation.', 'theghostlab'),
    randomRepeat: __('Variations will be shuffled at the beginning of each cycle. Cycle will repeat indefinitely.', 'theghostlab'),
    manual: __('Variations will cycle in sequence.  Cycle will finish on the last variation.', 'theghostlab'),
    manualRepeat: __('Variations will cycle in sequence. Cycle will repeat indefinitely.', 'theghostlab'),
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

const appendParamsToUrl = (url, params) => {
    const urlObject = new URL(url);

    // Iterate through the params object and append them to URLSearchParams
    for (const [key, value] of Object.entries(params)) {
        urlObject.searchParams.append(key, value);
    }

    return urlObject.toString();
}

export default ({attributes, clientId, setAttributes, parentBlockId, previewLink}) => {

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
                    >Controls</Text>

                    <HStack
                        jusify={'space-between'}
                    >
                        <ShuffleToggle
                            key={'toggle-shuffle'}
                            label={'Shuffle'}
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

                        <ShuffleToggle
                            key={'toggle-repeat'}
                            label={'Repeat'}
                            icon={RepeatIcon}
                            value={repeat}
                            callback={value => {

                                setAttributes({
                                    previousSettings: {
                                        ...attributes.previousSettings,
                                        repeat
                                    },
                                    repeat: value,
                                });
                            }}
                        />
                    </HStack>

                    <Text
                        variant={"muted"}
                        size={'12px'}
                        lineHeight={'1.5'}
                    >{help[setControlHelp(randomize.setting, repeat)]}</Text>

                    <Button
                        variant={'secondary'}
                        style={{width: '100%'}}
                        onMouseDown={() => {

                            wp.apiRequest({
                                path: ROUTES.CLEAR_TEST_PREVIEW,
                                data:{
                                    postId,
                                    clientId,
                                },
                                type: 'POST',
                            })
                                .then( () => wp.data.dispatch( 'core/editor' ).savePost() )
                                .catch((error, statusTest) => {
                                    console.error(statusTest);
                                    console.error(error);
                                })
                        }}
                        onClick={() =>{
                            window.open(
                                appendParamsToUrl(previewLink, {
                                    preview: true,
                                    ghostlabs_testing: parentBlockId,
                                    _ghostlabs_nonce: theghostlab_cycle_vars.nonce
                                }),
                                '_blank',
                                'noopener,noreferrer'
                            )
                        }}
                    >
                        <div style={{
                            display: 'flex',
                            alignItems: `center`,
                            justifyContent: 'center',
                            width: '100%',
                            gap: '.35rem'
                        }}>
                            <span>{__('Preview content cycling', 'theghostlab')}</span>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fillRule="evenodd" clipRule="evenodd"
                                      d="M5 6a1 1 0 011-1h4a1 1 0 100-2H6a3 3 0 00-3 3v12a3 3 0 003 3h12a3 3 0 003-3v-4a1 1 0 10-2 0v4a1 1 0 01-1 1H6a1 1 0 01-1-1V6zm10-3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 001.414 1.414L19 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"
                                      fill="currentColor"/>
                            </svg>
                        </div>
                    </Button>
                    <Text
                        variant={"muted"}
                        lineHeight={'18px'}
                        size={'12px'}
                    >
                        {__(`In the preview, the cycle interval is disabled, which lets you refresh the page to cycle through variations.`, 'theghostlab')}
                    </Text>
                </div>
            </PanelRow>
        </Panel>
    )
}