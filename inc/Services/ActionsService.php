<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

class ActionsService
{
    
    protected $post;
    public function __construct()
    {
        global $post;
        $this->post =& $post;

        add_action( 'save_post', [$this, 'deleteLogEntryTransient'],100 );
        add_action( 'save_post', [$this, 'deletePreviewTransient'],100 );
    }

    public function deletePreviewTransient(int $post_id): void
    {
        $post = get_post($post_id);

        if( empty($post->post_content) ) return;

        $blocks = parse_blocks($post->post_content);

        $cycleBlocks = array_values(
            array_filter($blocks, fn ($block) => $block['blockName'] === 'theghostlab/cycle')
        );

        foreach ($cycleBlocks as $block) {

            if (empty($block['attrs']['blockId'])) {
                continue;
            }

            $transient = PluginConfig::testPreviewTransient($post_id, $block['attrs']['blockId']);
            delete_transient($transient);
        }
    }

    public function deleteLogEntryTransient(int $post_id): void {
        $transient = sprintf("wp-block-theghostlab-cycle-%s",$post_id);

        delete_transient($transient);
    }
}