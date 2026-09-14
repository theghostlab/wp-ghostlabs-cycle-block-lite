<?php

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Services\DBService;
use GHOSTLABS\CYCLE_BLOCK\Services\DBTables;
use \GHOSTLABS\CYCLE_BLOCK\Services\Parse;
use \GHOSTLABS\CYCLE_BLOCK\Services\Cycle;
use GHOSTLABS\CYCLE_BLOCK\Services\PreviewStyle;
use GHOSTLABS\CYCLE_BLOCK\Services\SourceRoots;

if ( ! defined( 'ABSPATH' ) ) exit;

function create_theghostlab_cycle_block(): void {

    register_block_type( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH.'build/blocks/cycle', [
        'render_callback' => 'theghostlab_cycle_callback',
    ] );
}

function theghostlab_cycle_callback($block_attributes, $content, \WP_Block $block) {

    if(is_admin()) return $content;

    $isRestRender = function_exists('wp_is_rest_endpoint')
        ? wp_is_rest_endpoint()
        : ( defined('REST_REQUEST') && REST_REQUEST ); 

    if( $isRestRender ) return $content;

    $stylesheet = SourceRoots::asset('assets/css/theghostlab-cycle.css');

    if( $stylesheet ) {
        wp_enqueue_style(
            'theghostlab-cycle',
            $stylesheet['url'],
            [],
            PluginConfig::assetVersion('assets/css/theghostlab-cycle.css')
        );

        wp_add_inline_style('theghostlab-cycle', PreviewStyle::inlineCss());
    }

    $postId = $block->context['postId'] ?? null;

    if( empty($postId) ) return $content;

    if( empty($block_attributes['blockId']) ) return $content;

    $cycle = new Cycle((int) $postId, $block_attributes);

    try {

        $contentId = $cycle->content();

        if( empty($contentId) ) return $content;

        $parse = new Parse($contentId, $content, $block);

        if( !$parse->check() ) return $content;

        return $parse->run();
    } catch ( \Throwable $e ) {
        \GHOSTLABS\CYCLE_BLOCK\Services\DebugLog::write( 'render: ' . $e->getMessage() );
        return $content;
    }
}