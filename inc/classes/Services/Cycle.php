<?php

namespace THEGHOSTLAB\CYCLE\Services;

use Exception;
use THEGHOSTLAB\CYCLE\Handlers\PreviewHandler;
use wpdb;

class Cycle
{
    private array $blockAttributes;
    private int $postId;
    protected wpdb $wpdb;

    public function __construct(int $postId, array $blockAttributes)
    {
        global $wpdb;

        $this->wpdb =& $wpdb;
        $this->blockAttributes  = $blockAttributes;
        $this->postId           = $postId;
    }

    static public function reset(string $blockId)
    {
        $DBService = new DBService();
        $DBService->delete(['block_id' => $blockId], 'block_id', DBTables::frequencyTable());
        $DBService->delete(['block_id' => $blockId], 'block_id', DBTables::queueTable());
        $DBService->delete(['block_id' => $blockId], 'block_id', DBTables::settingsTable());
        EntryHistory::clearCache($blockId);
    }

    /**
     * @throws Exception
     */
    private function publishContent() {

        $frontEnd = new FrontEndService($this->postId, $this->blockAttributes);

	    return $frontEnd->run();
    }

    /**
     * @throws Exception
     */
    public function content() {

        if( is_preview() ) {
            return ( new PreviewHandler($this->postId, $this->blockAttributes) )->run();
        }

        return $this->publishContent();
    }
}