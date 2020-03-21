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
	protected $middlewareEnabled = false;

	/**
	 * @var bool
	 */
	protected $bootstrapEnabled = false;

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		$routes->prefix('Admin', function (RouteBuilder $routes) {
			$routes->plugin('Expose', function (RouteBuilder $routes) {
				$routes->connect('/', ['controller' => 'Expose', 'action' => 'index']);

				$routes->fallbacks();
			});
		});
	}

}
