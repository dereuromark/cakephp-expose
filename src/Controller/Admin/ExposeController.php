<?php

namespace Expose\Controller\Admin;

use App\Controller\AppController;
use Expose\Reverser\Reverser;

class ExposeController extends AppController {

	/**
	 * @return void
	 */
	public function index(): void {
		if ($this->request->is('post')) {
			$result = null;

			$uuid = (string)$this->request->getData('uuid');
			$result = $this->reverseUuid($uuid);

			$this->set(compact('result'));
		}
	}

	/**
	 * @param string $uuid
	 *
	 * @return string|null
	 */
	protected function reverseUuid(string $uuid): ?string {
		return (new Reverser())->reverse($uuid);
	}

}
