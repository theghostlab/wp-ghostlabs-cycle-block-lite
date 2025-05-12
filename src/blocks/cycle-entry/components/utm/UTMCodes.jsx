import { __ } from '@wordpress/i18n';
import { Button, Flex, FlexBlock, Panel, PanelBody, PanelRow } from '@wordpress/components';
import AutoComplete from "../autocomplete/AutoComplete";
import UtmSets from "../sets/UtmSets";

const meta = {
    utm_source: {
        label: __('Source','theghostlab'),
        purpose: 'Identifies which site, platform, or source sent the traffic.',
        example: '`google`, `facebook`, `newsletter`',
    },
    utm_medium: {
        label: __('Medium','theghostlab'),
        purpose: 'Identifies the marketing medium used.',
        example: '`cpc` (for pay-per-click), `organic`, `email`, `social`',
    },
    utm_campaign: {
        label: __('Campaign','theghostlab'),
        purpose: ' Identifies the specific campaign or promotion.',
        example: '`summer-sale`, `black-friday`, `launch-2021`',
    },
    utm_term: {
        label: __('Term','theghostlab'),
        purpose: 'Identifies the search terms if used in paid search ads.',
        example: '`running+shoes`',
    },
    utm_content: {
        label: __('Content','theghostlab'),
        purpose: 'Used to differentiate between multiple ads or links that point to the same URL. It\'s useful for A/B testing and content-targeted ads.',
        example: '`logolink` vs. `textlink`',
    },
}

const clearUtmFields = utmCodes => Object.fromEntries(
    Object.entries(utmCodes)
    .map(utmCode => {
        utmCode[1] = ''
        return utmCode;
    })
);

const filterInput = value => {
    // Match all characters that are NOT alphanumeric, _, -, +, or @.
    const regex = /[^a-zA-Z0-9_\-+@]/g;

    // Replace those characters with an empty string.
    return value.replace(regex, '');
}


const UTMCodes = ({setAttributes, utmCodes, utmSet}) => {

    const entries = Object.entries(utmCodes);

    return (
        <PanelBody title={__("UTM Settings", 'theghostlab')} initialOpen={ true }>
            <PanelRow>
                <UtmSets setAttributes={setAttributes} utmCodes={utmCodes} utmSet={utmSet} />
            </PanelRow>
            <PanelRow>
                <Flex>
                    <FlexBlock>
                        {
                            entries.map( entry => ([
                                <AutoComplete
                                    key={`theghostlab-autocomplete-${entry[0]}`}
                                    label={meta[entry[0]].label}
                                    value={utmCodes[entry[0]]}
                                    callback={value => {
                                        setAttributes({
                                            utmCodes: {
                                                ...utmCodes,
                                                [entry[0]]: filterInput(value)
                                            }
                                        })
                                    }}
                                    help={`${meta[entry[0]].purpose} Example: ${meta[entry[0]].example}`}
                                    disabled={true}
                                />,
                            ]))
                        }
                    </FlexBlock>
                </Flex>
            </PanelRow>
            {
                (utmSet)
                && <Panel>
                    <Flex justifyContent={'stretch'}>
                        <FlexBlock>
                            <Button
                                onClick={() => {
                                    setAttributes({
                                        utmCodes: clearUtmFields(utmCodes),
                                        utmSet: 'null'
                                    })
                                }}
                                style={{
                                    display:'flex',
                                    justifyContent: 'center',
                                    width: '100%'
                                }}
                                variant={"secondary"}
                            >
                                {__('Clear','theghostlab')}
                            </Button>
                        </FlexBlock>
                    </Flex>
                </Panel>
            }
        </PanelBody>
    )
}

export default UTMCodes;