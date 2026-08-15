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

class FrontEndService implements FrontEndServiceInterface
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
    private CycleHandler $cycleHandler;
    private EntryService $entryService;
    private IntervalFrequency $intervalFrequency;
    private QueueService $queue;

    /**
     * @param array<string, mixed> $blockAttributes
     */
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

//            error_log(__LINE__);
//            error_log(__FILE__);
//            error_log(__FUNCTION__);
//            error_log(__CLASS__);
//            error_log(__METHOD__);

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

            /** @var SplQueue<string>|null $splQ **/
            $splQ = QueueService::deserialize($queue['queue'] ?? null);

            // A block can reach the front end without a usable queue row: the row was
            // removed, or save_post never wrote one. Treat that as an empty queue so the
            // refill path below rebuilds it instead of fataling on the page.
            if( !$splQ instanceof SplQueue ) {
                $splQ = new SplQueue();
            }

            $splQ->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);

//            error_log("BLOCK:");
//            error_log(print_r($block,true));
//
//            error_log("QUEUED ENTRIES BEFORE 0:");
//            error_log(print_r($splQ,true));
//
//            error_log("CACHED ENTRIES BEFORE 0:");
//            error_log(print_r(EntryHistory::getCache($block['block_id']),true));

            $next = $intervals->current();

            if( $splQ->count() === 0 ) {

                EntryHistory::clearCache($this->postId, $this->blockAttributes['blockId']);

                // Defaulted, not read directly: parse_blocks does not apply
                // block.json defaults and Gutenberg omits an attribute that still
                // equals its default, so `repeat` is absent for any block whose
                // author never touched the toggle — and entirely absent in the
                // free tier, which ships no toggle and repeats by design. The
                // fallback is block.json's own default.
                if( $this->blockAttributes['repeat'] ?? true ) {

                    $entries = $this->cycleHandler
                        ->runStrategy()
                        ->returnEntries()
                    ;

                    $splQ = $this->queue->refresh($splQ, $entries);

                    $newEntryId = $this->saveLatestEntry($block, $splQ, $now);
//                    error_log("~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUEUE IS REFRESHED, WHAT IS NEW ENTRY ID? $newEntryId ~~~~~~~~~~~~~~~~~~~~~~~~~~~");

                    return $newEntryId;
                } else {
                    return $block['entry_id'];
                }
            }

//            error_log("QUEUED ENTRIES:");
//            error_log(print_r($splQ,true));

            $latestEntry = '';

//            error_log("NEXT:");
//            error_log(print_r($next,true));

//            error_log("NOW:");
//            error_log(print_r($now,true));

//            ob_start();
//            var_dump( $now > $next );
//            $contents = ob_get_contents();
//            ob_end_clean();
//            error_log( $contents );

            if( $now > $next ) {

                $latestEntry = $this->saveLatestEntry($block, $splQ, $now);

//                $dequeue = $latestEntry = $splQ->dequeue();
//
//                error_log("DEQUEUED:::::::::::::::::::::::::::: $dequeue");
//
//                $this->intervalFrequency->addFrequency([
//                    'postId' => $this->postId,
//                    'blockId' => $block['block_id'],
//                    'entryId' => $dequeue,
//                    'lastDisplayed' => $now->format(Formats::DATE),
//                ]);
//
//                $this->queue->updateQueue([
//                    'postId' => $this->postId,
//                    'blockId' => $block['block_id'],
//                    'queue' => serialize($splQ),
//                    'updatedOn' => $now->format(Formats::DATE),
//                ]);
//
//                EntryHistory::setCache($block['block_id'], $dequeue);
            }

//            error_log(print_r($this->blockAttributes,true));

            return empty($latestEntry) ? $block['entry_id'] : $latestEntry;

        } catch (Throwable $e) {
            // Throwable, not Exception, so coding faults degrade the same way
            // as data faults. Logged, never echoed: this runs inside render_block, so anything
            // printed here lands in the page — and over the REST API inside
            // content.rendered, corrupting the JSON body. Returning null lets the
            // render callback fall back to the block's own content.
            error_log('[Cycle Block] play: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @param array<string, mixed> $block The block's frequency row.
     * @param SplQueue<string> $splQ
     * @return string The entry id handed out.
     */
    private function saveLatestEntry(array $block, SplQueue $splQ, DateTimeImmutable $now)
    {
        $dequeue = $splQ->dequeue();

//        error_log("DEQUEUED:::::::::::::::::::::::::::: $dequeue");

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