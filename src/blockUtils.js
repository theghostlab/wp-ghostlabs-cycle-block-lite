import {select} from '@wordpress/data';

const isBlockIdReserved = blockId => {
    const blocksClientIds = select( 'core/block-editor' ).getClientIdsWithDescendants();

    const test = blocksClientIds.reduce((a,clientId) => {

        const block = select( 'core/block-editor' ).getBlockAttributes( clientId);

        if( block?.blockId === blockId ) {
            a.push(clientId);
        }

        return a
    },[]);

    return test.length > 1;
};

const isChildBlockIdReserved = (blockId, clientId) => {

    const parentClientIds = select( 'core/block-editor' ).getBlockParentsByBlockName( clientId, 'theghostlab/cycle' );
    const parentClientId = parentClientIds[0] ?? undefined;

    if( !parentClientId ) return false;

    const childBlocks = select( 'core/block-editor' ).getBlocks(parentClientId);

    const test = childBlocks.reduce((a,{attributes}) => {

        if( attributes?.id === blockId ) {
            a.push(blockId);
        }

        return a;
    },[]);

    return test.length > 1;
};

/**
 * The document the block canvas renders into.
 *
 * apiVersion 3 blocks render inside the editor-canvas iframe while their editor
 * scripts keep running in the parent frame, so the parent's `document` never
 * contains a #block-… node there. Non-iframed canvases still render into the
 * parent document, which the fallback covers.
 */
const canvasDocument = () =>
    document.querySelector('iframe[name="editor-canvas"]')?.contentDocument ?? document;

const blockNode = clientId => canvasDocument().getElementById(`block-${clientId}`);

export {
    blockNode,
    canvasDocument,
    isBlockIdReserved,
    isChildBlockIdReserved
}