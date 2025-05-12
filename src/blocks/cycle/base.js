import { __ } from '@wordpress/i18n';
import { createBlock } from "@wordpress/blocks";
import { Fragment } from "@wordpress/element";
import { ulid } from "ulid";
import { memoize } from "../../../javascript/utils";

const contentPreview = clientId => {
    const child = document.body.querySelector(`#block-${clientId}`);

    if( !child ) return null;

    return child.textContent;
}

const entrySelectedArgs = args => args;

const filterEntriesStatus = ({selectedBlock,entries,entriesStatus}) => {

    let cycleEntry = wp.data.select( 'core/block-editor' ).getBlock( selectedBlock );
    const cycleEntries =  wp.data.select( 'core/block-editor' ).getBlockParentsByBlockName(selectedBlock, 'theghostlab/cycle-entry');

    if(cycleEntries.length) {
        cycleEntry = wp.data.select( 'core/block-editor' ).getBlock( cycleEntries[0] );
    }

    const tmp = trackLastSelected(entries, entriesStatus, cycleEntry);
    return tmp.filter(entryStatus => entries.some(entry => entry.id === entryStatus.id));
}

const generateContentPreview = text => (<Fragment><h5>{text}</h5></Fragment>);

const haveEntriesBeenAdded = ({store, entries, entriesStatus, trackLastSelected}) => {
    const previousEntryLength = store.get('entryLength');
    let entriesStatusFiltered = entriesStatus;

    if( entries.length > previousEntryLength ) {
        const lastEntry = entries[entries.length - 1];
        const cycleEntry = wp.data.select( 'core/block-editor' ).getBlock(lastEntry.clientId)
        const tmp = trackLastSelected(entries, entriesStatus, cycleEntry);
        entriesStatusFiltered = tmp.filter(entryStatus => entries.some(entry => entry.id === entryStatus.id));
    }

    return entriesStatusFiltered;
}

const insertContentEntryBlock = clientId => () => {

    const innerCount = wp.data.select("core/block-editor").getBlocksByClientId(clientId)[0].innerBlocks.length;
    const block = createBlock("theghostlab/cycle-entry");

    wp.data.dispatch("core/block-editor").insertBlock(block, innerCount, clientId);

    const randomizer = document.getElementById(`block-${clientId}`)
    randomizer.scrollIntoView({ behavior: "smooth", block: "start"});
}

const setEntryLabel = (clientId, index) => __(`Variation ${(index+1)}`,'theghostlab');

const trackLastSelected = (entries, entriesStatus, cycleEntry) => {

    if( !cycleEntry ) return entriesStatus;

    const { id } = cycleEntry.attributes;

    const check = entries.map(entry => entry.id).some( x => x === id);

    if( !check ) return entriesStatus;

    const newObj = {id, lastSelected: true};

    // let updatedArray = [];
    let updatedArray = [...entriesStatus];

    if( !updatedArray.length ) {

        const entryExists = entries.find(entry => entry.id === id);

        newObj.id = entryExists.id;

        updatedArray.push(newObj);

        return updatedArray;
    }

    for (const index in updatedArray) {

        const exists = updatedArray.find(x => x.id === id);

        if( !exists ) {
            updatedArray.push(newObj);
            updatedArray[index].lastSelected = false;
        } else {
            updatedArray[index].lastSelected = updatedArray[index].id === id;
        }
    }

    return updatedArray;
}

const setUniqIdsForEntries = parentClientId => {

    const newEntryIds = [];

    const siblingBlockIds = wp.data.select( 'core/block-editor' )
        .getBlocks( parentClientId )
        .map( ({attributes, clientId}, index) => ({clientId, id: attributes.id, index}));

    siblingBlockIds.forEach( ({clientId}) => {

        const newEntryId = ulid();
        wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(clientId,{
            id: newEntryId
        })

        newEntryIds.push(newEntryId)
    });

    return newEntryIds;
}

const uniqueSiblingIds = (clientId, blockId, setAttributes, name, callback = null) => {

    const dom = document.body.querySelector(`#block-${clientId}`);
    if( !dom ) return;

    const rootContainer = dom.closest('.is-root-container');
    console.log("=>(base.js:127) rootContainer", rootContainer);

    if( !rootContainer ) return;

    const config = { attributes: true, childList: true, subtree: true };

    let hasChildren = false;

    const checkHasChildren = memoize(hasChildren => hasChildren);

    const _callback = mutationList => {

        for (const mutation of mutationList) {

            if (mutation.type === "childList") {
                hasChildren = true;
            }
        }

        const check = checkHasChildren(hasChildren)?.[0];

        if( check ) {
            const randomizers = rootContainer.querySelectorAll(`[data-type="${name}"]`);
            const randomizerClientIds = Array.from(randomizers).map( obj => obj.dataset.block);
            const randomizerAttributes = randomizerClientIds
                .map( randomizerClientId => ({clientId: randomizerClientId, attributes: wp.data.select('core/block-editor').getBlockAttributes( randomizerClientId )}) )
                .map( ({attributes, clientId}, index) => ({blockId: attributes.blockId, clientId, index}));

            const notSelf = randomizerAttributes.filter( sibling => sibling.clientId !== clientId );
            const isSelf = randomizerAttributes.find( sibling => sibling.clientId === clientId )

            notSelf.forEach( sibling => {

                if( sibling.blockId === blockId ) {

                    if( isSelf?.index > sibling.index ) {
                        const newBlockId = ulid();
                        console.log("=>(base.js:163) newBlockId", newBlockId);
                        setAttributes({blockId: newBlockId});

                        const newEntryIds = setUniqIdsForEntries(sibling.clientId);

                        if( typeof callback === 'function' ) {
                            callback(newBlockId, newEntryIds)
                        }
                    }
                }
            });

            hasChildren = false;
            observer.disconnect();
        }
    }

    const observer = new MutationObserver(_callback);

    observer.observe(rootContainer, config);
}

export {
    contentPreview,
    entrySelectedArgs,
    filterEntriesStatus,
    generateContentPreview,
    haveEntriesBeenAdded,
    insertContentEntryBlock,
    setEntryLabel,
    trackLastSelected,
    uniqueSiblingIds
};