import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {useEffect, useRef} from "@wordpress/element";
import {BlockControls, InnerBlocks, InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, Toolbar, ToolbarGroup} from '@wordpress/components';
import {ulid} from 'ulid';
import { debounce, memoize } from '../../../dev/javascript/utils';
import apiFetch from '@wordpress/api-fetch';
import ToolbarNav from './components/toolbar-nav/ToolbarNav';
import CycleSettings from "./components/frequency/CycleSettings";
import ControlSettings from "./components/randomization/ControlSettings";
import {isBlockIdReserved} from "../../blockUtils";
import {
    entrySelectedArgs,
    filterEntriesStatus,
    haveEntriesBeenAdded,
    insertContentEntryBlock,
    setEntryLabel,
    trackLastSelected,
} from "./base";

const store = new Map();

const params = new URLSearchParams(window.location.search);

const entrySelected = (attributes,postId) => async () => {

    const obj = {};

    for (const [key, value] of store) {
        obj[key] = value;
    }

    return {
        blockId: attributes.blockId,
        postId,
        ...obj
    };
}

const memoizeSetEntry = debounce(memoize(entrySelectedArgs, ([attributes,postId]) => {

    const selectEntry = entrySelected(attributes,postId);

    selectEntry(postId)
        .then(data => apiFetch({
            path: `/${theghostlab_cycle_vars.restNamespace}/block/entry-change`,
            method: 'POST',
            data,
        }))
        // .then( ({currentId}) => {
        //     store.set('pastEntryId', currentId)
        // })
        .then( response => {

            if( response?.status ) {
                wp.data.dispatch( 'core/editor' ).savePost();
                store.set('startingPosition', response.status);
            }

            //
        })
        .catch(err => {
            console.error("-> err", err);
        })

}), 100);

const capturePastedCycleBlocks = (e) => {
    // console.log("PASTED!!!", e.clipboardData.getData("text"));

    const test = wp.blocks.parse(e.clipboardData.getData("text"));

    if( test && Array.isArray(test) && test.length ) {

        const tmp = test.filter( x => x.name === 'theghostlab/cycle')
            .map( x => x.attributes.blockId);

        store.set('pastedBlockIds', tmp);

    } else {
        store.set('pastedBlockIds', null);
    }
};

const pasteWiredWindows = new WeakSet();

/**
 * Paste events fire in the window the caret lives in — under apiVersion 3 that
 * is the editor-canvas iframe, not the parent frame this module runs in. The
 * parent window is wired at load; each mounted cycle block wires its own
 * canvas window, so copy-pasting a cycle block within a post keeps being
 * detected. (A paste into a post that holds no cycle block yet has no wired
 * canvas — but then there is no sibling to collide with either.)
 */
const watchPaste = (win) => {
    if( !win || pasteWiredWindows.has(win) ) return;

    pasteWiredWindows.add(win);
    win.addEventListener("paste", capturePastedCycleBlocks);
};

watchPaste(window);

// Only serializable content belongs here — entries[] is saved into post
// content. clientId (an editor-session handle) and preview (a React element,
// which serialized as JSON junk and, on a failed validation, captured the
// literal error text permanently) used to ride along; nothing ever read them.
const generateEntries = ({childBlocks, entryIds}) => {
    return childBlocks
        .filter(({attributes}) => entryIds.includes(attributes.id))
        .map(({attributes, clientId}, index) => ({
            id: attributes.id,
            label: attributes?.metadata?.name?.length ? attributes.metadata.name : setEntryLabel(clientId, index),
            index,
        }));
}

