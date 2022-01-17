<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

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
            'firstname'=> 'Alex',
            'lastname'=> 'Gomez',
            'password'=> 'passpass'
        ];

        $this->post($this->_getEndpoint(), $data);

        $this->assertResponseOk($this->_getBodyAsString());

        $this->assertEquals('test@example.com', $data['email']);
        $this->assertEquals('Alex', $data['firstname']);
        $this->assertEquals('Gomez', $data['lastname']);
        $this->assertEquals('passpass', $data['password']);
    }
}
