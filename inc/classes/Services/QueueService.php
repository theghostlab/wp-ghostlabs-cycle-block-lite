<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DateTimeImmutable;
use SplDoublyLinkedList;
use SplQueue;
use THEGHOSTLAB\CYCLE\Constants\Formats;

class QueueService
{
    private int $postId;
    private array $blockAttributes;
    private DBService $DBService;
    private SplQueue $q;

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->postId          = $postId;
        $this->blockAttributes = $blockAttributes;
        $this->DBService       = new DBService();
    }

    public function refresh(SplQueue $splQueue, array $items): SplQueue
    {
        foreach ($items as $item) {
            $splQueue->enqueue($item);
        }

        return $splQueue;
    }

    public function fetchQueue(string $blockId)
    {
        $queue = $this->DBService->getTableByKeys(DBTables::queueTable(),[
            'blockId' => $blockId,
        ],'LIMIT 1');

        return $queue[0] ?? null;
    }

    public function setQueue(?array $items = null): QueueService
    {
        $this->q = new SplQueue();

        foreach ($items as $item) {
            $this->q->enqueue($item);
        }

        return $this;
    }

    public function firstRun(): QueueService
    {
        $block = $this->DBService->getSelectedOffsetLimit([
            $this->blockAttributes['blockId'],
        ], DBTables::frequencyTable(), 'block_id', 0, 1);

        if( empty($block) ) {
            $this->q->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);
            $this->q->dequeue();
        }

        return $this;
    }

    public function returnQueue(): SplQueue {
        return $this->q;
    }

    public function saveQueue()
    {
        return $this->DBService->insertOnUpdate([
            'post_id' => $this->postId,
            'block_id' => $this->blockAttributes['blockId'],
            'queue' => serialize($this->q),
            'updated_on' => (new DateTimeImmutable('now'))->format(Formats::DATE),
        ], DBTables::queueTable());
    }

    public function updateQueue(array $data) {
        return $this->DBService->insertOnUpdate($data,DBTables::queueTable());
    }
}