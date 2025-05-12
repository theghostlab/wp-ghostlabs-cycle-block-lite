<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DateTimeImmutable;
use Exception;
use THEGHOSTLAB\CYCLE\Constants\Formats;

class BlockSettings
{
    private DBService $DBService;

    public function __construct()
    {
        $this->DBService = new DBService();
    }

    /**
     * @throws Exception
     */
    public function insertEntry(int $post_id, array $blockAttributes)
    {
        $dto = $this->blockAttributesDto($post_id, $blockAttributes);

        return $this->DBService->insertOnUpdate($dto, DBTables::settingsTable());
    }

    /**
     * @throws Exception
     */
    public function blockAttributesDTO(int $post_id, array $blockAttributes): array
    {
        return [
            'post_id' => $post_id,
            'block_id' => sanitize_text_field($blockAttributes['blockId']),
            'current_id' => !empty($blockAttributes['currentId']) ? sanitize_text_field($blockAttributes['currentId']) : '',
            'update_on' => (new DateTimeImmutable($blockAttributes['update']['on']))->format(Formats::DATE),
            'update_interval' => sanitize_text_field($blockAttributes['update']['interval']),
            'update_frequency' => sanitize_text_field($blockAttributes['update']['frequency']),
            'randomize_setting' => !empty($blockAttributes['randomize']['setting']) ? sanitize_text_field($blockAttributes['randomize']['setting']) : 'random'
        ];
    }
}