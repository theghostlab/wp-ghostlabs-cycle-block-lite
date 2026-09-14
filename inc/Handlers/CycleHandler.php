<?php

namespace GHOSTLABS\CYCLE_BLOCK\Handlers;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Services\CycleStrategy;
use GHOSTLABS\CYCLE_BLOCK\Services\DBService;
use GHOSTLABS\CYCLE_BLOCK\Services\DBTables;
use GHOSTLABS\CYCLE_BLOCK\Services\EntryHistory;
use GHOSTLABS\CYCLE_BLOCK\Services\RandomizationStrategy;
use GHOSTLABS\CYCLE_BLOCK\Services\Transients;

if ( ! defined( 'ABSPATH' ) ) exit;

class CycleHandler implements CycleStrategy
{
    private int $postId;
    
    private array $blockAttributes;
    private DBService $DBService;
    
    private array $entries;
    private ?string $postStatus;

    public function __construct(int $postId, array $blockAttributes, ?string $postStatus = null )
    {
        $this->postId = $postId;
        $this->blockAttributes = $blockAttributes;

        $this->entries = array_values(array_map(
            static fn(array $entry): string => (string) ( $entry['id'] ?? '' ),
            array_filter(
                is_array($blockAttributes['entries'] ?? null) ? $blockAttributes['entries'] : [],
                'is_array'
            )
        ));

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
            'blockId' => $this->blockAttributes['blockId'],
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        $lastEntry = $fetchLastEntry[0] ?? null;

        if( !$lastEntry ) return $this;

        $offset = ($this->blockAttributes['update']['interval'] ?? null) === 0 ? 0 : 1;

        $key = array_search($lastEntry['entry_id'], $this->entries);
        $key = is_int($key) ? $key : 0;

        $entries = array_slice($this->entries, $key + $offset);

        $this->entries = $entries;

        return $this;
    }

    public function firstRun(): CycleHandler
    {
        $block = $this->DBService->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $this->blockAttributes['blockId'],
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        if( empty($block) ) {
            EntryHistory::setCache($this->postId, $this->blockAttributes['blockId'], $this->blockAttributes['currentId']);
        }

        if(
            empty($block[0]['last_displayed'])
            && ($this->blockAttributes['update']['interval'] ?? null) === 0
            && $this->postStatus === 'publish'
        ) {
            if( !isset($this->blockAttributes['randomize']['setting'])
                || (isset($this->blockAttributes['randomize']['setting']) && $this->blockAttributes['randomize']['setting'] === 'random')
            ) {
                Transients::setCollection(PluginConfig::instantInit($this->postId, $this->blockAttributes['blockId']), ['entry_id' => $this->blockAttributes['currentId']], YEAR_IN_SECONDS);
            }
        }

        return $this;
    }
}