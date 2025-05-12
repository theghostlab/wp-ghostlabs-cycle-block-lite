import { __ } from '@wordpress/i18n';
import { Flex, FlexBlock, FlexItem, ToolbarButton } from '@wordpress/components';
import { chevronLeft, chevronRight } from '@wordpress/icons';
import { truncateString } from '../../../../../javascript/utils';
import { toolbarNavStyle } from '../../../../../javascript/inlineStyles';

const next = (el,siblingBlockIds) => {
    const nextEntry = el.nextSibling

    if( !nextEntry.classList.contains('theghostlab-cycle-entry') ) {
        wp.data.dispatch('core/block-editor').selectBlock(siblingBlockIds[0].clientId)
        return;
    }

    const nextEntryClientId = nextEntry.dataset.block;

    if( !nextEntryClientId ) {
        throw Error('Something went very very wrong');
    }

    wp.data.dispatch('core/block-editor').selectBlock(nextEntryClientId)
}

const previous = (el,siblingBlockIds) => {
    const previousEntry = el.previousSibling

    if( !previousEntry ) {
        wp.data.dispatch('core/block-editor').selectBlock(siblingBlockIds[siblingBlockIds.length - 1].clientId)
        return;
    }

    const previousEntryClientId = previousEntry.dataset.block;

    if( !previousEntryClientId ) {
        throw Error('Something went very very wrong');
    }

    wp.data.dispatch('core/block-editor').selectBlock(previousEntryClientId)
}

const disable = siblingBlockIds => siblingBlockIds.length < 2;

const displayText = (el, blockName, label) => {

    if(label) {
        return label;
    }

    if(!el) {
        return '...';
    }

    if( el.textContent.charCodeAt(0) === 65279 || !el.textContent.length ){
        return '...';
    }

    return blockName.length ? blockName : truncateString(el.textContent, 12);
}

export default ({el, parentClientId, blockName, label}) => {

    const siblingBlockIds = wp.data.select( 'core/block-editor' )
    .getBlocks( parentClientId )
    .map( ({attributes, clientId}, index) => ({clientId, id: attributes.id, index}));

    return(
        <Flex>
            <FlexItem>
                <ToolbarButton
                    icon={ chevronLeft }
                    label={__("Previous Entry", "theghostlab")}
                    disabled={ disable(siblingBlockIds) }
                    onClick={ () => {
                        previous(el,siblingBlockIds) 
                    } }
                />
            </FlexItem>
            <FlexBlock>
                <span style={toolbarNavStyle}>{displayText(el, blockName, label)}</span>
            </FlexBlock>
            <FlexItem>
                <ToolbarButton
                    icon={ chevronRight }
                    label={__("Next Entry", "theghostlab")}
                    disabled={ disable(siblingBlockIds) }
                    onClick={ () => {
                        next(el, siblingBlockIds)
                    } }
                />
            </FlexItem>
        </Flex>
    )
}

