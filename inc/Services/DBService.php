<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use mysqli_result;
use stdClass;
use wpdb;
use GHOSTLABS\CYCLE_BLOCK\DTO\Pagination;

if ( ! defined( 'ABSPATH' ) ) exit;

class DBService
{
    protected wpdb $wpdb;
    
    private array $specifierMap = [
        "double" => "f",
        "integer" => "d",
        "string" => "s"
    ];

    public function __construct()
    {
        global $wpdb;
        $this->wpdb =& $wpdb;
    }

    function selectAll(Pagination $pagination, $table, ?string $orderBy = null)
    {
        $orderBy = $this->safeOrderBy($orderBy);

        // grandfathered in `phpstan-baseline.neon`. That count was raised

        $query = 'SELECT * FROM %i'
            . ($orderBy === null ? '' : ' ORDER BY ' . $orderBy)
            . ' LIMIT %d, %d';

        $sql = $this->wpdb->prepare(
            $query,
            $this->setTable($table),
            $pagination->startingLimit,
            $pagination->pageSize
        );

        if (! is_string($sql)) {
            return null;
        }

        $results = $this->wpdb->get_results($sql, ARRAY_A);

        if( empty($results) ){
            return $results;
        }

        $data = [];

        foreach ($results as $index => $result) {
            foreach ($result as $key => $value){
                $key = $this->snakeToCamel($key);
                $data[$index][$key] = $value;
            }
        }

        return $data;
    }

    function insertOnUpdate($data, $table)
    {
        [
            "columns" => $columns,
            "columnNames" => $columnNames,
            "valuesFormat" => $valuesFormat,
            "updateFormat" => $updateFormat,
            "rawValues" => $rawValues,
            "updateArgs" => $updateArgs
        ] = $this->formatInsertOnUpdate($data);

        $query = sprintf(
            'INSERT INTO %%i (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $columns,
            $valuesFormat,
            $updateFormat
        );

        $sql = $this->wpdb->prepare(
            $query,
            array_merge([$this->setTable($table)], $columnNames, $rawValues, $updateArgs)
        );

        if ($sql === null) {
            return false;
        }

        return $this->wpdb->query($sql);
    }

    private function camelToSnake(string $input): string
    {
        $spaced = preg_replace("/([a-z])([A-Z])/", '$1_$2', lcfirst($input));

        return strtolower($spaced ?? lcfirst($input));
    }

