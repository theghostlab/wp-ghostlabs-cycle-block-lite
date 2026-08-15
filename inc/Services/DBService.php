<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use mysqli_result;
use stdClass;
use wpdb;
use GHOSTLABS\CYCLE_BLOCK\DTO\Pagination;

/**
 * Generic query building over camelCase <-> snake_case translation.
 *
 * The four wpdb::prepare() calls below stay baselined in PHPStan
 * (argument.type: literal-string): each query interpolates a table name and
 * column list assembled from calling code before the values go through
 * prepare(), so the query string cannot be a literal by construction — the
 * same posture as Loader's hook registry.
 */
class DBService
{
    protected wpdb $wpdb;
    /** @var array<string, string> gettype() name -> wpdb::prepare specifier */
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

    /**
     * @param Pagination $pagination
     * @param string $table
     * @param string|null $orderBy A literal ORDER BY clause, e.g. "created_on DESC, id DESC".
     *                             Interpolated directly, so it must be a constant supplied by
     *                             calling code — never request input. Omit and MySQL returns
     *                             rows in whatever order it likes, which makes paging over
     *                             them repeat or skip rows.
     * @return array<int, array<string, mixed>>|null Rows with snake_case columns
     *         mapped back to camelCase keys.
     */
    function selectAll(Pagination $pagination, $table, ?string $orderBy = null)
    {
        $table = $this->setTable($table);

        $sql = sprintf(
            "SELECT * FROM %s%s LIMIT %d, %d;",
            $table,
            $orderBy === null ? '' : ' ORDER BY ' . $orderBy,
            $pagination->startingLimit,
            $pagination->pageSize
        );

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

    /**
     * @param array<string, mixed> $data camelCase keys; translated to snake_case columns.
     * @param string $table
     * @return int|bool Rows affected, or false when the query could not be prepared.
     */
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

        // prepare() returns null when it rejects the query — a placeholder/argument
        // mismatch, most likely — and query() takes a string.
        if ($sql === null) {
            return false;
        }

        return $this->wpdb->query($sql);
    }

    private function camelToSnake(string $input): string
    {
        $spaced = preg_replace("/([a-z])([A-Z])/", '$1_$2', lcfirst($input));

        // preg_replace returns null if the subject is not valid UTF-8. Passing that
        // to strtolower is deprecated in PHP 8.1 and fatal in 9; fall back to the
        // untransformed input, which is still a usable column name.
        return strtolower($spaced ?? lcfirst($input));
    }

    /**
     * @param string $input
     */
    function snakeToCamel($input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{columns: string, valuesFormat: string, updateFormat: string, rawValues: list<mixed>}
     */
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

    /**
     * @param array<string, mixed> $data
     */
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

    /**
     * @param array<mixed> $data Values only; keys are ignored.
     */
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

        // Appended with [], so it is already a list — unlike setInsertTypes(), which
        // keys by column name and does need the array_values().
        return implode(",", $format);
    }

    /**
     * @param array<string, mixed> $data
     */
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

    private function setTable(string $table): string
    {
        return sprintf('%s%s',$this->wpdb->prefix, $table );
    }

    /**
     * @param array<mixed> $payload Values for the IN (...) clause.
     * @return list<array<string, mixed>>|null
     */
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

    public function getCount(string $table): ?string
    {
        $table = $this->setTable($table);

        $sql = sprintf("SELECT COUNT(*) FROM %s;", $table);

        return $this->wpdb->get_var( $sql );
    }

    /**
     * Delete the rows matching every given column, ANDed.
     *
     * @param array<string, mixed> $conditions camelCase keys; translated to snake_case columns.
     * @return int|bool Rows deleted, or false when the statement could not be prepared.
     */
    public function deleteByColumns(array $conditions, string $table)
    {
        $table = $this->setTable($table);

        $where = [];
        $values = [];

        foreach ($conditions as $key => $value) {
            $specifier = $this->specifierMap[gettype($value)] ?? 's';

            $where[] = sprintf('%s = %%%s', $this->camelToSnake($key), $specifier);
            $values[] = $value;
        }

        $sql = $this->wpdb->prepare(
            sprintf('DELETE FROM %s WHERE %s', $table, implode(' AND ', $where)),
            $values
        );

        if ($sql === null) {
            return false;
        }

        return $this->wpdb->query($sql);
    }

    /**
     * @param string $table
     * @param array<string,mixed> $keys
     * @param string $limit
     * @return list<array<string,mixed>>|null Rows as associative arrays — get_results
     *         is called with ARRAY_A, so stdClass is never in play. Callers index the
     *         result ([0]), which the previous `object` in this union made impossible
     *         to verify.
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