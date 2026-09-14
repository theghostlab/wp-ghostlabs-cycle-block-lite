<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Queues WordPress hooks so Kernel::run() can register them only once the
 * edition's gate allows. Anything added here is gated by construction.
 *
 * @phpstan-type HookRegistration array{
 *     hook: string,
 *     component: object,
 *     callback: string,
 *     priority: int,
 *     accepted_args: int
 * }
 */
class Loader {

	protected array $actions;

	protected array $filters;

	public function __construct() {

		$this->actions = [];
		$this->filters = [];
	}

	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		];

		return $hooks;

	}

	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}

	}
}