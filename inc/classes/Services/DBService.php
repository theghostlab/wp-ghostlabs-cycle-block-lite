<?php

namespace THEGHOSTLAB\CYCLE\Services;

use mysqli_result;
use stdClass;
use wpdb;

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

    function insertOnUpdate($data, $table)
    {
        $table = $this->setTable($table);

        [
            "columns" => $columns,
            "valuesFormat" => $valuesFormat,
            "updateFormat" => $updateFormat,
            "rawValues" => $rawValues
        ] = $this->formatInsertOnUpdate($data);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s",
            $table,
            $columns,
            $valuesFormat,
            $updateFormat
        );

        $sql = $this->wpdb->prepare($sql,array_merge($rawValues, $rawValues));

	    return $this->wpdb->query($sql);
    }

    private function camelToSnake(string $input): string
    {
        return strtolower(
            preg_replace("/([a-z])([A-Z])/", '$1_$2', lcfirst($input))
        );
    }

    private function formatInsertOnUpdate($payload): array
    {
        $data = [];

        foreach ($payload as $key => $value) {
            $key = $this->camelToSnake($key);
            $data[$key] = $value;
        }

        $keys = array_keys($data);
        $columns = implode(",", $keys);
        $values = array_values($data);
        $insertTypes = $this->setInsertTypes($data);
        $updateTypes = $this->setUpdateTypes($data);

        return [
            "columns" => $columns,
            "valuesFormat" => $insertTypes,
            "updateFormat" => $updateTypes,
            "rawValues" => $values
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

    private function setDeleteTypes($data): string
    {
        $format = [];

        foreach ($data as $value)
        {
            $specifier = "g";
            $type = gettype($value);

            if (isset($this->specifierMap[$type])) {
                $specifier = "%" . $this->specifierMap[$type];
            }

            $format[] = $specifier;
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

            $key = trim($this->camelToSnake($key));

            $format[] = "$key = $specifier";
        }

        return implode(", ", $format);
    }

    public function pagination(array $pagination): array
    {
        [
            'pageSize' => $pageSize,
            'startingLimit' => $start,
        ] = $pagination;

        $pageSize = $pageSize ?? 60;
        $start = $start ?? 0;

        return [
            'start' => $start,
            'pageSize' => $pageSize
        ];
    }

    private function setTable(string $table): string
    {
        return sprintf('%s%s',$this->wpdb->prefix, $table );
    }

    public function delete(array $payload, string $column, string $table)
    {
        $formats = $this->setDeleteTypes($payload);
        $table = $this->setTable($table);
        $column = $this->camelToSnake($column);

        $sql = sprintf("DELETE from %s WHERE %s IN (%s)", $table, $column, $formats);

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $payload),
            ARRAY_A
        );
    }

	public function getQueueTableCount(): ?string {
		return $this->wpdb->get_var( "SELECT COUNT(*) FROM wp_theghostlab_cycle_queue;" );
	}

    public function getSelectedOffsetLimit(array $selected, string $table, $key, $offset, $limit)
    {
        $table = $this->setTable($table);
        $formats = $this->setDeleteTypes($selected);

        $sql = sprintf("SELECT * FROM %s WHERE %s IN (%s) LIMIT %s, %d", $table, $key, $formats, $offset, $limit);

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                $sql,
                $selected
            ),
            ARRAY_A
        );
    }

    /**
     * @param string $table
     * @param array $keys
     * @param string $limit
     * @return array|object|stdClass[]|null
     */
    public function getTableByKeys(string $table, array $keys, string $limit = '')
    {
        $values = array_values($keys);
        $formats = explode(',',$this->setUpdateTypes($keys));
        $table = $this->setTable($table);

        $i = -1;

        foreach ($formats as &$format)
        {
            $i++;

            if( $i === 0 ) {
                $format = sprintf("WHERE %s", $format);
            } else {
                $format = sprintf("AND %s", $format);
            }
        }

        $formats = implode(' ',$formats);

        $sql = sprintf("SELECT * FROM %s %s %s;", $table, $formats, $limit);

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                $sql,
                $values
            ),
            ARRAY_A
        );
    }
}