import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {

    const blockProps = useBlockProps.save({
        'data-block-id': attributes.parentId,
        'data-entry-id': attributes.id,
        'data-variation-name': attributes.label,
    });

    return (
        <div
            {...blockProps}
        >
            <InnerBlocks.Content />
        </div>
    )
}