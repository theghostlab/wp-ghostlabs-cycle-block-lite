<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

enum UpsellSource: string
{
    
    case EditorSchedule = 'editor-schedule';

    case EditorEvents = 'editor-events';

    case EditorCycleNotice = 'editor-cycle-notice';

    case PluginsRow = 'plugins-row';

    case Settings = 'settings';

    case SettingsTable = 'settings-table';
}
