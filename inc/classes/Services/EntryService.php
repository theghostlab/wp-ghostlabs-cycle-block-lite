<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DateTimeImmutable;
use Exception;
use THEGHOSTLAB\CYCLE\Constants\Formats;

class EntryService
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
    private DBService $DBService;
    private IntervalFrequency $intervalFrequency;

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes   = $blockAttributes;
        $this->postId            = $postId;
        $this->DBService         = new DBService();
        $this->intervalFrequency = new IntervalFrequency($this->postId,$this->blockAttributes);
    }

    static public function updateOnEntryChange(int $post_id, string $block_id, string $current_id, string $past_entry_id) : string {
        if( $current_id === $past_entry_id ) return false;

        $DBService = new DBService();

        $DBService->insertOnUpdate([
            'post_id'  => $post_id,
            'block_id' => $block_id,
            'entry_id' => $current_id,
        ], DBTables::frequencyTable());

        EntryHistory::clearCache($post_id,$block_id);

        return $current_id;
    }

    public function getBlockSetting(?string $blockId = null)
    {
        $blockSettings = $this->DBService->getTableByKeys(DBTables::settingsTable(),[
            'blockId' => $blockId ?? $this->blockAttributes['blockId']
        ], 'LIMIT 1');

        return $this->computeEntry($blockSettings);
    }

    private function computeEntry(?array $blockSettings) {
        if( !$blockSettings ) return false;

        $tmp = $blockSettings[0];

        return [
            'id' => esc_attr($tmp['id']),
            'post_id' => esc_attr($tmp['post_id']),
            'current_id' => esc_attr($tmp['current_id']),
            'update_on' => esc_attr($tmp['update_on']) ?? 'now',
            'update_interval' => esc_attr($tmp['update_interval']),
            'update_frequency' => esc_attr($tmp['update_frequency']),
            'randomize_setting' => esc_attr($tmp['randomize_setting']),
        ];
    }

    /**
     * @throws Exception
     */
    public function getBlock(?string $blockId = null) {

        $_blockId = $blockId ?? $this->blockAttributes['blockId'];

        $block = $this->DBService->getSelectedOffsetLimit([
            $_blockId,
        ], DBTables::frequencyTable(), 'block_id', 0, 1);

        if (empty($block[0])) {
            throw new Exception(esc_html__('Block not found.', 'ghostlabs-cycle-block-lite'));
        }

        return $block[0];
    }

    public function firstRun(array $block): array
    {
        if( !empty($block['last_displayed']) ) return $block;

        $block['last_displayed'] = $now = (new DateTimeImmutable('now'))->format(Formats::DATE);

        $this->intervalFrequency->addFrequency([
            'lastDisplayed' => $now,
        ]);

        return $block;
    }
}