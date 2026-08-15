<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateTimeImmutable;
use Exception;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

class PreviewService
{
    /**
     * The block's editor attributes. Typed loosely on purpose: parse_blocks does not
     * apply block.json defaults, so which keys are present depends on what the editor
     * actually serialised. Expect blockId, currentId, pastEntryId, entries, update
     * {on, interval, frequency, date}, repeat and randomize {setting} — but check
     * before reading rather than assuming.
     *
     * @var array<string, mixed>
     */
    private array $blockAttributes;
    private int $postId;
    private string $transient;
    /**
     * The in-transient stand-in for the three cycling tables, so previewing never
     * touches live schedule state.
     *
     * @var array{frequency: list<array<string, mixed>>, queue: list<array<string, mixed>>, history: list<string>}
     */
    private array $previewDB = [
        'frequency' => [],
        'queue' => [],
        'history' => [],
    ];

    private Utils $utils;
    private QueueService $q;
    private EntryService $entryService;

    /**
     * @param array<string, mixed> $blockAttributes
     */
    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes   = $blockAttributes;
        $this->postId            = $postId;
        $this->entryService      = new EntryService($this->postId,$this->blockAttributes);
        $this->q                 = new QueueService($postId, $blockAttributes);
        $this->utils             = new Utils();
        $this->transient         = $this->setTransient();
    }

    /**
     * @param array<string, mixed> $block
     * @return array<string, mixed>
     */
    public function firstRun(array $block): array
    {
        if( !empty($block['last_displayed']) ) return $block;

        $block['last_displayed'] = $now = (new DateTimeImmutable('now'))->format(Formats::DATE);

        $this->addFrequency([
            'lastDisplayed' => $now,
        ]);

        return $block;
    }

    /**
     * @return list<string> Entry ids already shown in this preview pass.
     */
    public function getHistory(string $blockId): array
    {
        $collection = $this->getCollection();

        if( empty($collection['history']) ) {

            $collection['history'] = [];

            Transients::setCollection($this->transient, $collection);
        }

        return $collection['history'];
    }

    /**
     * @return void
     */
    public function setHistory(string $dequeue)
    {
        $collection = $this->getCollection();

        $collection['history'][] = $dequeue;

        Transients::setCollection($this->transient, $collection);
    }

    /**
     * @return void
     */
    public function clearHistory()
    {
        $collection = $this->getCollection();

        $collection['history'] = [];

        Transients::setCollection($this->transient, $collection);
    }

    /**
     * @return array<string, mixed>
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

    /**
     * @param array<string, mixed> $update
     * @return void
     */
    public function addFrequency(array $update = [])
    {
        $data = array_merge([
            'block_id'       => $this->blockAttributes['blockId'],
            'entry_id'       => $this->blockAttributes['currentId'],
            'post_id'        => $this->postId,
        ], $update);

        $this->insertOnUpdate('frequency', 'entry_id', $data['entry_id'], $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public function updateQueue(array $data)
    {
        $this->insertOnUpdate('queue', 'block_id', $data['block_id'], $data);
    }

    /**
     * @param mixed $value Value of $property identifying the record to replace.
     * @param array<string, mixed> $obj
     * @return void
     */
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

    /**
     * @return array<string, mixed>
     */
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
            $queue = $this->q->fetchQueue($this->blockAttributes['blockId']) ?? $this->emptyQueue($block_id);
            $this->insertOnUpdate('queue', 'block_id', $block_id, $queue);
        }

        return $queue;
    }

    /**
     * A queue record for a block that has none in the database, so previewing a block
     * whose queue row is missing rebuilds it instead of failing.
     *
     * @return array<string, mixed>
     */
    private function emptyQueue(string $block_id): array
    {
        return [
            'post_id'    => $this->postId,
            'block_id'   => $block_id,
            'queue'      => serialize(new \SplQueue()),
            'updated_on' => (new DateTimeImmutable('now'))->format(Formats::DATE),
        ];
    }

    /**
     * @return array{frequency: list<array<string, mixed>>, queue: list<array<string, mixed>>, history: list<string>}
     */
    private function initializeCollection(): array
    {
        $collection = $this->previewDB;
        Transients::setCollection($this->transient, $collection);

        return $collection;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCollection(): array
    {
        return Transients::getCollection($this->transient);
    }

    private function setTransient(): string
    {
        // Keyed by (post, block) like the live schedule rows — previewing a
        // duplicated post must not read or advance the other copy's preview state.
        return sprintf(
            '_%s_%s_preview_%d_%s',
            PluginConfig::$domain,
            PluginConfig::$pluginSlug,
            $this->postId,
            $this->blockAttributes['blockId']
        );
    }
}