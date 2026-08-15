<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Generator;
use Throwable;
use GHOSTLABS\CYCLE_BLOCK\Handlers\CycleHandler;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use WP_Post;

class SaveBlockAttributes
{
    private BlockSettings $blockSettings;

    public function __construct()
    {
        $this->blockSettings = new BlockSettings();
    }

    public function save(int $post_id, WP_Post $post): void {

        // save_post fires a second time for the revision WordPress writes on
        // every edit, with the revision's own post id. Schedule rows are keyed
        // by (block, post), so without this guard every edit would add a
        // phantom row under the revision id — the old block_id-only unique key
        // silently collapsed those into the real row, which is why this never
        // showed before.
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $blocks = parse_blocks( $post->post_content );

        foreach ($this->scanForBlockName($blocks) as $block)
        {
            // parse_blocks returns the raw serialized attributes and does NOT apply
            // block.json defaults, so a block saved without a blockId has no key at
            // all here. Every schedule row is keyed by block_id, so there is nothing
            // to write — and CycleHandler would hand the null straight to
            // EntryHistory::setCache(string), fataling the whole save.
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
                // Throwable, not Exception: malformed attributes surface as
                // TypeError — an Error — just as readily. Logged, not rethrown:
                // save_post runs inside the editor's REST save, and anything
                // escaping here turns one malformed block into a failed save
                // for the whole post (and skips every block after this one).
                // The block simply keeps its previous schedule.
                error_log('[Cycle Block] save: ' . $e->getMessage());
            }
        }
    }

    /**
     * Recursively scans the array for the specified blockName.
     *
     * @param array<int|string, mixed> $data parse_blocks() output.
     * @return Generator<int, array<string, mixed>>
     */
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

    /**
     * Public method to scan and return generator.
     *
     * @param array<int|string, mixed> $blocks parse_blocks() output.
     * @return Generator<int, array<string, mixed>>
     */
    public function scanForBlockName(array $blocks): Generator
    {
        yield from $this->recursiveScan($blocks);
    }
}