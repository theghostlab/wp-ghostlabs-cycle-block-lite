<?php

namespace GHOSTLABS\CYCLE_BLOCK\Handlers;

use DateTimeImmutable;
use Throwable;
use SplDoublyLinkedList;
use SplQueue;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\Services\EntryService;
use GHOSTLABS\CYCLE_BLOCK\Services\FrontEndServiceInterface;
use GHOSTLABS\CYCLE_BLOCK\Services\IntervalFrequency;
use GHOSTLABS\CYCLE_BLOCK\Services\PreviewService;
use GHOSTLABS\CYCLE_BLOCK\Services\QueueService;
use GHOSTLABS\CYCLE_BLOCK\Services\DebugLog;

if ( ! defined( 'ABSPATH' ) ) exit;

class PreviewHandler implements FrontEndServiceInterface
{
    private int $postId;
    
    private array $blockAttributes;
    private EntryService $entryService;
    private PreviewService $previewService;
    private CycleHandler $cycleHandler;
    private IntervalFrequency $intervalFrequency;
    private QueueService $queue;

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->postId = $postId;
        $this->blockAttributes = $blockAttributes;
        $this->cycleHandler = new CycleHandler($this->postId,$this->blockAttributes);
        $this->entryService = new EntryService($this->postId,$this->blockAttributes);
        $this->intervalFrequency = new IntervalFrequency($this->postId,$this->blockAttributes);
        $this->previewService = new PreviewService($this->postId,$this->blockAttributes);
        $this->queue = new QueueService($postId, $blockAttributes);
    }

    public function run()
    {
        return $this->play();
    }

    public function play()
    {
        try {

            $isPreview = is_preview();

            $now = new DateTimeImmutable('now');

            $block = $this->previewService->getBlock();

            $testing = isset($_GET['ghostlabs_testing'])
                ? sanitize_text_field(wp_unslash($_GET['ghostlabs_testing']))
                : null;

            if($testing !== null && $testing !== $block['block_id']) {
                $isPreview = false;
            }

            $blockSettings = $this->entryService->getBlockSetting($block['block_id']);

            $queue = $this->previewService->fetchQueue($block['block_id']);

            if( empty($block['last_displayed']) )
            {
                $this->previewService->addFrequency([
                    'post_id' => $this->postId,
                    'block_id' => $block['block_id'],
                    'entry_id' => $block['entry_id'],
                    'last_displayed' => $now->format(Formats::DATE),
                ]);

                return $block['entry_id'];
            }

            $intervals = $this->intervalFrequency->generateIntervals($block['last_displayed'], $blockSettings, $isPreview);

            $splQ = QueueService::deserialize($queue['queue'] ?? null);

            if( !$splQ instanceof SplQueue ) {
                $splQ = new SplQueue();
            }

            $splQ->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);

            $next = $intervals->current();

            if( $splQ->count() === 0 ) {

                $this->previewService->clearHistory();

                if( $this->blockAttributes['repeat'] ) {

                    $entries = $this->cycleHandler
                        ->runStrategy()
                        ->returnEntries()
                    ;

                    $splQ = $this->queue->refresh($splQ, $entries);

                } else {

                    $collections = $this->previewService->getCollection();
                    $frequencies = $collections['frequency'];
                    return $frequencies[count($frequencies) - 1]['entry_id'];
                }
            }

            $latestEntry = '';

            if( $now > $next ) {

                $dequeue = $latestEntry = $splQ->dequeue();

                $this->previewService->addFrequency([
                    'post_id' => $this->postId,
                    'block_id' => $block['block_id'],
                    'entry_id' => $dequeue,
                    'last_displayed' => $now->format(Formats::DATE),
                ]);

                $this->previewService->updateQueue([
                    'post_id' => $this->postId,
                    'block_id' => $block['block_id'],
                    'queue' => serialize($splQ),
                    'updated_on' => $now->format(Formats::DATE),
                ]);

                $this->previewService->setHistory($dequeue);
            }

            return empty($latestEntry) ? $block['entry_id'] : $latestEntry;
            
        } catch (Throwable $e) {

            DebugLog::write('preview: ' . $e->getMessage());
        }

        return null;
    }
}