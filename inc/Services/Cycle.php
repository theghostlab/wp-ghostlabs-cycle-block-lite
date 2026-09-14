<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Exception;
use GHOSTLABS\CYCLE_BLOCK\Handlers\PreviewHandler;

if ( ! defined( 'ABSPATH' ) ) exit;

class Cycle
{
    
    private array $blockAttributes;
    private int $postId;

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes  = $blockAttributes;
        $this->postId           = $postId;
    }

    static public function reset(int $postId, string $blockId): void
    {
        $DBService = new DBService();

        $DBService->deleteByColumns(['blockId' => $blockId, 'postId' => $postId], DBTables::frequencyTable());
        $DBService->deleteByColumns(['blockId' => $blockId, 'postId' => $postId], DBTables::queueTable());
        $DBService->deleteByColumns(['blockId' => $blockId, 'postId' => $postId], DBTables::settingsTable());

        EntryHistory::clearCache($postId, $blockId);
    }

    private function publishContent() {

        $frontEnd = new FrontEndService($this->postId, $this->blockAttributes);
        return $frontEnd->run();
    }

    public function content() {

        if( is_preview() && current_user_can('edit_post', $this->postId) ) {
            return ( new PreviewHandler($this->postId, $this->blockAttributes) )->run();
        }

        return $this->publishContent();
    }
}