    function snakeToCamel($input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    private function formatInsertOnUpdate($payload): array
    {
        $data = [];

        foreach ($payload as $key => $value) {
            $key = $this->camelToSnake($key);
            $data[$key] = $value;
        }

        $keys = array_keys($data);
        $values = array_values($data);
        $insertTypes = $this->setInsertTypes($data);
        $updateTypes = $this->setUpdateTypes($data);

        $updateArgs = [];

        foreach ($data as $column => $value) {
            $updateArgs[] = $column;
            $updateArgs[] = $value;
        }

        return [

            "columns" => implode(", ", array_fill(0, count($keys), '%i')),
            "columnNames" => $keys,
            "valuesFormat" => $insertTypes,
            "updateFormat" => $updateTypes,
            "rawValues" => $values,
            "updateArgs" => $updateArgs
        ];
    }

    private function setInsertTypes($data): string
    {
        $format = [];

        foreach ($data as $key => $value)
        {
            $specifier = "%g";
            $type = gettype($value);

            if (isset($this->specifierMap[$type])) {
                $specifier = "%" . $this->specifierMap[$type];
            }

            $format[$key] = $specifier;
        }

        return implode(",", array_values($format));
    }

    private function setUpdateTypes($data): string
    {
        $format = [];

        foreach ($data as $key => $value)
        {
            $specifier = "%g";
            $type = gettype($value);

            if (isset($this->specifierMap[$type])) {
                $specifier = "%" . $this->specifierMap[$type];
            }

            $format[] = "%i = $specifier";
        }

        return implode(", ", $format);
    }

    private function setTable(string $table): string
    {
        return sprintf('%s%s',$this->wpdb->prefix, $table );
    }

    private function safeOrderBy(?string $orderBy): ?string
    {
        if ($orderBy === null || trim($orderBy) === '') {
            return null;
        }

        $orderBy = trim($orderBy);

        $term = '[A-Za-z_][A-Za-z0-9_]*(?:\s+(?:ASC|DESC))?';

        return preg_match('/^' . $term . '(?:\s*,\s*' . $term . ')*$/i', $orderBy) === 1
            ? $orderBy
            : null;
    }

    private function safeLimit(string $limit): string
    {
        $limit = trim($limit);

        return preg_match('/^LIMIT\s+\d+(?:\s*,\s*\d+)?$/i', $limit) === 1 ? $limit : '';
    }

    public function getCount(string $table): ?string
    {

        $sql = $this->wpdb->prepare('SELECT COUNT(*) FROM %i', $this->setTable($table));

        if (! is_string($sql)) {
            return null;
        }

        return $this->wpdb->get_var( $sql );
    }

    public function selectSince(string $table, string $column, int $days): array
    {
        $sql = $this->wpdb->prepare(
            'SELECT * FROM %i WHERE %i >= ( NOW() - INTERVAL %d DAY )',
            $this->setTable($table),
            $this->camelToSnake($column),
            max(1, $days)
        );

        if (!is_string($sql)) {
            return [];
        }

        $rows = $this->wpdb->get_results($sql, ARRAY_A);

        if (!is_array($rows)) {
            return [];
        }

        $data = [];

        foreach ($rows as $row) {
            $mapped = [];

            foreach ((array) $row as $key => $value) {
                $mapped[$this->snakeToCamel((string) $key)] = $value;
            }

            $data[] = $mapped;
        }

        return $data;
    }

    public function deleteOlderThanDays(string $table, string $column, int $days): int
    {
        $sql = $this->wpdb->prepare(
            'DELETE FROM %i WHERE %i < ( NOW() - INTERVAL %d DAY )',
            $this->setTable($table),
            $this->camelToSnake($column),
            max(1, $days)
        );

        if (!is_string($sql)) {
            return 0;
        }

        return (int) $this->wpdb->query($sql);
    }

    public function deleteByColumns(array $conditions, string $table)
    {
        $where = [];
        $values = [];

        foreach ($conditions as $key => $value) {
            $specifier = $this->specifierMap[gettype($value)] ?? 's';

            $where[] = sprintf('%%i = %%%s', $specifier);
            $values[] = $this->camelToSnake($key);
            $values[] = $value;
        }

        $query = sprintf('DELETE FROM %%i WHERE %s', implode(' AND ', $where));

        $sql = $this->wpdb->prepare(
            $query,
            array_merge([$this->setTable($table)], $values)
        );

        if ($sql === null) {
            return false;
        }

        return $this->wpdb->query($sql);
    }

    public function getTableByKeys(string $table, array $keys, string $limit = '')
    {

        $limit = $this->safeLimit($limit);

        $conditions = [];
        $args       = [];

        foreach ($keys as $key => $value) {
            $specifier = $this->specifierMap[gettype($value)] ?? 's';

            $conditions[] = sprintf(
                '%s %%i = %%%s',
                $conditions === [] ? 'WHERE' : 'AND',
                $specifier
            );

            $args[] = $this->camelToSnake($key);
            $args[] = $value;
        }

        $query = sprintf('SELECT * FROM %%i %s %s', implode(' ', $conditions), $limit);

        $sql = $this->wpdb->prepare(
            $query,
            array_merge([$this->setTable($table)], $args)
        );

        if (! is_string($sql)) {
            return null;
        }

        return $this->wpdb->get_results($sql, ARRAY_A);
    }
}