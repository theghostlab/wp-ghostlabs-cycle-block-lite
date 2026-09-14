<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

class RandomizationStrategy implements CycleStrategy
{
    
    private array $blockAttributes;

    private array $entries;
    private int $postId;

    public function __construct(int $postId, array $blockAttributes, array $entries)
    {
        $this->blockAttributes = $blockAttributes;
        $this->entries         = $entries;
        $this->postId          = $postId;
    }

    private function diff(): array
    {
        $cachedEntries = EntryHistory::getCache($this->postId, $this->blockAttributes['blockId']);

        if( is_preview() ) {

            $cachedEntries = (new PreviewService($this->postId, $this->blockAttributes))
                ->getHistory($this->blockAttributes['blockId']);
        }

        $entries = $this->entries;

        foreach ($cachedEntries as $entry)
        {
            $key = array_search($entry, $entries);

            if( $key !== false ) {
                unset($entries[$key]);
            }
        }

        $entries = array_values($entries);

        if( empty($entries) ) {
            return $this->entries;
        }

        return $entries;
    }

    public function returnEntries(): array
    {
        $entries = $this->diff();

        shuffle($entries);

        return $entries;
    }
}