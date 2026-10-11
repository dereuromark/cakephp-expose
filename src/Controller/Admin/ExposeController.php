<?php

namespace Expose\Controller\Admin;

use App\Controller\AppController;
use Expose\Reverser\Reverser;
use Psr\Http\Message\UploadedFileInterface;

class ExposeController extends AppController {

	/**
	 * @return void
	 */
	public function index(): void {
		if ($this->request->is('post')) {
			$result = null;

			$uuid = (string)$this->request->getData('uuid');
			$file = $this->request->getData('file');
			if ($file instanceof UploadedFileInterface && $file->getError() === UPLOAD_ERR_OK) {
				$content = $file->getStream()->getContents();
				if (strlen((string)$content) === 16) {
					$uuid = $content;
				}
			}

			$result = $this->reverseUuid($uuid);

			$this->set(['result' => $result]);
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
