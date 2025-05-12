<?php

namespace THEGHOSTLAB\CYCLE\Services;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Generator;

class IntervalFrequency
{
    private array $blockAttributes;
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

    public function __construct(int $postId, array $blockAttributes)
    {
        $this->blockAttributes  = $blockAttributes;
        $this->postId           = $postId;
        $this->DBService        = new DBService();
        $this->utils            = new Utils();
    }

    public function setFrequency(?string $blockId = null)
    {
        if( !$this->checkFrequency($blockId) ) {

            $params = [];

            if( !empty($blockId)) {
                $params = [
                    'blockId' => $blockId,
                ];
            }

            $this->addFrequency($params);
        }
    }

    private function checkFrequency(?string $blockId = null)
    {
        $blockId = empty($blockId) ? $this->blockAttributes['blockId'] : $blockId;
        $fetchLastFrequency = $this->DBService->getTableByKeys(DBTables::frequencyTable(), ['blockId' =>  $blockId], 'LIMIT 1');

        return $fetchLastFrequency[0]['block_id'] ?? null;
    }

    public function addFrequency($update = [])
    {
        $data = array_merge([
            'blockId'       => $this->blockAttributes['blockId'],
            'entryId'       => $this->blockAttributes['currentId'],
            'postId'        => $this->postId,
        ], $update);

        return $this->DBService->insertOnUpdate($data,DBTables::frequencyTable());
    }

    /**
     * @throws Exception
     */
    public function generateIntervals(string $start, array $blockSettings, bool $isPreview = false): Generator
    {
        $intervals = $this->intervals($start, $blockSettings, $isPreview);

        return $this->utils->generator($intervals);
    }

    /**
     * @throws Exception
     */
    public function intervals(string $start, array $blockSettings, bool $isPreview = false): ?DatePeriod
    {
        if( empty($blockSettings) ) return null;
        if( !$this->designators[$blockSettings['update_frequency']] ) return null;

        $designator = sprintf($this->designators[$blockSettings['update_frequency']], $blockSettings['update_interval']);

        if( $isPreview ) {
            $designator = "PT0S";
        }

        $start      = new DateTimeImmutable($start);
        $interval   = new DateInterval($designator);
        $end        = ( new DateTimeImmutable() )->modify('+100 years');

        return new DatePeriod($start, $interval, $end, DatePeriod::EXCLUDE_START_DATE);
    }
}