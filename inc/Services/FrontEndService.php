<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateTimeImmutable;
use Throwable;
use SplDoublyLinkedList;
use SplQueue;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\FrequencyRow;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\QueueRow;
use GHOSTLABS\CYCLE_BLOCK\Handlers\CycleHandler;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

class FrontEndService implements FrontEndServiceInterface
{
    
    private array $blockAttributes;
    private int $postId;
    private CycleHandler $cycleHandler;
    private EntryService $entryService;
    private IntervalFrequency $intervalFrequency;
    private QueueService $queue;

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes   = $blockAttributes;
        $this->postId            = $postId;
        $this->cycleHandler      = new CycleHandler($this->postId,$this->blockAttributes);
        $this->entryService      = new EntryService($this->postId,$this->blockAttributes);
        $this->intervalFrequency = new IntervalFrequency($this->postId,$this->blockAttributes);
        $this->queue             = new QueueService($postId, $blockAttributes);
    }

    public function play()
    {
        try {

            $now = new DateTimeImmutable('now');

            $block = $this->entryService->firstRun( $this->entryService->getBlock() );

            $instantTransient = PluginConfig::instantInit($this->postId, $block['block_id']);
            $instant = Transients::getCollection($instantTransient);

            if(!empty($instant['entry_id'])) {
                EntryHistory::setCache($this->postId, $block['block_id'], $instant['entry_id']);
                Transients::clearCollection($instantTransient);
                return $instant['entry_id'];
            }

            $blockSettings = $this->entryService->getBlockSetting($block['block_id']);

            $intervals = $this->intervalFrequency->generateIntervals($block['last_displayed'], $blockSettings, is_preview());

            $queue = $this->queue->fetchQueue($this->blockAttributes['blockId']);

            $splQ = QueueService::deserialize($queue['queue'] ?? null);

            if( !$splQ instanceof SplQueue ) {
                $splQ = new SplQueue();
            }

            $splQ->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);

            $next = $intervals->current();

            if( $splQ->count() === 0 ) {

                EntryHistory::clearCache($this->postId, $this->blockAttributes['blockId']);

                if( $this->blockAttributes['repeat'] ?? true ) {

                    $entries = $this->cycleHandler
                        ->runStrategy()
                        ->returnEntries()
                    ;

                    $splQ = $this->queue->refresh($splQ, $entries);

                    $newEntryId = $this->saveLatestEntry($block, $splQ, $now);

                    return $newEntryId;
                } else {
                    return $block['entry_id'];
                }
            }

            $latestEntry = '';

            if( $now > $next ) {

                $latestEntry = $this->saveLatestEntry($block, $splQ, $now);

            }

            return empty($latestEntry) ? $block['entry_id'] : $latestEntry;

        } catch (Throwable $e) {

            DebugLog::write('play: ' . $e->getMessage());
        }

        return null;
    }

    private function saveLatestEntry(array $block, SplQueue $splQ, DateTimeImmutable $now)
    {
        $dequeue = $splQ->dequeue();

        $frequency = new FrequencyRow();
        $frequency->post_id        = $this->postId;
        $frequency->block_id       = $block['block_id'];
        $frequency->entry_id       = $dequeue;
        $frequency->last_displayed = $now->format(Formats::DATE);

        $this->intervalFrequency->addFrequency($frequency);

        $queueRow = new QueueRow();
        $queueRow->post_id    = $this->postId;
        $queueRow->block_id   = $block['block_id'];
        $queueRow->queue      = serialize($splQ);
        $queueRow->updated_on = $now->format(Formats::DATE);

        $this->queue->updateQueue($queueRow);

        EntryHistory::setCache($this->postId, $block['block_id'], $dequeue);

        return $dequeue;
    }

    public function run() {
        return $this->play();
    }

    public function service(): FrontEndServiceInterface {
        return $this;
    }

}