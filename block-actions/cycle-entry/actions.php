<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function create_theghostlab_cycle_entry_block(): void {
    register_block_type( GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH.'build/blocks/cycle-entry' );
}