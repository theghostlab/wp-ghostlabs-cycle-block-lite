<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateTimeImmutable;
use Exception;
use GhostLabs\WPKit\Service\DTOMapper;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\BlockSettingsRow;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\FrequencyRow;

if ( ! defined( 'ABSPATH' ) ) exit;

class EntryService
{
    
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

    public function getBlockSetting(?string $blockId = null): ?BlockSettingsRow
    {
        $blockSettings = $this->DBService->getTableByKeys(DBTables::settingsTable(),[
            'blockId' => $blockId ?? $this->blockAttributes['blockId'],
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        return $this->computeEntry($blockSettings);
    }

    private function computeEntry(?array $blockSettings): ?BlockSettingsRow
    {
        if( !$blockSettings ) return null;

        $escaped = array_map(
            static fn($value) => esc_attr((string) ($value ?? '')),
            $blockSettings[0]
        );

        $row = DTOMapper::map($escaped, BlockSettingsRow::class);

        return $row;
    }

    public function getBlock(?string $blockId = null) {

        $block = $this->DBService->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $blockId ?? $this->blockAttributes['blockId'],
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        if (empty($block[0])) {
            throw new Exception(__('Block not found.', 'ghostlabs-cycle-block-lite'));
        }

        return $block[0];
    }

    public function firstRun(array $block): array
    {
        if( !empty($block['last_displayed']) ) return $block;

        $block['last_displayed'] = $now = (new DateTimeImmutable('now'))->format(Formats::DATE);

        $update = new FrequencyRow();
        $update->last_displayed = $now;

        $this->intervalFrequency->addFrequency($update);

        return $block;
    }
}