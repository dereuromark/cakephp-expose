<?php

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property \DateTimeInterface $created
 * @property \DateTimeInterface $modified
 */
class User extends Entity {

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
