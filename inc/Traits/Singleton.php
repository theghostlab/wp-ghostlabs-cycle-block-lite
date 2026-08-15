<?php

namespace GHOSTLABS\CYCLE_BLOCK\Traits;

trait Singleton
{
    /** @var static|null */
    private static $instance;

    final function __construct() {}
    final function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'ghostlabs-cycle-block-lite' ), '0.1.0' );
    }
    final function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'ghostlabs-cycle-block-lite' ), '0.1.0' );
    }

    /**
     * @return static
     */
    final static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
