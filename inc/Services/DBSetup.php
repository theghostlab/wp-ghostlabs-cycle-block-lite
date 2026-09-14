<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Boot\TierBootFactory;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) exit;

final class DBSetup
{
    
    public const SCHEMA_VERSION = '0.5.0';
    public const VERSION_OPTION = 'theghostlab_cycle_db_version';

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

    private function createTableFrequency(): void {

        $table_name = $this->wpdb->prefix . $this::$tablePrefix.'frequency';
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            block_id varchar(26) NOT NULL,
            entry_id varchar(26) NOT NULL,
            post_id bigint(20) NOT NULL,
            last_displayed datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY block_post (block_id,post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function createTableSettings(): void {

        $table_name = $this->wpdb->prefix . $this::$tablePrefix.'settings';
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            block_id varchar(26) NOT NULL,
            current_id varchar(26) NOT NULL,
            update_on datetime DEFAULT NULL,
            update_interval varchar(12) DEFAULT NULL,
            update_frequency varchar(12) DEFAULT NULL,
            randomize_setting varchar(12) DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY block_post (block_id,post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function createTableQueue(): void {
        $table_name = $this->wpdb->prefix . $this::$tablePrefix.'queue';
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            block_id varchar(26) NOT NULL,
            queue text DEFAULT NULL,
            updated_on datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY block_post (block_id,post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function maybeUpgrade(): void
    {
        if ( get_option( self::VERSION_OPTION ) === self::SCHEMA_VERSION ) {
            return;
        }

        $this->install();
    }

    public function install(): void {

        $this->dropLegacyKeys();

        foreach ($this->prepTables() as $method) {
            $this->{$method}();
        }

        $this->installTierTables();

        update_option( self::VERSION_OPTION, self::SCHEMA_VERSION );
    }

    private function installTierTables(): void
    {
        $statements = TierBootFactory::current()->schemaStatements(
            $this->wpdb->prefix . self::$tablePrefix,
            $this->wpdb->get_charset_collate()
        );

        if ( $statements === [] ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ($statements as $sql) {
            dbDelta( $sql );
        }
    }

    private function dropLegacyKeys(): void
    {

        $legacyKeys = [
            'frequency' => 'block_id',
            'queue'     => 'block_id',
            'settings'  => 'block_id',
        ] + TierBootFactory::current()->legacyIndexes();

        foreach ($legacyKeys as $suffix => $keyName) {
            $table = $this->wpdb->prefix . self::$tablePrefix . $suffix;

            $exists = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table );

            if ( ! is_string( $exists ) || $this->wpdb->get_var( $exists ) !== $table ) {
                continue;
            }

            $indexes = $this->wpdb->prepare( 'SHOW INDEX FROM %i WHERE Key_name = %s', $table, $keyName );

            if ( ! is_string( $indexes ) ) {
                continue;
            }

            $legacy = $this->wpdb->get_results( $indexes );

            if ( empty( $legacy ) ) {
                continue;
            }

            $drop = $this->wpdb->prepare( 'ALTER TABLE %i DROP INDEX %i', $table, $keyName );

            if ( $drop !== null ) {
                $this->wpdb->query( $drop );
            }
        }
    }
}
