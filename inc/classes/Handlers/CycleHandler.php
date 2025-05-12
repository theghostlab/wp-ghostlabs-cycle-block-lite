<?php

namespace THEGHOSTLAB\CYCLE\Handlers;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Services\CycleStrategy;
use THEGHOSTLAB\CYCLE\Services\DBService;
use THEGHOSTLAB\CYCLE\Services\DBTables;
use THEGHOSTLAB\CYCLE\Services\EntryHistory;
use THEGHOSTLAB\CYCLE\Services\RandomizationStrategy;
use THEGHOSTLAB\CYCLE\Services\Transients;

class CycleHandler implements CycleStrategy
{
    private int $postId;
    private array $blockAttributes;
    private DBService $DBService;
    private array $entries;
    private ?string $lastDisplayed = null;
    private ?string $postStatus;

    public function __construct(int $postId, array $blockAttributes, ?string $postStatus = null )
    {
        $this->postId = $postId;
        $this->blockAttributes = $blockAttributes;
        $this->entries = array_map(fn($entry) => $entry['id'], $blockAttributes['entries'] ?? []);
        $this->DBService = new DBService();
        $this->postStatus = $postStatus;
    }

    public function refresh(): CycleHandler
    {
        if( isset($this->blockAttributes['randomize']['setting']) && $this->blockAttributes['randomize']['setting'] === 'manual') {
            return $this;
        }

        $tmp = $this->entries;

        shuffle($tmp);

        $this->entries = $tmp;

        return $this;
    }

    public function runStrategy(?array $entries = null): CycleHandler
    {
        $entries = $entries ?? $this->entries;

        if( isset($this->blockAttributes['randomize']['setting']) && $this->blockAttributes['randomize']['setting'] === 'manual') {
            return $this;
        }

        $this->entries = (new RandomizationStrategy($this->postId, $this->blockAttributes, $entries))->returnEntries();

        return $this;
    }

    public function returnEntries(): array
    {
        return $this->entries;
    }

    public function diff(): CycleHandler
    {
        if (empty($this->blockAttributes['randomize']['setting']) || $this->blockAttributes['randomize']['setting'] === 'random') return $this;

        $fetchLastEntry = $this->DBService->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $this->blockAttributes['blockId']
        ], 'LIMIT 1');

        $lastEntry = $fetchLastEntry[0] ?? null;

        if( !$lastEntry ) return $this;

        $offset = $this->blockAttributes['update']['interval'] === 0 ? 0 : 1;

        $key = array_search($lastEntry['entry_id'], $this->entries);
        $key = $key === false ? 0 : $key;

        $entries = array_slice($this->entries, $key + $offset);

        $this->entries = $entries;

        return $this;
    }

    public function firstRun(?string $lastDisplayed = null): CycleHandler
    {
        $block = $this->DBService->getSelectedOffsetLimit([
            $this->blockAttributes['blockId'],
        ], DBTables::frequencyTable(), 'block_id', 0, 1);

        if( empty($block) ) {
            EntryHistory::setCache($this->blockAttributes['blockId'], $this->blockAttributes['currentId']);
        }

        $lastDisplayed = $block[0]['last_displayed'] ?? $lastDisplayed;

        $this->lastDisplayed = $lastDisplayed;

        if(
            empty($block[0]['last_displayed'])
            && $this->blockAttributes['update']['interval'] === 0
            && $this->postStatus === 'publish'
        ) {
            if( !isset($this->blockAttributes['randomize']['setting'])
                || (isset($this->blockAttributes['randomize']['setting']) && $this->blockAttributes['randomize']['setting'] === 'random')
            ) {
                Transients::setCollection(PluginInfo::instantInit($this->postId, $this->blockAttributes['blockId']), ['entry_id' => $this->blockAttributes['currentId']], YEAR_IN_SECONDS);
            }
        }

        return $this;
    }
}