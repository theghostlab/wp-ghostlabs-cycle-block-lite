<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateTimeImmutable;
use Exception;
use GHOSTLABS\CYCLE_BLOCK\Constants\Formats;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\BlockSettingsRow;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlockSettings
{
    private DBService $DBService;

    public function __construct()
    {
        $this->DBService = new DBService();
    }

    public function insertEntry(int $post_id, array $blockAttributes)
    {
        $row = $this->blockAttributesRow($post_id, $blockAttributes);

        return $this->DBService->insertOnUpdate($row->toRow(), DBTables::settingsTable());
    }

    public function blockAttributesRow(int $post_id, array $blockAttributes): BlockSettingsRow
    {

        $update = is_array($blockAttributes['update'] ?? null) ? $blockAttributes['update'] : [];

        $on = !empty($update['on']) && is_string($update['on']) ? $update['on'] : 'now';

        try {
            $updateOn = new DateTimeImmutable($on);
        } catch (Exception $e) {
            
            $updateOn = new DateTimeImmutable('now');
        }

        $row = new BlockSettingsRow();

        $row->post_id           = $post_id;
        $row->block_id          = sanitize_text_field($blockAttributes['blockId']);
        $row->current_id        = !empty($blockAttributes['currentId']) ? sanitize_text_field($blockAttributes['currentId']) : '';
        $row->update_on         = $updateOn->format(Formats::DATE);
        $row->update_interval   = sanitize_text_field((string) ($update['interval'] ?? 7));
        $row->update_frequency  = sanitize_text_field((string) ($update['frequency'] ?? 'd'));
        $row->randomize_setting = !empty($blockAttributes['randomize']['setting']) ? sanitize_text_field($blockAttributes['randomize']['setting']) : 'random';

        return $row;
    }
}