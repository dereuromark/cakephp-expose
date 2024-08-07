<?php

namespace TestApp\Controller;

use Cake\Http\Response;

/**
 * @property \TestApp\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Expose.Superimpose');
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function add() {
		$post = $this->Posts->newEmptyEntity();
		if ($this->request->is('post')) {
			$post = $this->Posts->patchEntity($post, $this->request->getData());
			if ($this->Posts->save($post)) {
				// Success

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The record could not be saved. Please, try again.'));
		}
	}

	/**
	 * Edit method
	 *
	 * @param string|int|null $id
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function edit($id = null) {
		$post = $this->Posts->get($id);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$post = $this->Posts->patchEntity($post, $this->request->getData());
			if ($this->Posts->save($post)) {
				// Success

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The record could not be saved. Please, try again.'));
		}
	}

	/**
	 * Delete method
	 *
	 * @param string|int|null $id
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function delete($id = null): ?Response {
		$this->request->allowMethod(['post', 'delete']);
		$post = $this->Posts->get($id);
		if ($this->Posts->delete($post)) {
			// Success
		} else {
			$this->Flash->error(__('The record could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
