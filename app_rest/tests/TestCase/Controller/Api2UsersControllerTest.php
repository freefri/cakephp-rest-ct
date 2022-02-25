<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\UsersTable;

class Api2UsersControllerTest extends Api2CommonErrorsTest
{
    protected $fixtures = [
        'app.Users'
    ];

    protected function _getEndpoint(): string
    {
        return '/api/v2/users/';
    }

    public function testAddNew_InputData()
    {
        $data = [
            'email'=> 'test@example.com',
            'firstname'=> 'Test',
            'lastname'=> 'Last',
            'password'=> 'passpass'
        ];

        $this->post($this->_getEndpoint(), $data);

        $this->assertResponseOk($this->_getBodyAsString());
        $return = json_decode($this->_getBodyAsString(), true)['data'];

        $this->assertEquals($data['email'], $return['email']);
        $this->assertEquals($data['firstname'], $return['firstname']);
        $this->assertEquals($data['lastname'], $return['lastname']);
        $this->assertStringStartsWith('$2y$10$', $return['password']);
    }
}
