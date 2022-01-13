<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property string firstname
 * @property string lastname
 * @property string email
 */
class User extends Entity
{
    public function __construct(array $properties = [], array $options = [])
    {
        parent::__construct($properties, $options);
    }

    protected $_accessible = [
        '*' => false,
        'id' => false,

        'password' => true,
        'email' => true,
        'firstname' => true,
        'lastname' => true,
    ];
}
