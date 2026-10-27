<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Generator;
use GHOSTLABS\CYCLE_BLOCK\Boot\TierBootFactory;
use GHOSTLABS\CYCLE_BLOCK\Contracts\ScheduleSetInterface;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\BlockSettingsRow;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\FrequencyRow;

if ( ! defined( 'ABSPATH' ) ) exit;

class IntervalFrequency
{
    
    private array $blockAttributes;
    private int $postId;
    private DBService $DBService;
    private Utils $utils;
    private ScheduleSetInterface $schedules;

    public function __construct(int $postId, array $blockAttributes, ?ScheduleSetInterface $schedules = null)
    {
        $this->blockAttributes  = $blockAttributes;
        $this->postId           = $postId;
        $this->DBService        = new DBService();
        $this->utils            = new Utils();
        $this->schedules        = $schedules ?? TierBootFactory::current()->schedules();
    }

    public function setFrequency(?string $blockId = null): void
    {
        if( !$this->checkFrequency($blockId) ) {

            $row = new FrequencyRow();

            if( !empty($blockId)) {
                $row->block_id = $blockId;
            }

            $this->addFrequency($row);
        }
    }

    private function checkFrequency(?string $blockId = null)
    {
        $blockId = empty($blockId) ? $this->blockAttributes['blockId'] : $blockId;
        $fetchLastFrequency = $this->DBService->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $blockId,
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        return $fetchLastFrequency[0]['block_id'] ?? null;
    }

    public function addFrequency(?FrequencyRow $update = null)
    {
        
        $row = clone ($update ?? new FrequencyRow());

        $row->block_id ??= $this->blockAttributes['blockId'];
        $row->entry_id ??= $this->blockAttributes['currentId'];
        $row->post_id  ??= $this->postId;

        return $this->DBService->insertOnUpdate($row->toRow(), DBTables::frequencyTable());
    }

    public function generateIntervals(string $start, ?BlockSettingsRow $blockSettings, bool $isPreview = false): Generator
    {
        $intervals = $this->intervals($start, $blockSettings, $isPreview);

        return $this->utils->generator($intervals ?? []);
    }

    public function intervals(string $start, ?BlockSettingsRow $blockSettings, bool $isPreview = false): ?DatePeriod
    {
        if( $blockSettings === null ) return null;

        $designator = $this->schedules->duration(sprintf(
            '%s-%s',
            (string) ($blockSettings->update_interval ?? ''),
            (string) ($blockSettings->update_frequency ?? '')
        ));

        if( $designator === null ) return null;

        if( $isPreview ) {
            $designator = "PT0S";
        }

        $start      = new DateTimeImmutable($start);
        $interval   = new DateInterval($designator);
        $end        = ( new DateTimeImmutable() )->modify('+100 years');

        return new DatePeriod($start, $interval, $end, DatePeriod::EXCLUDE_START_DATE);
    }
}