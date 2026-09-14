<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Exception;
use GHOSTLABS\CYCLE_BLOCK\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit;

final class ActivatorDeactivator
{
    use Singleton;

    private const SITES_PER_PAGE = 100;

    static public function activate( $networkWide = false ) : void {

        self::run( $networkWide, static function (): void {
            ( new DBSetup() )->install();
        } );
    }

    public static function run( $networkWide, callable $install ) : void {

        if ( ! $networkWide || ! is_multisite() ) {
            $install();

            flush_rewrite_rules();

            return;
        }

        $offset = 0;

        while ( true ) {
            $siteIds = get_sites( [
                'fields'   => 'ids',
                'number'   => self::SITES_PER_PAGE,
                'offset'   => $offset,
                
                'orderby'  => 'id',
                'order'    => 'ASC',
                
                'deleted'  => 0,
                'archived' => 0,
                'spam'     => 0,
            ] );

            if ( $siteIds === [] ) {
                break;
            }

            foreach ( $siteIds as $siteId ) {
                self::installOn( (int) $siteId, $install );
            }

            if ( count( $siteIds ) < self::SITES_PER_PAGE ) {
                break;
            }

            $offset += self::SITES_PER_PAGE;
        }

        flush_rewrite_rules();
    }

    public static function onNewSite( $site, ?callable $install = null ) : void {

        if ( ! is_multisite() ) {
            return;
        }

        $install ??= static function (): void {
            ( new DBSetup() )->install();
        };

        $siteId = $site instanceof \WP_Site ? (int) $site->blog_id : (int) $site;

        if ( $siteId <= 0 ) {
            return;
        }

        self::installOn( $siteId, $install );
    }

    private static function installOn( int $siteId, callable $install ) : void {

        switch_to_blog( $siteId );

        try {
            $install();
        } finally {

            restore_current_blog();
        }
    }

    static public function deactivate() : void {
        flush_rewrite_rules();
    }
}
