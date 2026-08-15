<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Generator;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\BlockSettingsRow;
use GHOSTLABS\CYCLE_BLOCK\DTO\Row\FrequencyRow;

class IntervalFrequency
{
    /** @var array<string, mixed> */
    private array $blockAttributes;
    /** @var array<string, string> Frequency letter -> DateInterval designator template. */
    private array $designators = [
        'y' => 'P%dY',
        'm' => 'P%dM',
        'd' => 'P%dD',
        'h' => 'PT%dH',
        'i' => 'PT%dM',
        's' => 'PT%dS',
    ];
    private int $postId;
    private DBService $DBService;
    private Utils $utils;

    /**
     * @param array<string, mixed> $blockAttributes
     */
    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes  = $blockAttributes;
        $this->postId           = $postId;
        $this->DBService        = new DBService();
        $this->utils            = new Utils();
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

    /**
     * @return string|null The block_id of the existing row, or null when there is none.
     */
    private function checkFrequency(?string $blockId = null)
    {
        $blockId = empty($blockId) ? $this->blockAttributes['blockId'] : $blockId;
        $fetchLastFrequency = $this->DBService->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $blockId,
            'postId'  => $this->postId,
        ], 'LIMIT 1');

        return $fetchLastFrequency[0]['block_id'] ?? null;
    }

    /**
     * @return int|bool Rows affected, or false when the query could not be prepared.
     */
    public function addFrequency(?FrequencyRow $update = null)
    {
        // Cloned so filling in the defaults never mutates the caller's row.
        $row = clone ($update ?? new FrequencyRow());

        $row->block_id ??= $this->blockAttributes['blockId'];
        $row->entry_id ??= $this->blockAttributes['currentId'];
        $row->post_id  ??= $this->postId;

        return $this->DBService->insertOnUpdate($row->toRow(), DBTables::frequencyTable());
    }

    /**
     * @throws Exception
     */
    public function generateIntervals(string $start, ?BlockSettingsRow $blockSettings, bool $isPreview = false): Generator
    {
        $intervals = $this->intervals($start, $blockSettings, $isPreview);

        // intervals() returns null for a block with no settings row, and that went
        // straight into a foreach. Yielding nothing is the intended outcome there;
        // the warning was not.
        return $this->utils->generator($intervals ?? []);
    }

    /**
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>|null
     * @throws Exception
     */
    public function intervals(string $start, ?BlockSettingsRow $blockSettings, bool $isPreview = false): ?DatePeriod
    {
        if( $blockSettings === null ) return null;

        $frequency = $blockSettings->update_frequency ?? '';

        if( !isset($this->designators[$frequency]) ) return null;

        $designator = sprintf($this->designators[$frequency], $blockSettings->update_interval);

        if( $isPreview ) {
            $designator = "PT0S";
        }

        $start      = new DateTimeImmutable($start);
        $interval   = new DateInterval($designator);
        $end        = ( new DateTimeImmutable() )->modify('+100 years');

        return new DatePeriod($start, $interval, $end, DatePeriod::EXCLUDE_START_DATE);
    }
}