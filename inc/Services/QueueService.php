<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateTimeImmutable;
use SplDoublyLinkedList;
use SplQueue;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\QueueRow;

if ( ! defined( 'ABSPATH' ) ) exit;

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

    public static function deserialize(?string $blob): ?SplQueue
    {
        if (empty($blob)) {
            return null;
        }

        $queue = unserialize($blob, ['allowed_classes' => [SplQueue::class]]);

        return $queue instanceof SplQueue ? $queue : null;
    }

    public function fetchQueue(string $blockId)
    {
        $queue = $this->DBService->getTableByKeys(DBTables::queueTable(),[
            'blockId' => $blockId,
            'postId'  => $this->postId,
        ],'LIMIT 1');

        return $queue[0] ?? null;
    }

    public function setQueue(?array $items = null): QueueService
    {
        $this->q = new SplQueue();

        foreach ($items ?? [] as $item) {
            $this->q->enqueue($item);
        }

        return $this;
    }

    public function firstRun(): QueueService
    {
        $block = $this->DBService->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $this->blockAttributes['blockId'],
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        if( empty($block) && !$this->q->isEmpty() && $this->q->bottom() === ($this->blockAttributes['currentId'] ?? null) ) {
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
        $row = new QueueRow();
        $row->post_id    = $this->postId;
        $row->block_id   = $this->blockAttributes['blockId'];
        $row->queue      = serialize($this->q);
        $row->updated_on = (new DateTimeImmutable('now'))->format(Formats::DATE);

        return $this->updateQueue($row);
    }

    public function updateQueue(QueueRow $row) {
        return $this->DBService->insertOnUpdate($row->toRow(), DBTables::queueTable());
    }
}