const handleEntries = ({attributes, entries, entriesStatus, childBlocks, postId, setAttributes}) => {
    if(!entries.length) return;

    const entriesStatusFiltered = haveEntriesBeenAdded({store, entries, entriesStatus, childBlocks, trackLastSelected});

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

export default function edit({ attributes, clientId, setAttributes, name, isSelected}) {

    const coreEditor = wp.data.select( 'core/editor' );

    const {
        blockId,
        entries,
        entriesStatus,
    } = attributes;

    const blockRef = useRef(null);

    const blockProps = useBlockProps({
        className: 'theghostlab-cycle',
        ref: blockRef,
    });

    // The block renders into the canvas document; its window is where paste
    // events for the canvas fire. See watchPaste above.
    useEffect(() => {
        watchPaste(blockRef.current?.ownerDocument?.defaultView);
    }, []);

    const childBlocks = useSelect(
        select => select( 'core/block-editor' ).getBlocks( clientId )
    );

    const postId = useSelect(select =>
        select("core/editor").getCurrentPostId()
    );

    const selectedBlock = useSelect(select => select( 'core/block-editor' ).getSelectedBlockClientId());

    useEffect( () => {

        // console.warn("NEW BLOCK")
        //
        // if( !attributes.blockId ){
        //     setAttributes({blockId: ulid()});
        //
        //     console.warn("NEW BLOCK ID!!!!")
        // }

        store.set("startingPosition", attributes.currentId)

        setAttributes({update: { ...attributes.update, on: wp.date.getDate().toISOString() }});

    },[]);

    useEffect( () => {

        if( !blockId ) {
            setAttributes({blockId: ulid()});
            return;
        }

        if ( isBlockIdReserved( blockId ) ) {
            // eslint-disable-next-line no-console
            // console.log( `Block with id '${ blockId }' already exists. Regenerating...`, blockId );
            setAttributes({blockId: ulid()});
            // console.log( `Regenerating...`, blockId );

            const newBlock = wp.data.select( 'core/block-editor' ).getBlock( clientId );

            // const childBlocks = wp.data.select( 'core/block-editor' ).getBlocks( clientId )

            const newCurrentId = newBlock.innerBlocks[0].attributes.id
            // console.log("=>(edit.js:177) newCurrentId", newCurrentId);
            // console.log("=>(edit.js:175) newBlock", newBlock);
            // console.log("=>(edit.js:175) childBlocks", childBlocks);

            apiFetch({
                path: `/${theghostlab_cycle_vars.restNamespace}/block/update`,
                method: 'POST',
                data: {
                    blockId: newBlock.attributes.blockId,
                    entryId: newCurrentId,
                    postId: postId,
                },
            })
                .catch( error => {
                    console.error(error)
                })

            //save.block?
        }

    },[blockId])

    // useEffect( () => {
    //
    //     if( !store.get('hasSingleVariation') ) {
    //         store.set('hasSingleVariation', 0);
    //     }
    //
    //     if( !store.get('isSingleVariationSelected') ) {
    //         store.set('isSingleVariationSelected', false);
    //     }
    //
    //     if( isSelected ) {
    //         const childBlocks = wp.data.select( 'core/block-editor' ).getBlocks( clientId )
    //
    //         store.set('hasSingleVariation', childBlocks.length)
    //
    //         if( store.get('hasSingleVariation') === 1 && !store.get('isSingleVariationSelected') ) {
    //             wp.data.dispatch( 'core/block-editor' ).selectBlock(childBlocks[0].clientId)
    //             store.set('isSingleVariationSelected', true)
    //         }
    //
    //     } else {
    //         const hasInnerBlockSelected = wp.data.select( 'core/block-editor' ).hasSelectedInnerBlock(clientId)
    //
    //         if( hasInnerBlockSelected ) {
    //             store.set('isSingleVariationSelected', true);
    //         } else {
    //             store.set('hasSingleVariation', 0);
    //             store.set('isSingleVariationSelected', false);
    //         }
    //     }
    //
    // },[isSelected]);

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
            childBlocks,
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
            wp.data.dispatch('core/edit-post').setIsListViewOpened(true);

            // enumerateEntriesFromLogs(clientId);
        }

    },[params]);

    return ([
        <BlockControls
            key={`theghostlab-cycle-toolbar-${blockProps['data-block']}`}
        >
            <Toolbar
                label={__('Options', 'ghostlabs-cycle-block-lite')}
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
            <PanelBody title={__("Settings", 'ghostlabs-cycle-block-lite')} opened={true}>

                <CycleSettings
                    key={`cycle-settings-${blockProps['data-block']}`}
                    attributes={attributes}
                    setAttributes={setAttributes}
                />

                <ControlSettings
                    key={`cycle-controls-${blockProps['data-block']}`}
                    attributes={attributes}
                    blockId={clientId}
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
                        // One label for name and tooltip — a name that differs
                        // from what sighted users read breaks voice control,
                        // and no aria-haspopup: this inserts directly, it
                        // never opens a popup.
                        aria-label={__('Add variation', 'ghostlabs-cycle-block-lite')}
                        type={'button'}
                        title={__('Add variation', 'ghostlabs-cycle-block-lite')}
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