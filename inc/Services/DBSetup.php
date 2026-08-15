<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Boot\TierBootFactory;
use wpdb;

final class DBSetup
{
    /**
     * Bump this when any createTable* statement changes. Plugin updates never
     * reactivate the plugin, so maybeUpgrade()'s version check on admin_init is
     * the only path a schema change has onto an already-installed site.
     */
    public const SCHEMA_VERSION = '0.5.0';
    public const VERSION_OPTION = 'theghostlab_cycle_db_version';

    public static string $tablePrefix = 'theghostlab_cycle_';
    protected wpdb $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb =& $wpdb;
    }

    /**
     * @return list<string>
     */
    private function prepTables(): array
    {
        return array_values( array_filter(get_class_methods($this), fn($method) => strpos($method, 'createTable') !== false ) );
    }


    /*
     * The CREATE statements below are dbDelta-conformant on purpose — this is
     * load-bearing, not style:
     *
     *  - No IF NOT EXISTS: dbDelta parses the table name right after
     *    "CREATE TABLE", so IF NOT EXISTS made it misread the name, skip the
     *    describe-and-diff entirely, and just execute the raw SQL — which is why
     *    the old statements could only ever CREATE, never ALTER.
     *  - PRIMARY KEY is followed by two spaces (dbDelta's documented format).
     *  - Every UNIQUE KEY is *named*, and named exactly what MySQL auto-generated
     *    on existing installs from the old bare `UNIQUE (col)` form (the first
     *    column's name). A mismatched name makes dbDelta add a duplicate index —
     *    block_id_2, block_id_3 — on every upgrade pass, forever.
     *    SchemaUpgradeTest::testRunningTheUpgradeTwiceLeavesTheSchemaAlone pins this.
     */

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

    /**
     * Re-run the schema when the recorded version lags the shipped one. Cheap on
     * the happy path — one autoloaded option read and a string compare.
     */
    public function maybeUpgrade(): void
    {
        if ( get_option( self::VERSION_OPTION ) === self::SCHEMA_VERSION ) {
            return;
        }

        $this->install();
    }

    public function install(): void {

        // Before dbDelta: dbDelta only ever ADDs — it cannot drop the legacy
        // unique keys, and while the one-column schedule key exists the widened
        // (block_id, post_id) key changes nothing.
        $this->dropLegacyKeys();

        foreach ($this->prepTables() as $method) {
            $this->{$method}();
        }

        $this->installTierTables();

        // update_option, not add_option: add_option never overwrites, so an
        // upgraded install kept its old recorded version and maybeUpgrade()
        // would re-run dbDelta on every admin request.
        update_option( self::VERSION_OPTION, self::SCHEMA_VERSION );
    }

    /**
     * Tables only the running edition uses.
     *
     * `prepTables()` reflects over this class, so anything declared here runs in
     * every edition — which is exactly how an edition came to create a table it
     * had no code to write to. The edition is asked instead, through
     * `TierBootInterface::schemaStatements()`.
     *
     * Resolved through `TierBootFactory` rather than a filter on purpose: this
     * also runs from the activation hook, and by then `plugins_loaded` — where
     * `boot()` registers everything — has already fired. A filter would be
     * missing at exactly the moment the tables are first created, and `install()`
     * stamps the version option on its way out, so `maybeUpgrade()` would never
     * come back to fix it.
     */
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

    /**
     * Drop the legacy unique keys dbDelta cannot remove. Idempotent: a fresh
     * install never creates them, and once dropped the SHOW INDEX probe finds
     * nothing. MySQL auto-named each from its first column:
     *
     *  - pre-0.4.0 `UNIQUE (block_id)` on the schedule tables, superseded by
     *    the widened block_post pair key;
     *
     * An edition's own tables add their own entries through
     * `TierBootInterface::legacyIndexes()`.
     */
    private function dropLegacyKeys(): void
    {
        // The shared schedule tables, plus whatever the running edition adds for
        // its own — an edition owns its tables, so it owns their migrations.
        $legacyKeys = [
            'frequency' => 'block_id',
            'queue'     => 'block_id',
            'settings'  => 'block_id',
        ] + TierBootFactory::current()->legacyIndexes();

        foreach ($legacyKeys as $suffix => $keyName) {
            $table = $this->wpdb->prefix . self::$tablePrefix . $suffix;

            if ( $this->wpdb->get_var( $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
                continue;
            }

            $legacy = $this->wpdb->get_results(
                $this->wpdb->prepare( 'SHOW INDEX FROM %i WHERE Key_name = %s', $table, $keyName )
            );

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
