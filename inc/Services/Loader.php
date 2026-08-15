<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

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

	/** @var list<HookRegistration> */
	protected array $actions;

	/** @var list<HookRegistration> */
	protected array $filters;

	public function __construct() {

		$this->actions = [];
		$this->filters = [];
	}
	
	/**
	 * @param string $hook
	 * @param object $component
	 * @param string $callback Method name on $component.
	 * @param int    $priority
	 * @param int    $accepted_args
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}
	
	/**
	 * @param string $hook
	 * @param object $component
	 * @param string $callback Method name on $component.
	 * @param int    $priority
	 * @param int    $accepted_args
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}
	
	/**
	 * @param list<HookRegistration> $hooks
	 * @param string $hook
	 * @param object $component
	 * @param string $callback
	 * @param int    $priority
	 * @param int    $accepted_args
	 * @return list<HookRegistration>
	 */
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
	
	/**
	 * @return void
	 */
	public function run() {

		// The [$component, $callback] pairs below stay baselined in PHPStan. The
		// component is any service object and the callback is a method name resolved
		// at runtime, so array{object, string} cannot be proven callable without
		// enumerating every component class. That is the point of this registry.
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}

	}
}