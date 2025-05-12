<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DateTimeImmutable;
use Exception;
use THEGHOSTLAB\CYCLE\Constants\Formats;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;

class PreviewService
{
    /**
     * @var array{
     *     blockId:string,
     *     currentId:string,
     *     pastEntryId:string,
     *     entries:array,
     *     update:array{
     *          on:string,
     *          interval:int,
     *          frequency:string,
     *          date:int
     *     },
     *     repeate:bool,
     *     randomize:array{
     *          setting:string
     *     }
     * }
     */
    private array $blockAttributes;
    private int $postId;
    private string $transient;
    private array $previewDB = [
        'frequency' => [],
        'queue' => [],
        'history' => [],
    ];

    private Utils $utils;
    private QueueService $q;
    private EntryService $entryService;

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes   = $blockAttributes;
        $this->postId            = $postId;
        $this->entryService      = new EntryService($this->postId,$this->blockAttributes);
        $this->q                 = new QueueService($postId, $blockAttributes);
        $this->utils             = new Utils();
        $this->transient         = $this->setTransient();
    }

    public function firstRun(array $block): array
    {
        if( !empty($block['last_displayed']) ) return $block;

        $block['last_displayed'] = $now = (new DateTimeImmutable('now'))->format(Formats::DATE);

        $this->addFrequency([
            'lastDisplayed' => $now,
        ]);

        return $block;
    }

    public function getHistory(string $blockId): array
    {
        $collection = $this->getCollection();

        if( empty($collection['history']) ) {

            $collection['history'] = [];

            Transients::setCollection($this->transient, $collection);
        }

        return $collection['history'];
    }

    public function setHistory(string $dequeue)
    {
        $collection = $this->getCollection();

        $collection['history'][] = $dequeue;

        Transients::setCollection($this->transient, $collection);
    }

    public function clearHistory()
    {
        $collection = $this->getCollection();

        $collection['history'] = [];

        Transients::setCollection($this->transient, $collection);
    }

    /**
     * @throws Exception
     */
    public function getBlock(?string $blockId = null)
    {
        $_blockId = $blockId ?? $this->blockAttributes['blockId'];

        $collection = Transients::getCollection($this->transient);

        if( empty($collection) ) {
            $collection = $this->initializeCollection();
        }

        if( empty($collection['frequency']) ) {
            $collection['frequency'] = [];
        }

        $result = $this->utils->searchArrayByProperty($collection['frequency'], 'block_id', $_blockId);

        if( empty($result) ) {
            $result = $this->entryService->getBlock($_blockId);
            $collection['frequency'][] = $result;
            Transients::setCollection($this->transient, $collection);
        }

        return $result;
    }

    public function addFrequency(array $update = [])
    {
        $data = array_merge([
            'block_id'       => $this->blockAttributes['blockId'],
            'entry_id'       => $this->blockAttributes['currentId'],
            'post_id'        => $this->postId,
        ], $update);

        $this->insertOnUpdate('frequency', 'entry_id', $data['entry_id'], $data);
    }

    public function updateQueue(array $data)
    {
        $this->insertOnUpdate('queue', 'block_id', $data['block_id'], $data);
    }

    public function insertOnUpdate(string $table, string $property, $value, array $obj)
    {
        $collection = Transients::getCollection($this->transient);

        if( empty($collection) ) {
            $collection = $this->initializeCollection();
        }

        if(empty($collection[$table]) ) {
            $collection[$table] = [];
        }

        $result = $this->utils->searchArrayByProperty($collection[$table], $property, $value);

        if( empty($result) ) {
            $collection[$table][] = $obj;
        } else {

            $result = array_merge($result, $obj);
            $updatedFrequencies = $this->utils->updateArrayItemByProperty($collection[$table], $property, $value, $result);

            $collection[$table] = $updatedFrequencies;
        }

        Transients::setCollection($this->transient, $collection);
    }

    public function fetchQueue(string $block_id)
    {
        $collection = Transients::getCollection($this->transient);

        if( empty($collection) ) {
            $collection = $this->initializeCollection();
        }

        if( empty($collection['queue']) ) {
            $collection['queue'] = [];
        }

        $queue = $this->utils->searchArrayByProperty($collection['queue'], 'block_id', $block_id);

        if( empty($queue) ) {
            $queue = $this->q->fetchQueue($this->blockAttributes['blockId']);
            $this->insertOnUpdate('queue', 'block_id', $block_id, $queue);
        }

        return $queue;
    }

    private function initializeCollection(): array
    {
        $collection = $this->previewDB;
        Transients::setCollection($this->transient, $collection);

        return $collection;
    }

    public function getCollection(): array
    {
        return Transients::getCollection($this->transient);
    }

    private function setTransient(): string
    {
        return sprintf(
            '_%s_%s_preview_%s',
            PluginInfo::$domain,
            PluginInfo::$pluginSlug,
            $this->blockAttributes['blockId']
        );
    }
}