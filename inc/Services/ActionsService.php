<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

class ActionsService
{
    /** @var \WP_Post|null */
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
            // No blockId means no transient was ever keyed for it. parse_blocks does
            // not apply block.json defaults, so the key can be missing entirely and
            // testPreviewTransient(int, string) would fatal on the null.
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