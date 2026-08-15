<?php

namespace GHOSTLABS\CYCLE_BLOCK;

if ( ! defined( 'ABSPATH' ) ) exit;

use GHOSTLABS\CYCLE_BLOCK\Boot\TierBootFactory;
use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;
use GHOSTLABS\CYCLE_BLOCK\Services\ActionsService;
use GHOSTLABS\CYCLE_BLOCK\Services\DBSetup;
use GHOSTLABS\CYCLE_BLOCK\Services\PluginMeta;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Services\BlockFactory;
use GHOSTLABS\CYCLE_BLOCK\Services\Loader;
use GHOSTLABS\CYCLE_BLOCK\Services\RestRoutes\RestRouteInterface;
use GHOSTLABS\CYCLE_BLOCK\Services\SaveBlockAttributes;
use GHOSTLABS\CYCLE_BLOCK\Services\SourceRoots;
use GHOSTLABS\CYCLE_BLOCK\Services\Utils;
use GHOSTLABS\CYCLE_BLOCK\Traits\Singleton;
use WpOrg\Requests\Exception;

final class Kernel
{
    use Singleton;

    private BlockFactory $blockFactory;
    private Loader $loader;
    private PluginMeta $pluginMeta;
    private SaveBlockAttributes $saveBlockAttributes;
    private Utils $utils;

    /**
     * The edition's own wiring and its gate. Everything tier-specific is behind
     * this one object, so nothing below names a class only one tier ships.
     */
    private TierBootInterface $tier;

    /**
     * @throws Exception
     */
    public function init() : Kernel {

        $this->utils                = new Utils();
        $this->blockFactory         = new BlockFactory();
        $this->loader               = new Loader();
        $this->pluginMeta           = new PluginMeta();
        $this->saveBlockAttributes  = new SaveBlockAttributes();
        $this->tier                 = TierBootFactory::current();

        // Constructed for its side effect: it hooks save_post to clear the
        // test-preview transient. Shared, because both editions ship preview.
        new ActionsService();

        $this->defineSchemaUpgrade();
        $this->definePluginMeta();
        $this->defineBlockFactoryHooks();
        $this->defineBlockActions();
        $this->defineBlockScripts();

        $this->defineRestRoutes();

        // Last, so the edition's own hooks land after the common ones — the order
        // Kernel registered them in when this wiring still lived here.
        $this->tier->boot($this->loader);

        return $this;
    }

    /**
     * The schema version check, on a direct add_action: plugin updates never
     * reactivate the plugin, so this is the only migration path — and it must
     * run even when the tier's gate is denying, or those installs' schema goes
     * stale behind it.
     */
    private function defineSchemaUpgrade(): void
    {
        add_action('admin_init', [new DBSetup(), 'maybeUpgrade']);
    }

    /**
     * Discovery is queued, not run: Utils::getClasses() walks inc/ and
     * require_onces every file, and this used to happen on every page load —
     * front end included — just to find route classes. Deferring the walk to
     * rest_api_init (still through the loader, so still gated) means only
     * requests that actually boot the REST API pay for it.
     *
     * @return void
     */
    public function defineRestRoutes()
    {
        $this->loader->add_action('rest_api_init', $this, 'registerDiscoveredRestRoutes');
    }

    public function registerDiscoveredRestRoutes(): void
    {
        // Every root, not just the plugin's own inc/: once a route class lives in
        // shared/ the single-directory walk finds the tier's routes and silently
        // misses the shared ones. In an assembled build there is only one root,
        // so this is a no-op there.
        $registered = [];

        foreach (SourceRoots::dirs('inc') as $dir) {
            foreach ($this->utils->getClasses($dir, RestRouteInterface::class) as $class) {
                if (isset($registered[$class])) {
                    continue;
                }

                $registered[$class] = true;
                $route = new $class();

                if ($route instanceof RestRouteInterface) {
                    $route->routes();
                }
            }
        }
    }

    private function definePluginMeta(): void {
        $hook = sprintf( 'plugin_action_links_%s', GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG);
        add_filter( $hook, [$this->pluginMeta, 'actionSettings'], 10, 4);
    }

    private function defineBlockScripts(): void {
        // Block-editor screens only: theghostlab_cycle_vars is read by the block
        // editor UI alone.
        add_action('enqueue_block_editor_assets', [$this, 'scripts']);
    }

    /**
     * Build the vars the block editor reads.
     *
     * Shared carries only what every edition genuinely needs: the REST namespace
     * for apiFetch, which brings its own cookie nonce. (The admin-ajax era's
     * nonce/domain/adminUrl went with the AJAX endpoints.)
     *
     * Anything an edition alone needs is added through the filter, by that
     * edition. Inlining it here instead put it into the page source of every
     * edition, including ones with no code that reads it.
     *
     * @return array<string, mixed>
     */
    public function editorVars(): array {
        /**
         * Filters the variables inlined for the block editor.
         *
         * @param array<string, mixed> $vars
         */
        return apply_filters('theghostlab_cycle_editor_vars', [
            'restNamespace' => PluginConfig::restNameSpace(),
        ]);
    }

    public function scripts(): void {

        wp_register_script( 'theghostlab-cycle-editor', '', [], PluginConfig::$version, true );

        wp_enqueue_script( 'theghostlab-cycle-editor'  );

        // `window.` and not `const`. A top-level `const` in a classic script
        // creates a *script-scoped* binding: a bare `theghostlab_cycle_vars` read
        // resolves, but `window.theghostlab_cycle_vars` is undefined — so any
        // module reading it off `window` silently gets nothing. Assigning the
        // property satisfies both forms, because a window property *is* a global.
        $script = 'window.theghostlab_cycle_vars = ' . wp_json_encode($this->editorVars()) . ';';

        wp_add_inline_script('theghostlab-cycle-editor', $script, 'before');
    }

    private function defineBlockActions(): void {
        $this->loader->add_action('save_post', $this->saveBlockAttributes, 'save', 50, 3);
    }

    /**
     * @throws Exception
     */
    private function defineBlockFactoryHooks(): void {
        if(!$this->tier->gate()->allows()) return;
        $tags = $this->blockFactory->getActionTags();

        if( !$tags ) return;

        foreach ($tags as $tag) {
            add_action('init', $tag);
        }
    }

    public function run(): void {
        if(!$this->tier->gate()->allows()) return;

        $this->loader->run();

//        add_filter( 'http_request_args', function( $args, $url ) {
//            if ( 'https://mysite.com' === $url ) {
//                $args['reject_unsafe_urls'] = false;
//            }
//            return $args;
//        }, 10, 2 );

//        $this->debug();
    }


}