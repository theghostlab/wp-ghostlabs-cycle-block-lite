<?php

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Services\DBService;
use GHOSTLABS\CYCLE_BLOCK\Services\DBTables;
use \GHOSTLABS\CYCLE_BLOCK\Services\Parse;
use \GHOSTLABS\CYCLE_BLOCK\Services\Cycle;
use GHOSTLABS\CYCLE_BLOCK\Services\SourceRoots;
function create_theghostlab_cycle_block(): void {
    // No 'style' handle here on purpose: registered block styles are enqueued
    // on every page of a classic-theme site, block or no block. The callback
    // enqueues at render time instead, so only pages that carry a cycle block
    // pay for the request.
    register_block_type( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH.'build/blocks/cycle', [
        'render_callback' => 'theghostlab_cycle_callback',
    ] );
}

/**
 * Render one variation, or fall back to the block's own content.
 *
 * Every bail below returns $content unchanged, which renders the block exactly as
 * the editor saved it. That is the safe degradation: showing every variation is a
 * cosmetic problem, whereas letting any of this escape takes down the whole page —
 * and over the REST API, where the response embeds content.rendered, it corrupts
 * the JSON body and makes the post unsaveable.
 *
 * Typed in PHPDoc rather than natively, deliberately. This function's whole
 * contract is that nothing escapes it; a native `string` return type would turn
 * an unexpected null from any path into a TypeError — converting the failure it
 * exists to absorb back into the fatal it exists to prevent.
 *
 * @param array<string, mixed> $block_attributes
 * @param string $content
 * @return string
 */
function theghostlab_cycle_callback($block_attributes, $content, \WP_Block $block) {

    if(is_admin()) return $content;

    // A REST render (editor apiFetch loads, API readers embedding
    // content.rendered) must not run the cycle: choosing an entry here would
    // advance the live schedule on a request that is not a page view. This
    // used to key off $_REQUEST['_locale'] — the editor's apiFetch marker —
    // which also matched ordinary front-end URLs carrying that param, and it
    // answered a literal '...': a crawlable thin-content variant of every URL
    // the block appears on. Detect REST itself and fall back to the saved
    // content, like every other bail here.
    $isRestRender = function_exists('wp_is_rest_endpoint')
        ? wp_is_rest_endpoint()
        : ( defined('REST_REQUEST') && REST_REQUEST ); // WP < 6.5

    if( $isRestRender ) return $content;

    // Enqueued mid-render, so it prints from the footer via print_late_styles()
    // — fine, because the file only carries the ?ghostlabs_testing preview
    // affordance, which view.js applies from the footer anyway. Sits above the
    // bails below so the fallback render carries it too.
    // Resolved across both roots: the file is hand-written and lives in shared/
    // because both tiers need it, so running in place (and in wp-env) it is a
    // directory over from PLUGIN_URL rather than under it. A wrong URL here is a
    // silent 404 on a stylesheet, which no PHP test would ever see.
    $stylesheet = SourceRoots::asset('assets/css/theghostlab-cycle.css');

    if( $stylesheet ) {
        wp_enqueue_style(
            'theghostlab-cycle',
            $stylesheet['url'],
            [],
            PluginConfig::assetVersion('assets/css/theghostlab-cycle.css')
        );
    }

    // Supplied by core/post-content. Absent when a cycle block is rendered outside a
    // post context, and Cycle requires an int.
    $postId = $block->context['postId'] ?? null;

    if( empty($postId) ) return $content;

    // parse_blocks does not apply block.json defaults, so a block saved without a
    // blockId has no key here and never got a schedule row to look up.
    if( empty($block_attributes['blockId']) ) return $content;

    $cycle = new Cycle((int) $postId, $block_attributes);

    try {
        // EntryService::getBlock() throws "Block not found." whenever the frequency
        // table has no row for this block — deleted rows, a restored revision, a
        // post copied between sites.
        $contentId = $cycle->content();

        if( empty($contentId) ) return $content;

//    error_log(__LINE__);
//    error_log(__FILE__);
//    error_log(__FUNCTION__);
//    error_log(__CLASS__);
//    error_log(__METHOD__);
//    error_log(print_r($contentId,true));
//    error_log(print_r($block->parsed_block['attrs']['blockId'],true));
//
//    return $content;

        $parse = new Parse($contentId, $content, $block);

        if( !$parse->check() ) return $content;

        // Inside the catch on purpose: Parse::run() re-renders the chosen
        // variation through the_content, where any third-party filter can throw
        // — outside it, one exploding filter takes the page (and, over REST,
        // the JSON body) down with it.
        return $parse->run();
    } catch ( \Throwable $e ) {
        error_log( '[Cycle Block] render: ' . $e->getMessage() );
        return $content;
    }
}