<?php

namespace THEGHOSTLAB\CYCLE\Services;

use Exception;
use Generator;
use THEGHOSTLAB\CYCLE\Handlers\CycleHandler;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use WP_Post;

class SaveBlockAttributes
{
    private BlockSettings $blockSettings;

    public function __construct()
    {
        $this->blockSettings = new BlockSettings();
    }

    /**
     * @throws Exception
     */
    public function save($post_id, WP_Post $post) {

//        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $blocks = parse_blocks( $post->post_content );

	    foreach ($this->scanForBlockName($blocks) as $block)
	    {
		    $cycleHandler = new CycleHandler($post_id, $block['attrs'], $post->post_status);
		    $frequency    = new IntervalFrequency($post_id, $block['attrs']);
		    $queue        = new QueueService($post_id, $block['attrs']);

		    try {

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

		    } catch (Exception $e) {
			    throw new Exception(esc_html($e->getMessage()));
		    }
	    }
    }

	/**
	 * Recursively scans the array for the specified blockName.
	 *
	 * @param array $data
	 * @return Generator
	 */
	private function recursiveScan(array $data): Generator
	{
		foreach ($data as $item) {
			if (isset($item['blockName']) && $item['blockName'] === PluginInfo::$blockName) {
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
	 * @param array $blocks
	 * @return Generator
	 */
	public function scanForBlockName(array $blocks): Generator
	{
		yield from $this->recursiveScan($blocks);
	}
}