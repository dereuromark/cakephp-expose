<?php

namespace Expose\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use RuntimeException;

/**
 * Allows CRUD actions to stay as they are for Expose to work.
 * This should only be added to the actions necessary (no admin backend etc).
 */
class SuperimposeComponent extends Component {

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'modifyResult' => false,
		'actions' => [],
	];

	/**
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		$currentAction = $this->getController()->getRequest()->getParam('action');
		$actions = $this->_config['actions'];

		if ($actions && !in_array($currentAction, $actions, true)) {
			return;
		}

		$modelName = $this->getController()->loadModel()->getAlias();
		if (!$this->getController()->$modelName->hasBehavior('Expose')) {
			throw new RuntimeException('Expose.Expose behavior must be attached to a model that uses superimposing.');
		}

		$config = [
			'modifyResult' => $this->getConfig('modifyResult'),
		];
		$this->getController()->$modelName->addBehavior('Expose.Superimpose', $config);
	}

}
