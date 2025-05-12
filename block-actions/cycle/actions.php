<?php

use \THEGHOSTLAB\CYCLE\Services\Parse;
use \THEGHOSTLAB\CYCLE\Services\Cycle;

function ghostLabsCycleBlock() {
    register_block_type( THEGHOSTLAB_CYCLE_PLUGIN_PATH.'build/blocks/cycle', [
        'render_callback' => function($block_attributes, $content, \WP_Block $block) {
	        if(is_admin()) return $content;

	        $cycle = new Cycle($block->context['postId'], $block_attributes);

	        $contentId = $cycle->content();

	        $parse = new Parse($contentId, $content, $block);

	        if( !$parse->check() ) return $content;

	        return $parse->run();
        },
        'style' => 'theghostlab-cycle',
    ] );
}