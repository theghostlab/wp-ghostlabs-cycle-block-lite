import { ulid } from 'ulid';
import { BlockControls, InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Toolbar, ToolbarGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect, useMemo } from '@wordpress/element';
import ToolbarNav from './components/toolbar-nav/ToolbarNav';
import UTMCodes from "./components/utm/UTMCodes";
import { isChildBlockIdReserved } from "../../blockUtils";

const store = new Map();

export default function Edit({ attributes, setAttributes, clientId, name }) {
    // Memoize select calls to reduce unnecessary re-renders
    const parentClientIds = useSelect(
        (select) => select('core/block-editor').getBlockParentsByBlockName(clientId, 'theghostlab/cycle'),
        [clientId]
    );

    const parentClientId = useMemo(
        () => parentClientIds[parentClientIds.length - 1],
        [parentClientIds]
    );

    const parentAttributes = useSelect(
        (select) => select('core/block-editor').getBlockAttributes(parentClientId),
        [parentClientId]
    );

    const childBlocks = useSelect(
        (select) => select('core/block-editor').getBlocks( clientId ),
        [clientId]
    );

    const {
        id,
        utmCodes,
        utmSet,
        className,
    } = attributes;

    const { entriesStatus = [] } = parentAttributes || {};

    // Combine effects and optimize attribute setting
    useEffect(() => {
        const newAttributes = {};

        // Generate or regenerate ID if needed
        if (!id || isChildBlockIdReserved(id, clientId)) {
            newAttributes.id = ulid();
            store.set("initialized", true);
        }

        // Determine last selected status and set className
        const isLastSelected = entriesStatus.some(
            (entryStatus) => entryStatus.id === id && entryStatus.lastSelected
        );
        newAttributes.className = isLastSelected
            ? 'theghostlab-cycle-entry last-selected'
            : 'theghostlab-cycle-entry';

        // Set parent ID
        newAttributes.parentId = parentAttributes?.blockId;

        // Update entry-specific attributes if available
        const entry = parentAttributes?.entries?.find(
            (entry) => entry.clientId === clientId
        );

        if (entry) {
            newAttributes.index = entry.index;
            newAttributes.label = entry.label;
        }

        // Only update attributes if there are changes
        if (Object.keys(newAttributes).length > 0) {
            setAttributes(newAttributes);
        }
    }, [
        id,
        clientId,
        entriesStatus,
        parentAttributes?.blockId,
        parentAttributes?.entries
    ]);

    useEffect(() => {

        if( store.has("initialized") && childBlocks.length > 0 ) {
            wp.data.dispatch( 'core/block-editor' ).selectBlock(childBlocks[0].clientId)

            const isListViewOpened = wp.data.select( 'core/editor' ).isListViewOpened();

            if( !isListViewOpened ) {
                wp.data.dispatch( 'core/editor' ).setIsListViewOpened(true)
            }

            store.set("initialized", false);
        }

    },[childBlocks]);

    // Get DOM element for toolbar nav (moved inside component)
    const el = useMemo(
        () => document.querySelector(`#block-${clientId}`),
        [clientId]
    );

    // Memoize block props to prevent unnecessary re-renders
    const blockProps = useBlockProps({
        'data-block-id': attributes.parentId,
        'data-entry-id': attributes.id,
        'data-variation-name': attributes.label,
        className,
    });

    return [
        <InspectorControls key={`entry-inspector-controls-${clientId}`}>
            <UTMCodes
                utmSet={utmSet}
                utmCodes={utmCodes}
                setAttributes={setAttributes}
            />
        </InspectorControls>,

        <BlockControls key={`theghostlab-cycle-blocks-innerblocks-${clientId}`}>
            <Toolbar label="Options" className="theghostlab-cycle-toolbar">
                <ToolbarGroup>
                    <ToolbarNav
                        el={el}
                        parentClientId={parentClientId}
                        name={name}
                        label={attributes.label}
                    />
                </ToolbarGroup>
            </Toolbar>
        </BlockControls>,

        <div
            {...blockProps}
            key={`theghostlab-cycle-innerblocks-${clientId}`}
        >
            <InnerBlocks
                template={[
                    ['core/paragraph']
                ]}
            />
        </div>
    ];
}