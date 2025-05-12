import {ROUTE_NAMESPACE} from "../../../javascript/constants";
import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {useEffect} from "@wordpress/element";
import {BlockControls, InnerBlocks, InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, Toolbar, ToolbarGroup} from '@wordpress/components';
import {ulid} from 'ulid';
import { debounce, memoize } from '../../../javascript/utils';
import ToolbarNav from './components/toolbar-nav/ToolbarNav';
import CycleSettings from "./components/frequency/CycleSettings";
import ControlSettings from "./components/randomization/ControlSettings";
import {enumerateEntriesFromLogs} from "./helpers/list-view-helper";
import {isBlockIdReserved} from "../../blockUtils";
import {
    contentPreview,
    entrySelectedArgs,
    filterEntriesStatus,
    generateContentPreview,
    haveEntriesBeenAdded,
    insertContentEntryBlock,
    setEntryLabel,
    trackLastSelected,
} from "./base";

const ROUTES = {
    UPDATE_ON_ENTRY_CHANGE: `${ROUTE_NAMESPACE}/update-on-entry-change`,
    UPDATE_BLOCK: `${ROUTE_NAMESPACE}/update-block`
}

const store = new Map();

const params = new URLSearchParams(window.location.search);

const memoizeSetEntry = debounce(memoize(entrySelectedArgs, ([attributes,postId]) => {

    wp.apiRequest({
        path: ROUTES.UPDATE_ON_ENTRY_CHANGE,
        data:{
            postId,
            blockId: attributes.blockId,
            currentId: attributes.currentId,
            startingPosition: store.get('startingPosition'),
        },
        type: 'POST',
    })
        .then( ({currentId}) => {
            store.set('pastEntryId', currentId)
        })
        .catch((error, statusTest) => {
            console.error(statusTest);
            console.error(error);
        })

}), 100);

window.addEventListener("paste", (e) => {

    const test = wp.blocks.parse(e.clipboardData.getData("text"));

    if( test && Array.isArray(test) && test.length ) {

        const tmp = test.filter( x => x.name === 'theghostlab/cycle')
            .map( x => x.attributes.blockId);

        store.set('pastedBlockIds', tmp);

    } else {
        store.set('pastedBlockIds', null);
    }
});

const generateEntries = ({childBlocks, entryIds}) => {
    return childBlocks
        .filter(({attributes}) => entryIds.includes(attributes.id))
        .map(({attributes, clientId}, index) => ({
            id: attributes.id,
            clientId,
            preview: generateContentPreview(contentPreview(clientId)) ?? attributes.id,
            label: attributes?.metadata?.name?.length ? attributes.metadata.name : setEntryLabel(clientId, index),
            ...(Object.keys(attributes.utmCodes).length && {utmCodes: attributes.utmCodes}),
            index,
        }));
}

const handleEntries = ({attributes, entries, entriesStatus, postId, setAttributes}) => {
    if(!entries.length) return;

    const entriesStatusFiltered = haveEntriesBeenAdded({store, entries, entriesStatus, trackLastSelected});

    setAttributes({
        entries,
        entriesStatus: entriesStatusFiltered,
        currentId: entries[0]['id'],
        pastEntryId: entries[0]['id']
    })

    store.set('currentId', entries[0]['id'])
    store.set('pastEntryId', entries[0]['id']);
    store.set('entryLength', entries.length);

    memoizeSetEntry(attributes,postId)
}

