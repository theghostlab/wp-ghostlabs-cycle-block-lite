<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DateTimeImmutable;
use Exception;
use SplDoublyLinkedList;
use SplQueue;
use THEGHOSTLAB\CYCLE\Constants\Formats;
use THEGHOSTLAB\CYCLE\Handlers\CycleHandler;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;

class FrontEndService implements FrontEndServiceInterface
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

            $instantTransient = PluginInfo::instantInit($this->postId, $block['block_id']);
            $instant = Transients::getCollection($instantTransient);

            if(!empty($instant['entry_id'])) {
                EntryHistory::setCache($block['block_id'], $instant['entry_id']);
                Transients::clearCollection($instantTransient);
                return $instant['entry_id'];
            }

            $blockSettings = $this->entryService->getBlockSetting($block['block_id']);

            $intervals = $this->intervalFrequency->generateIntervals($block['last_displayed'], $blockSettings, is_preview());

            $queue = $this->queue->fetchQueue($this->blockAttributes['blockId']);

            /** @var SplQueue $splQ **/
            $splQ = unserialize($queue['queue']);
            $splQ->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);

            $next = $intervals->current();

            if( $splQ->count() === 0 ) {

                EntryHistory::clearCache($this->blockAttributes['blockId']);

                if( $this->blockAttributes['repeat'] ) {

                    $entries = $this->cycleHandler
                        ->runStrategy()
                        ->returnEntries()
                    ;

                    $splQ = $this->queue->refresh($splQ, $entries);

	                return $this->saveLatestEntry($block, $splQ, $now);
                } else {
                    return $block['entry_id'];
                }
            }

            $latestEntry = '';

            if( $now > $next ) {

                $latestEntry = $this->saveLatestEntry($block, $splQ, $now);
            }

            return empty($latestEntry) ? $block['entry_id'] : $latestEntry;

        } catch (Exception $e) {
            echo esc_html($e->getMessage());
        }

        return null;
    }

    private function saveLatestEntry(array $block, SplQueue $splQ, DateTimeImmutable $now)
    {
        $dequeue = $splQ->dequeue();

        $this->intervalFrequency->addFrequency([
            'postId' => $this->postId,
            'blockId' => $block['block_id'],
            'entryId' => $dequeue,
            'lastDisplayed' => $now->format(Formats::DATE),
        ]);

        $this->queue->updateQueue([
            'postId' => $this->postId,
            'blockId' => $block['block_id'],
            'queue' => serialize($splQ),
            'updatedOn' => $now->format(Formats::DATE),
        ]);

        EntryHistory::setCache($block['block_id'], $dequeue);

        return $dequeue;
    }

    public function run() {
        return $this->play();
    }

    public function service(): FrontEndServiceInterface {
        return $this;
    }

}