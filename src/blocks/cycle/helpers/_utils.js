const findBlock = blockName => {
    // Get all blocks from the editor
    const blocks = wp.data.select('core/block-editor').getBlocks();
    let found = false;

    // Recursive function to search blocks
    const searchBlocks = (blocks, blockName) => {
        // Short-circuit if we've already found a match
        if (found) return;

        for (let i = 0; i < blocks.length; i++) {
            // Short-circuit if we've already found a match
            if (found) return;

            const block = blocks[i];

            // Check if this block matches our target
            if (block.name === blockName) {
                found = block;
                return; // Stop searching
            }

            // Check inner blocks
            if (block.innerBlocks && block.innerBlocks.length > 0) {
                searchBlocks(block.innerBlocks, blockName);
            }
        }
    }

    searchBlocks(blocks, blockName);
    return found;
}

function findAllBlocks(blockName) {
    // Get all blocks from the editor
    const blocks = wp.data.select('core/block-editor').getBlocks();
    const foundBlocks = [];

    // Recursive function to search blocks
    function searchBlocks(blocks, blockName, foundBlocks) {
        blocks.forEach(block => {
            // Check if this block matches our target
            if (block.name === blockName) {
                foundBlocks.push(block);
            }

            // Check inner blocks
            if (block.innerBlocks && block.innerBlocks.length > 0) {
                searchBlocks(block.innerBlocks, blockName, foundBlocks);
            }
        });
    }

    searchBlocks(blocks, blockName, foundBlocks);
    return foundBlocks;
}

export {
    findAllBlocks,
    findBlock
}