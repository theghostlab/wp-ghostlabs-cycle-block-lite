import { __, sprintf } from '@wordpress/i18n';
import { createBlock } from "@wordpress/blocks";
import { blockNode } from "../../blockUtils";

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

const haveEntriesBeenAdded = ({store, entries, entriesStatus, childBlocks, trackLastSelected}) => {
    const previousEntryLength = store.get('entryLength');
    let entriesStatusFiltered = entriesStatus;

    if( entries.length > previousEntryLength ) {
        const lastEntry = entries[entries.length - 1];
        const cycleEntry = childBlocks.find( ({attributes}) => attributes.id === lastEntry.id );
        const tmp = trackLastSelected(entries, entriesStatus, cycleEntry);
        entriesStatusFiltered = tmp.filter(entryStatus => entries.some(entry => entry.id === entryStatus.id));
    }

    return entriesStatusFiltered;
}

const insertContentEntryBlock = clientId => () => {

    const innerCount = wp.data.select("core/block-editor").getBlocksByClientId(clientId)[0].innerBlocks.length;
    const block = createBlock("theghostlab/cycle-entry");

    wp.data.dispatch("core/block-editor").insertBlock(block, innerCount, clientId);

    blockNode(clientId)?.scrollIntoView({ behavior: "smooth", block: "start"});
}

const setEntryLabel = (clientId, index) => sprintf(__('Variation %d', 'ghostlabs-cycle-block-lite'), index + 1);

const trackLastSelected = (entries, entriesStatus, cycleEntry) => {

    if( !cycleEntry ) return entriesStatus;

    const { id } = cycleEntry.attributes;

    const check = entries.map(entry => entry.id).some( x => x === id);

    if( !check ) return entriesStatus;

    const newObj = {id, lastSelected: true};

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

export {
    entrySelectedArgs,
    filterEntriesStatus,
    haveEntriesBeenAdded,
    insertContentEntryBlock,
    setEntryLabel,
    trackLastSelected
};