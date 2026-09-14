import { __ } from '@wordpress/i18n';
import { ulid } from 'ulid';
import { BlockControls, InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Toolbar, ToolbarGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from "@wordpress/element";
import ToolbarNav from './components/toolbar-nav/ToolbarNav';
import {blockNode, isChildBlockIdReserved} from "../../blockUtils";
import CustomEvents from "@tier/cycle-entry/custom-events-panel";
import "./index.css";

const store = new Map();

export default function edit({ attributes, setAttributes, clientId, name, ...rest}) {

    const el = blockNode(clientId);
    const parentClientIds = useSelect( select => select( 'core/block-editor' ).getBlockParentsByBlockName( clientId, 'theghostlab/cycle' ) );
    const parentClientId = parentClientIds[parentClientIds.length - 1];
    const parentAttributes = useSelect( select => select('core/block-editor').getBlockAttributes( parentClientId ) );
    const childBlocks = wp.data.select( 'core/block-editor' ).getBlocks( clientId )

    const {
        id,
        className,
        index,
        metadata
    } = attributes;

    const {
        entriesStatus
    } = parentAttributes;

    const postId = useSelect(select =>
        select("core/editor").getCurrentPostId()
    );

    useEffect(() => {

        if( !id ) {
            setAttributes( { id: ulid() } );
            store.set("initialized", true);
        }

        if( isChildBlockIdReserved(id,clientId) ) {
            setAttributes({id: ulid()});
        }

    },[id]);


    useEffect(() => {

        const initialized = store.get("initialized");

        if( initialized && childBlocks.length > 0 ) {
            wp.data.dispatch( 'core/block-editor' ).selectBlock(childBlocks[0].clientId)

            const isListViewOpened = wp.data.select( 'core/editor' ).isListViewOpened();

            if( !isListViewOpened ) {
                wp.data.dispatch( 'core/editor' ).setIsListViewOpened(true)
            }

            store.set("initialized", false);
        }

    },[childBlocks]);

    useEffect(() => {

        const test = entriesStatus.find(entryStatus => entryStatus.id === id && entryStatus.lastSelected === true );

        setAttributes({
            className: test ? 'theghostlab-cycle-entry last-selected' : 'theghostlab-cycle-entry',
            parentId: parentAttributes.blockId,
        });

        if(parentAttributes?.entries?.length) {
            const entry = parentAttributes.entries.find( entry => entry.id === id);

            if(entry) {
                setAttributes({
                    index: entry.index,
                    label:entry.label
                })
            }
        }

    },[parentAttributes]);

    const blockProps = useBlockProps({
        'data-block-id': attributes.parentId,
        'data-entry-id': attributes.id,
        'data-variation-name': attributes.label,
        className,
    });

    return ([
        <InspectorControls key={`entry-inspector-controls-${blockProps['data-block']}`}>
            {}
            <CustomEvents
                parentClientId={attributes.parentId}
                attributes={attributes}
                setAttributes={setAttributes}
                variation_id={id}
                variation_index={index}
                variation_name={metadata.name}
            />
        </InspectorControls>,
        
        <BlockControls
            key={`theghostlab-cycle-blocks-innerblocks-${blockProps['data-block']}`}
        >
            <Toolbar label={__('Options', 'ghostlabs-cycle-block-lite')} className={`theghostlab-cycle-toolbar`}>
                <ToolbarGroup>
                    <ToolbarNav 
                        el={el}
                        parentClientId={parentClientId}
                        blockName={attributes.metadata.name}
                        name={name}
                        label={attributes.label}
                    />
                </ToolbarGroup>
            </Toolbar>
        </BlockControls>,
        <div
            {...blockProps}
            key={`theghostlab-cycle-innerblocks-${blockProps['data-block']}`}
        >
            <InnerBlocks 
                template={[
                    [ 'core/paragraph' ]
                ]}
            />
        </div>
    ])
}