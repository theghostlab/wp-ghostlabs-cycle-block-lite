<?php

namespace THEGHOSTLAB\CYCLE\Handlers;

use DateTimeImmutable;
use Exception;
use SplDoublyLinkedList;
use SplQueue;
use THEGHOSTLAB\CYCLE\Constants\Formats;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Services\EntryService;
use THEGHOSTLAB\CYCLE\Services\FrontEndServiceInterface;
use THEGHOSTLAB\CYCLE\Services\IntervalFrequency;
use THEGHOSTLAB\CYCLE\Services\PreviewService;
use THEGHOSTLAB\CYCLE\Services\QueueService;

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

			if(isset($_GET['_ghostlabs_nonce'])) {
				$nonce = sanitize_text_field(wp_unslash($_GET['_ghostlabs_nonce']));

				$nonce = wp_verify_nonce($nonce,PluginInfo::$nonceAction);

				if($nonce && isset($_GET['ghostlabs_testing']) ) {
					$blockId = sanitize_text_field(wp_unslash($_GET['ghostlabs_testing']));

					if( $block['block_id'] !== $blockId ) {
						$isPreview = false;
					}
				}
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

            /** @var SplQueue $splQ **/
            $splQ = unserialize($queue['queue']);
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
            
        } catch (Exception $e) {
            echo esc_html($e->getMessage());
        }

        return null;
    }
}