export default function edit({ attributes, clientId, setAttributes, isSelected}) {

    const coreEditor = wp.data.select( 'core/editor' );

    const {
        blockId,
        entries,
        entriesStatus,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'theghostlab-cycle',
    });

    const childBlocks = useSelect(
        select => select( 'core/block-editor' ).getBlocks( clientId )
    );

    const postId = useSelect(select =>
        select("core/editor").getCurrentPostId()
    );

    const selectedBlock = useSelect(select => select( 'core/block-editor' ).getSelectedBlockClientId());

    useEffect( () => {

        store.set("startingPosition", attributes.currentId)

        setAttributes({update: { ...attributes.update, on: wp.date.getDate().toISOString() }});

    },[]);

    useEffect( () => {

        if( !blockId ) {
            setAttributes({blockId: ulid()});
            return;
        }

        if ( isBlockIdReserved( blockId ) ) {

            setAttributes({blockId: ulid()});

            const newBlock = wp.data.select( 'core/block-editor' ).getBlock( clientId );

            const newCurrentId = newBlock.innerBlocks[0].attributes.id

            wp.apiRequest({
                path: ROUTES.UPDATE_BLOCK,
                data:{
                    postId,
                    blockId: newBlock.attributes.blockId,
                    entryId: newCurrentId,
                },
                type: 'POST',
            }).catch( (error,statusText) => {
                console.error(statusText)
                console.error(error)
            })
        }

    },[blockId])

    useEffect( () => {

        if( !store.has('hasSingleVariation') ) {
            store.set('hasSingleVariation', 0);
        }

        if( !store.has('isSingleVariationSelected') ) {
            store.set('isSingleVariationSelected', false);
        }

        if( isSelected ) {
            const childBlocks = wp.data.select( 'core/block-editor' ).getBlocks( clientId )

            store.set('hasSingleVariation', childBlocks.length)

            if( store.has('hasSingleVariation') === 1 && !store.get('isSingleVariationSelected') ) {
                wp.data.dispatch( 'core/block-editor' ).selectBlock(childBlocks[0].clientId)
                store.set('isSingleVariationSelected', true)
            }

        } else {
            const hasInnerBlockSelected = wp.data.select( 'core/block-editor' ).hasSelectedInnerBlock(clientId)

            if( hasInnerBlockSelected ) {
                store.set('isSingleVariationSelected', true);
            } else {
                store.set('hasSingleVariation', 0);
                store.set('isSingleVariationSelected', false);
            }
        }

    },[isSelected]);

    useEffect( () => {

        const entriesStatusFiltered = filterEntriesStatus({selectedBlock, entries, entriesStatus});
        setAttributes({entriesStatus:entriesStatusFiltered});

    },[selectedBlock]);

    useEffect( () => {

        const pastedBlockIds = store.get('pastedBlockIds');

        if( Array.isArray(pastedBlockIds) && pastedBlockIds.length ) {

            const check = pastedBlockIds.some( x => x === attributes.blockId);

            if(check) {

                setAttributes({blockId: ulid()});
                setAttributes({update: {...attributes.update, on: wp.date.getDate().toISOString()}});

                store.set('pastedBlockIds', null);
            }
        }

    },[store.get('pastedBlockIds')]);

    useEffect( () => {

        const childBlocks = wp.data.select( 'core/block-editor' ).getBlocks( clientId )

        if( !childBlocks.length ) return;

        const entryIds = childBlocks.map( ({attributes}) => attributes.id);

        const entries = generateEntries({
            childBlocks,
            entryIds,
        })

        handleEntries({
            attributes,
            entries,
            entriesStatus,
            postId,
            setAttributes,
        })

    },[childBlocks]);

    /**
     * @see LogComponent.jsx
     * @description find "entryQueryStringParameter"
     */
    useEffect( () => {

        if (params.has('cycle-entry'))
        {
            const entryId = params.get("cycle-entry");

            if( !entryId ) return;

            const childBlock = childBlocks.find( ({attributes}) => attributes.id === entryId)

            if( !childBlock?.clientId ) return;

            wp.data.dispatch("core/block-editor").selectBlock(childBlock.clientId);

            enumerateEntriesFromLogs(clientId);
        }

    },[params]);

    return ([
        <BlockControls
            key={`theghostlab-cycle-toolbar-${blockProps['data-block']}`}
        >
            <Toolbar
                label="Options"
                className={`theghostlab-cycle-toolbar`}
            >
                <ToolbarGroup>
                    <ToolbarNav
                        entries={entries}
                        childBlocks={childBlocks}
                        parentId={clientId}
                    />
                </ToolbarGroup>


            </Toolbar>


        </BlockControls>,
        <InspectorControls
            key={`inspector-controls-${blockProps['data-block']}`}
        >
            <PanelBody title={__("Settings", 'theghostlab')} opened={true}>

                <CycleSettings
                    key={`cycle-settings-${blockProps['data-block']}`}
                    attributes={attributes}
                    setAttributes={setAttributes}
                />

                <ControlSettings
                    key={`cycle-controls-${blockProps['data-block']}`}
                    attributes={attributes}
                    clientId={clientId}
                    setAttributes={setAttributes}
                    parentBlockId={attributes.blockId}
                    previewLink={coreEditor.getEditedPostPreviewLink()}
                />
            </PanelBody>
        </InspectorControls>,
        <div
            key={`tabber-component-${blockProps['data-block']}`}
            { ...blockProps }
        >
            <InnerBlocks
                allowedBlocks={[
                    'theghostlab/cycle-entry'
                ]}
                template={[
                    [ 'theghostlab/cycle-entry' ],
                ]}
                renderAppender={() => (
                    <button
                        className={"theghostlab-cycle__content-entry-appender components-button block-editor-inserter__toggle has-icon"}
                        onClick={insertContentEntryBlock(clientId)}
                        aria-label={"Add block"}
                        type={'button'}
                        aria-haspopup={true}
                        title={__('Add Variation', 'theghostlab')}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                             aria-hidden="true" focusable="false">
                            <path d="M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"></path>
                        </svg>
                    </button>
                )}
            />
        </div>
    ])
}