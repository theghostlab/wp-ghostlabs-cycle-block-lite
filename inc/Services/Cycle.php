<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Exception;
use GHOSTLABS\CYCLE_BLOCK\Handlers\PreviewHandler;

class Cycle
{
    /** @var array<string, mixed> */
    private array $blockAttributes;
    private int $postId;

    /**
     * @param array<string, mixed> $blockAttributes
     */
    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes  = $blockAttributes;
        $this->postId           = $postId;
    }

    static public function reset(int $postId, string $blockId): void
    {
        $DBService = new DBService();

        // Scoped to (block, post): a duplicated post shares the blockId, and a
        // reset from one copy must not wipe the other's schedule.
        $DBService->deleteByColumns(['blockId' => $blockId, 'postId' => $postId], DBTables::frequencyTable());
        $DBService->deleteByColumns(['blockId' => $blockId, 'postId' => $postId], DBTables::queueTable());
        $DBService->deleteByColumns(['blockId' => $blockId, 'postId' => $postId], DBTables::settingsTable());

        EntryHistory::clearCache($postId, $blockId);
    }

    /**
     * @return string|null
     * @throws Exception
     */
    private function publishContent() {

        $frontEnd = new FrontEndService($this->postId, $this->blockAttributes);
        return $frontEnd->run();
    }

    /**
     * Preview takes the parallel PreviewHandler path, which keeps its history and
     * queue in transients so previewing never mutates live cycling state.
     *
     * Every edition ships preview, so this is a direct call rather than an
     * extension point. It went through a `theghostlab_cycle_preview_content`
     * filter while that was briefly not the case; with every tier carrying it,
     * the filter was indirection with one implementation behind it.
     *
     * The property that mattered then still matters: a preview must never reach
     * `publishContent()`, because that dequeues a variation and stamps
     * `last_displayed` on a request that is not a page view.
     * `CyclePreviewTest` pins it.
     *
     * @return string|null
     * @throws Exception
     */
    public function content() {

        if( is_preview() ) {
            return ( new PreviewHandler($this->postId, $this->blockAttributes) )->run();
        }

        return $this->publishContent();
    }
}