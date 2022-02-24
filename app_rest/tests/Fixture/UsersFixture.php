<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    const LOAD = 'app.Users';
    const SELLER_ID = 2;

    public $records = [
        [
            'id' => self::SELLER_ID,
            'email' => 'seller@example.com',
            'firstname' => 'My Name',
            'lastname' => 'My Surname',
            'password' => '$2y$10$1cCayk8qquFFWyvk161qZuOm4kgLFbmg4O1ItVQ5Qt.w3V28VNUk2',
            'group_id' => 3,
            'created' => '2021-01-18 10:39:23',
            'modified' => '2021-01-18 10:41:31'
        ],
    ];
}
