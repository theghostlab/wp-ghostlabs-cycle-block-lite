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

    private TierBootInterface $tier;

    public function init() : Kernel {

        $this->utils                = new Utils();
        $this->blockFactory         = new BlockFactory();
        $this->loader               = new Loader();
        $this->pluginMeta           = new PluginMeta();
        $this->saveBlockAttributes  = new SaveBlockAttributes();
        $this->tier                 = TierBootFactory::current();

        new ActionsService();

        $this->defineSchemaUpgrade();
        $this->definePluginMeta();
        $this->defineBlockFactoryHooks();
        $this->defineBlockActions();
        $this->defineBlockScripts();

        $this->defineRestRoutes();

        $this->tier->boot($this->loader);

        return $this;
    }

    private function defineSchemaUpgrade(): void
    {
        add_action('admin_init', [new DBSetup(), 'maybeUpgrade']);
    }

    public function defineRestRoutes()
    {
        $this->loader->add_action('rest_api_init', $this, 'registerDiscoveredRestRoutes');
    }

    public function registerDiscoveredRestRoutes(): void
    {

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

        add_filter( 'plugin_row_meta', [$this->pluginMeta, 'rowMeta'], 10, 2);
    }

    private function defineBlockScripts(): void {

        add_action('enqueue_block_editor_assets', [$this, 'scripts']);
    }

    public function editorVars(): array {
        
        return apply_filters('theghostlab_cycle_editor_vars', [
            'restNamespace' => PluginConfig::restNameSpace(),
        ]);
    }

    public function scripts(): void {

        wp_register_script( 'theghostlab-cycle-editor', '', [], PluginConfig::$version, true );

        wp_enqueue_script( 'theghostlab-cycle-editor'  );

        $script = 'window.theghostlab_cycle_vars = ' . wp_json_encode($this->editorVars(), JSON_HEX_TAG | JSON_HEX_AMP) . ';';

        wp_add_inline_script('theghostlab-cycle-editor', $script, 'before');
    }

    private function defineBlockActions(): void {
        $this->loader->add_action('save_post', $this->saveBlockAttributes, 'save', 50, 3);
    }

    private function defineBlockFactoryHooks(): void {
        $tags = $this->blockFactory->getActionTags();

        if( !$tags ) return;

        foreach ($tags as $tag) {
            add_action('init', $tag);
        }
    }

    public function run(): void {
        if(!$this->tier->gate()->allows()) return;

        $this->loader->run();

    }

}