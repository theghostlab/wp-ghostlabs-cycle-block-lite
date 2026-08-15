<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

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

}