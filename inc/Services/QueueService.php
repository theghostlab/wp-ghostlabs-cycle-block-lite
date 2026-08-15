<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateTimeImmutable;
use SplDoublyLinkedList;
use SplQueue;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\QueueRow;

class QueueService
{
    private int $postId;
    /** @var array<string, mixed> */
    private array $blockAttributes;
    private DBService $DBService;
    /** @var SplQueue<string> */
    private SplQueue $q;

    /**
     * @param array<string, mixed> $blockAttributes
     */
    public function __construct(int $postId, array $blockAttributes)
    {
        $this->postId          = $postId;
        $this->blockAttributes = $blockAttributes;
        $this->DBService       = new DBService();
    }

    /**
     * @param SplQueue<string> $splQueue
     * @param list<string> $items
     * @return SplQueue<string>
     */
    public function refresh(SplQueue $splQueue, array $items): SplQueue
    {
        foreach ($items as $item) {
            $splQueue->enqueue($item);
        }

        return $splQueue;
    }

    /**
     * Safely restore a queue blob read from the queue column.
     *
     * The column is unserialize()d on every front-end render (FrontEndService and
     * PreviewHandler), so the allowed class list is pinned to SplQueue: a tampered
     * blob naming any other class is turned into an incomplete object — its
     * constructor and __wakeup never run — and rejected here as "no queue", rather
     * than becoming a PHP object-injection foothold. Callers already treat a null
     * as an empty queue and rebuild it.
     *
     * @return SplQueue<string>|null
     */
    public static function deserialize(?string $blob): ?SplQueue
    {
        if (empty($blob)) {
            return null;
        }

        $queue = unserialize($blob, ['allowed_classes' => [SplQueue::class]]);

        return $queue instanceof SplQueue ? $queue : null;
    }

    /**
     * @return array<string, mixed>|null The block's queue row.
     */
    public function fetchQueue(string $blockId)
    {
        $queue = $this->DBService->getTableByKeys(DBTables::queueTable(),[
            'blockId' => $blockId,
            'postId'  => $this->postId,
        ],'LIMIT 1');

        return $queue[0] ?? null;
    }

    /**
     * @param list<string>|null $items
     */
    public function setQueue(?array $items = null): QueueService
    {
        $this->q = new SplQueue();

        // null is part of this method's signature — a block with no entries yet
        // reaches it — and foreach over null warns rather than iterating nothing.
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

        // On a block's very first save the head of the queue is the variation already
        // on screen, so it gets dropped rather than handed out again. Only drop it when
        // it really is that variation: for randomized blocks RandomizationStrategy has
        // already removed it, and dropping again would skip a variation for a whole pass.
        if( empty($block) && !$this->q->isEmpty() && $this->q->bottom() === ($this->blockAttributes['currentId'] ?? null) ) {
            $this->q->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);
            $this->q->dequeue();
        }

        return $this;
    }

    /**
     * @return SplQueue<string>
     */
    public function returnQueue(): SplQueue {
        return $this->q;
    }

    /**
     * @return int|bool Rows affected, or false when the query could not be prepared.
     */
    public function saveQueue()
    {
        $row = new QueueRow();
        $row->post_id    = $this->postId;
        $row->block_id   = $this->blockAttributes['blockId'];
        $row->queue      = serialize($this->q);
        $row->updated_on = (new DateTimeImmutable('now'))->format(Formats::DATE);

        return $this->updateQueue($row);
    }

    /**
     * @return int|bool Rows affected, or false when the query could not be prepared.
     */
    public function updateQueue(QueueRow $row) {
        return $this->DBService->insertOnUpdate($row->toRow(), DBTables::queueTable());
    }
}