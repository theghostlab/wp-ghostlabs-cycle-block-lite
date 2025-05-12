<?php

namespace THEGHOSTLAB\CYCLE;

if ( ! defined( 'ABSPATH' ) ) exit;

use THEGHOSTLAB\CYCLE\Services\ActionsService;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Services\BlockFactory;
use THEGHOSTLAB\CYCLE\Services\DBService;
use THEGHOSTLAB\CYCLE\Services\Loader;
use THEGHOSTLAB\CYCLE\Services\NoticeService;
use THEGHOSTLAB\CYCLE\RestRoutes\RestRouteInterface;
use THEGHOSTLAB\CYCLE\Services\SaveBlockAttributes;
use THEGHOSTLAB\CYCLE\Services\Utils;
use THEGHOSTLAB\CYCLE\Traits\Singleton;
use WP_Error;
use WpOrg\Requests\Exception;

final class KERNEL
{
    use Singleton;

    private BlockFactory $blockFactory;
    private Loader $loader;
    private PluginInfo $pluginInfo;
    private SaveBlockAttributes $saveBlockAttributes;
	private NoticeService $noticeService;
	private Utils $utils;

    /**
     * @throws Exception
     */
    public function init() : KERNEL {

        $this->utils                = new Utils();
        $this->blockFactory         = new BlockFactory();
        $this->loader               = new Loader();
        $this->pluginInfo           = new PluginInfo();
        $this->saveBlockAttributes  = new SaveBlockAttributes();
		$this->noticeService        = new NoticeService(new DBService());
									  new ActionsService();

		$this->defineNotices();
        $this->defineBlockFactoryHooks();
        $this->defineBlockActions();
        $this->defineBlockScripts();
        $this->defineBlockStyles();
		$this->defineRestRoutes();

        return $this;
    }

	/**
	 * @uses \THEGHOSTLAB\CYCLE\RestRoutes\NoticeRestRoutes
	 * @uses \THEGHOSTLAB\CYCLE\RestRoutes\CycleRestRoutes
	 * @return void
	 */
	public function defineRestRoutes()
	{
		$dir = sprintf("%sinc/classes", THEGHOSTLAB_CYCLE_PLUGIN_PATH);

		$generated = $this->utils->getClasses($dir, RestRouteInterface::class);

		foreach ($generated as $class) {
			$init = new $class();
			$this->loader->add_action('rest_api_init', $init, 'routes');
		}
	}

    private function defineBlockScripts() {
        add_action('admin_enqueue_scripts', [$this, 'scripts']);
    }

    private function defineBlockStyles() {
        add_action('wp_enqueue_scripts', [$this, 'styles']);
    }

    public function styles()
    {
	    $version = sprintf('%s', time());

        wp_register_style( 'theghostlab-cycle', THEGHOSTLAB_CYCLE_PLUGIN_URL.'/css/theghostlab-cycle.css', [], $version );
        wp_enqueue_style( 'theghostlab-cycle' );
    }

    public function scripts() {

	    $version = sprintf('%s', time());

        wp_register_script( 'theghostlab-cycle-editor', '', [], $version, true );
        wp_enqueue_script( 'theghostlab-cycle-editor' );

        $script = 'const theghostlab_cycle_vars = {
            adminUrl: "'.esc_url( admin_url() ).'",
            promoLink: "'.esc_url( PluginInfo::$promoLink ).'",
            nonce: "'.esc_attr( wp_create_nonce( PluginInfo::$nonceAction ) ).'",
            siteSlug: "'.esc_attr( sanitize_title( get_bloginfo('name') ) ).'",
        }';

        wp_add_inline_script('theghostlab-cycle-editor', $script, 'before');

	    wp_register_script( 'theghostlab-cycle-lite-notices', sprintf('%s/js/notice-bundle.js',THEGHOSTLAB_CYCLE_PLUGIN_URL), ['wp-data','wp-api-fetch', 'wp-api-request'], $version, true );
	    wp_enqueue_script( 'theghostlab-cycle-lite-notices' );
    }

    private function defineBlockActions(){
        $this->loader->add_action('save_post', $this->saveBlockAttributes, 'save', 50, 3);
    }

    /**
     * @throws Exception
     */
    private function defineBlockFactoryHooks() {
        $tags = $this->blockFactory->getActionTags();

		/** @var WP_Error[] $hasErrors */
	    $hasErrors = array_values(array_filter($tags, fn($obj) => is_wp_error($obj)));

		if(!empty($hasErrors)) {
			foreach($hasErrors as $error) {
				echo esc_html($error->get_error_message());
			}

			return;
		}

        foreach ($tags as $tag) {
            add_action('init', $tag);
        }
    }

    public function run() {

        $this->loader->run();
    }

	private function defineNotices() {
		$this->loader->add_action('admin_notices', $this->noticeService, 'upgradeNotice');
	}
}