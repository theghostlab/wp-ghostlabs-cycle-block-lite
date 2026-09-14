const findBlock = blockName => {
    const blocks = wp.data.select('core/block-editor').getBlocks();
    let found = false;

    const searchBlocks = (blocks, blockName) => {
        if (found) return;

        for (let i = 0; i < blocks.length; i++) {
            if (found) return;

            const block = blocks[i];

            if (block.name === blockName) {
                found = block;
                return;
            }

            if (block.innerBlocks && block.innerBlocks.length > 0) {
                searchBlocks(block.innerBlocks, blockName);
            }
        }
    }

    searchBlocks(blocks, blockName);
    return found;
}

function findAllBlocks(blockName) {
    const blocks = wp.data.select('core/block-editor').getBlocks();
    const foundBlocks = [];

    function searchBlocks(blocks, blockName, foundBlocks) {
        blocks.forEach(block => {
            if (block.name === blockName) {
                foundBlocks.push(block);
            }

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