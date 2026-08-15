import { __ } from '@wordpress/i18n';
import { ulid } from 'ulid';
import { BlockControls, InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Toolbar, ToolbarGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from "@wordpress/element";
import ToolbarNav from './components/toolbar-nav/ToolbarNav';
import {blockNode, isChildBlockIdReserved} from "../../blockUtils";
// Resolved by webpack's `@tier` alias. An edition with the feature supplies
// panel; the free tier supplies its own component in the same slot.
import CustomEvents from "@tier/cycle-entry/custom-events-panel";
import "./index.css";

const store = new Map();

export default function edit({ attributes, setAttributes, clientId, name, ...rest}) {

    // Resolved through the canvas document — under apiVersion 3 the block DOM
    // lives inside the editor-canvas iframe, where the parent frame's
    // `document` never finds it.
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

    // useEffect(() => {
    //
    //     if( !attributes.id ) {
    //         setAttributes( { id: ulid() } );
    //     }
    //
    //     const siblingBlockIds = select( 'core/block-editor' )
    //     .getBlocks( parentClientId )
    //     .map( ({attributes, clientId}, index) => ({clientId, id: attributes.id, index}))
    //
    //     const notSelf = siblingBlockIds.filter( sibling => sibling.clientId !== clientId )
    //     const isSelf = siblingBlockIds.find( sibling => sibling.clientId === clientId )
    //
    //     notSelf.forEach( sibling => {
    //
    //         if( sibling.id === id ) {
    //
    //             if( isSelf?.index > sibling.index ) {
    //                 setAttributes( { id: ulid() } );
    //             }
    //         }
    //     });
    //
    // },[]);

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
            // Matched by the entry's own id — entries[] no longer carries
            // editor-session clientIds, they are saved post content.
            const entry = parentAttributes.entries.find( entry => entry.id === id);

            if(entry) {
                setAttributes({
                    index: entry.index,
                    label:entry.label
                })
            }
        }

    },[parentAttributes]);

    // useEffect(() => {
    //
    //     setAttributes({
    //         metadata: {
    //             ...attributes.metadata,
    //             name: attributes.metadata.name
    //         }
    //     });
    //
    // }, [attributes.metadata.name]);

    const blockProps = useBlockProps({
        'data-block-id': attributes.parentId,
        'data-entry-id': attributes.id,
        'data-variation-name': attributes.label,
        className,
    });

    return ([
        <InspectorControls key={`entry-inspector-controls-${blockProps['data-block']}`}>
            {/*
              * Rendered unconditionally. Whether there is anything to show,
              * and on what terms, is the edition's business — one supplies the
              * feature, another an upgrade prompt in its place.
              *
              * That decision used to be made here, by reading an inline var and
              * filtering on it. It belongs to the edition: this component
              * compiles into every one of them, and only some have a verdict to
              * read.
              */}
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