<?php

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $public_identifier
 * @property string $name
 * @property \DateTimeInterface $created
 * @property \DateTimeInterface $modified
 */
class CustomFieldRecord extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * @var array
	 */
	protected array $_accessible = [
		'*' => true,
		'public_identifier' => false,
		'id' => false,
	];

}
