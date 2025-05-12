<?php

namespace THEGHOSTLAB\CYCLE\Traits;

trait Singleton
{
    private static $instance;

    final function __construct() {}
    final function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'You are not allowed to clone this class.', 'ghostlabs-cycle-block-lite' ), '0.1.0' );
    }
    final function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'You are not allowed to unserialize this class.', 'ghostlabs-cycle-block-lite' ), '0.1.0' );
    }

    final static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}