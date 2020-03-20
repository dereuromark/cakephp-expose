<?php

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $content
 * @property \DateTimeInterface $created
 * @property \DateTimeInterface $modified
 */
class Post extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'uuid' => false,
		'id' => false,
	];

}
