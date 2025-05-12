<?php

namespace THEGHOSTLAB\CYCLE\Services;

use Generator;
use wpdb;

final class DBSetup
{
    private string $version = '0.2.3';
    public static string $tablePrefix = 'theghostlab_cycle_';
    protected wpdb $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb =& $wpdb;
    }

    private function prepTables(): array
    {
        return array_values( array_filter(get_class_methods($this), fn($method) => strpos($method, 'createTable') !== false ) );
    }

    private function generate(): Generator
    {
        $tables = $this->prepTables();

        for( $i = 0; $i < count( $tables ); $i++ ){
            if(is_callable([$this,$tables[$i]])){
                $method = $tables[$i];
                yield $this->$method();
            }
        }
    }

    private function createTableFrequency() {

        $table_name = $this->wpdb->prefix . $this::$tablePrefix.'frequency';
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `id` bigint AUTO_INCREMENT,
            `block_id` varchar(26) NOT NULL,
            `entry_id` varchar(26) NOT NULL,
            `post_id` bigint NOT NULL,
            `last_displayed` datetime, 
            PRIMARY KEY (id),
            UNIQUE (block_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function createTableSettings() {

        $table_name = $this->wpdb->prefix . $this::$tablePrefix.'settings';
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `id` bigint AUTO_INCREMENT,
            `post_id` bigint NOT NULL,
            `block_id` varchar(26) NOT NULL,
            `current_id` varchar(26) NOT NULL,
            `update_on` datetime DEFAULT NULL,
            `update_interval` varchar(12),
            `update_frequency` varchar(12),
            `randomize_setting` varchar(12),
            PRIMARY KEY (id),
            UNIQUE (block_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function createTableQueue() {
        $table_name = $this->wpdb->prefix . $this::$tablePrefix.'queue';
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `id` bigint AUTO_INCREMENT,
            `post_id` bigint NOT NULL,
            `block_id` varchar(26) NOT NULL,
            `queue` text DEFAULT NULL,
            `updated_on` datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE (block_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function install() {

        foreach ($this->generate() as $method) {
            $method;
        }

        add_option( 'theghostlab_cycle_db_version', $this->version );
    }
}