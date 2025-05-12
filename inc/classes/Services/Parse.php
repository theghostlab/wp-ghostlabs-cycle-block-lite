<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DOMDocument;
use DOMElement;
use DomXpath;
use Exception;

class Parse
{
    private string $content;
    private string $contentId;
    private DOMDocument $dom;
    private DomXpath $xpath;
    private \WP_Block $block;

    public function __construct(string $contentId, string $content, \WP_Block $block)
    {
        $this->block = $block;
        $this->content   = $content;
        $this->contentId = $contentId;
    }

	public function run(): string
	{
		$utils = new Utils();

		$wrapper = array_values($utils->filterEmptyValues( $this->block->parsed_block['innerContent']));

		$entryBlocks = array_values(
			array_filter($this->block->parsed_block['innerBlocks'], fn($innerBlock) => $innerBlock['blockName'] === 'theghostlab/cycle-entry' && $innerBlock['attrs']['id'] === $this->contentId)
		);

		$serialize_blocks = serialize_blocks( $entryBlocks );
		$parsed_blocks    = parse_blocks( $serialize_blocks );

		$tmp = $wrapper[0];

		if ( $parsed_blocks ) {
			foreach ( $parsed_blocks as $block ) {
				$tmp .= apply_filters( 'the_content', render_block( $block ) );
			}
		}

		return sprintf("%s%s", $tmp, $wrapper[1]);
	}

    public function check(): bool
    {
        if( empty($this->content) ) return false;
        if( empty($this->contentId) ) return false;

        return true;
    }
}