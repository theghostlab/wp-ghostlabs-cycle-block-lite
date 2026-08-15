<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

class RandomizationStrategy implements CycleStrategy
{
    /** @var array<string, mixed> */
    private array $blockAttributes;

    /** @var list<string> */
    private array $entries;
    private int $postId;

    /**
     * @param array<string, mixed> $blockAttributes
     * @param list<string> $entries
     */
    public function __construct(int $postId, array $blockAttributes, array $entries)
    {
        $this->blockAttributes = $blockAttributes;
        $this->entries         = $entries;
        $this->postId          = $postId;
    }

    /**
     * @return list<string>
     */
    private function diff(): array
    {
        $cachedEntries = EntryHistory::getCache($this->postId, $this->blockAttributes['blockId']);

        if( is_preview() ) {
            // Constructed here, inside the branch, not in the constructor.
            // It used to be a constructor property while only this line used it,
            // so every front-end render built a preview service it never
            // touched — dead weight, and an outright fatal in any edition that
            // did not ship the class. Every edition ships it now; the lazy
            // construction is kept because it was always the right shape.
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