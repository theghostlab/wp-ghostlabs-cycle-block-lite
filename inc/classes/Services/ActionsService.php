<?php

namespace THEGHOSTLAB\CYCLE\Services;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Services\Extras\Extras;
use THEGHOSTLAB\CYCLE\Services\Extras\WebTrafficLogger;
use WP_Post;

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

    public function deletePreviewTransient($post_id)
    {
        $post = get_post($post_id);

        if( empty($post->post_content) ) return;

        $blocks = parse_blocks($post->post_content);

        $cycleBlocks = array_values(
            array_filter($blocks, fn ($block) => $block['blockName'] === 'theghostlab/cycle')
        );

        foreach ($cycleBlocks as $block) {
            $transient = PluginInfo::testPreviewTransient($post_id, $block['attrs']['blockId']);
            delete_transient($transient);
        }
    }

    public function deleteLogEntryTransient($post_id) {
        $transient = sprintf("wp-block-theghostlab-cycle-%s",$post_id);

        delete_transient($transient);
    }
}