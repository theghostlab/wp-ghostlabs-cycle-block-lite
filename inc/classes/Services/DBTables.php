<?php

namespace THEGHOSTLAB\CYCLE\Services;

global $wpdb;

class DBTables
{
    static public function frequencyTable(): string {
        return sprintf('%sfrequency', DBSetup::$tablePrefix);
    }

    static public function queueTable(): string {
        return sprintf('%squeue', DBSetup::$tablePrefix);
    }

    static public function settingsTable(): string {
        return sprintf('%ssettings', DBSetup::$tablePrefix);
    }

    static public function logsTable(): string {
        return sprintf('%slogs', DBSetup::$tablePrefix);
    }
}