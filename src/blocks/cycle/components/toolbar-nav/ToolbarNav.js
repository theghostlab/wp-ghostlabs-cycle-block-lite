import { __ } from '@wordpress/i18n';
import { Flex, FlexBlock, FlexItem, ToolbarButton } from '@wordpress/components';
import { chevronLeft, chevronRight } from '@wordpress/icons';
import { truncateString } from '../../../../../dev/javascript/utils';
import { toolbarNavStyle } from '../../../../../dev/javascript/inlineStyles';

const disable = childBlocks => childBlocks.length < 2;

export default ({entries, childBlocks}) => {

    return(
        <Flex>
            <FlexItem>
                <ToolbarButton
                    icon={ chevronLeft }
                    label={__("Previous Entry", "ghostlabs-cycle-block-lite")}
                    disabled={ disable(childBlocks) }
                    onClick={ () => {
                        wp.data.dispatch('core/block-editor').selectBlock(childBlocks[childBlocks.length - 1].clientId);
                    } }
                />
            </FlexItem>
            <FlexBlock>
                <span style={toolbarNavStyle}>
                    {
                        (entries.length)
                            ? truncateString(entries[0].label, 12)
                            : 'N/A'
                    }
                </span>
            </FlexBlock>
            <FlexItem>
                <ToolbarButton
                    icon={ chevronRight }
                    label={__("Next Entry", "ghostlabs-cycle-block-lite")}
                    disabled={ disable(childBlocks) }
                    onClick={ () => {
                        wp.data.dispatch('core/block-editor').selectBlock(childBlocks[1].clientId);
                    } }
                />
            </FlexItem>
        </Flex>
    )
}