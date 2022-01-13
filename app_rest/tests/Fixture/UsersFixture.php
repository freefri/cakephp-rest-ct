<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public $records = [
        [
            'id' => 1,
            'email' => 'test@example.com',
            'firstname' => 'My Name',
            'lastname' => 'My Surname',
            'password' => '$2a$10$5XHCbLVU4Z15o3tSsn6BD.vRO6zmSk34kj2lkjsdklfjwiejo2lke',
            'group_id' => 3,
            'created' => '2021-01-18 10:39:23',
            'modified' => '2021-01-18 10:41:31'
        ],
    ];
}
