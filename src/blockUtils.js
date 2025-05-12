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

/**
 * Checks if a block ID is already reserved by another child block
 * @param {string} blockId - The ID to check
 * @param {string} clientId - The current block's client ID
 * @returns {boolean} - Whether the ID is reserved
 */
const isChildBlockIdReserved = (blockId, clientId) => {
    // Use select from the data module
    const { select } = wp.data;

    // Find parent cycle block
    const parentClientIds = select('core/block-editor').getBlockParentsByBlockName(clientId, 'theghostlab/cycle');
    const parentClientId = parentClientIds[0];

    // If no parent found, ID is not reserved
    if (!parentClientId) return false;

    // Get all child blocks of the parent
    const childBlocks = select('core/block-editor').getBlocks(parentClientId);

    // Count occurrences of the block ID
    const idOccurrences = childBlocks.filter(
        block => block.attributes?.id === blockId && block.clientId !== clientId
    );

    // ID is reserved if it appears in more than one block
    return idOccurrences.length > 0;
};

export {
    isBlockIdReserved,
    isChildBlockIdReserved
}