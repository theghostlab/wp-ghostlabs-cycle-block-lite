<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Generator;
use Throwable;
use GHOSTLABS\CYCLE_BLOCK\Handlers\CycleHandler;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) exit;

class SaveBlockAttributes
{
    private BlockSettings $blockSettings;

    public function __construct()
    {
        $this->blockSettings = new BlockSettings();
    }

    public function save(int $post_id, WP_Post $post): void {

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $blocks = parse_blocks( $post->post_content );

        foreach ($this->scanForBlockName($blocks) as $block)
        {

            if (empty($block['attrs']['blockId'])) {
                continue;
            }

            try {

                $cycleHandler = new CycleHandler($post_id, $block['attrs'], $post->post_status);
                $frequency    = new IntervalFrequency($post_id, $block['attrs']);
                $queue        = new QueueService($post_id, $block['attrs']);

                $entries = $cycleHandler
                    ->firstRun()
                    ->diff()
                    ->runStrategy()
                    ->returnEntries()
                ;

                $queue
                    ->setQueue($entries)
                    ->firstRun()
                    ->saveQueue()
                ;

                $frequency->setFrequency();

                $this->blockSettings->insertEntry($post_id, $block['attrs']);

            } catch (Throwable $e) {

                DebugLog::write('save: ' . $e->getMessage());
            }
        }
    }

    private function recursiveScan(array $data): Generator
    {
        foreach ($data as $item) {
            if (isset($item['blockName']) && $item['blockName'] === PluginConfig::$blockName) {
                yield $item;
            }

            if (isset($item['innerBlocks']) && is_array($item['innerBlocks'])) {
                yield from $this->recursiveScan($item['innerBlocks']);
            }
        }
    }

    public function scanForBlockName(array $blocks): Generator
    {
        yield from $this->recursiveScan($blocks);
    }
}