<?php

namespace Expose;

use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;

/**
 * Plugin for Expose
 */
class Plugin extends BasePlugin {

	/**
	 * @var bool
	 */
	protected bool $middlewareEnabled = false;

	/**
	 * @var bool
	 */
	protected bool $bootstrapEnabled = false;

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		$routes->prefix('Admin', function (RouteBuilder $routes): void {
			$routes->plugin('Expose', function (RouteBuilder $routes): void {
				$routes->connect('/', ['controller' => 'Expose', 'action' => 'index']);

				$routes->fallbacks();
			});
		});
	}

}
