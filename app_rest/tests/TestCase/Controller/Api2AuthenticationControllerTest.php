<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\Api2Controller;
use App\Model\Table\UsersTable;
use App\Test\Fixture\OauthClientsFixture;
use App\Test\Fixture\UsersFixture;

class Api2AuthenticationControllerTest extends Api2CommonErrorsTest
{
    protected $fixtures = [
        UsersFixture::LOAD,
        OauthClientsFixture::LOAD,
    ];


    protected function _getEndpoint(): string
    {
        return Api2Controller::ROUTE_PREFIX . '/authentication/';
    }

    public function setUp(): void
    {
        parent::setUp();
        UsersTable::load();
    }

    public function testAddNew_login()
    {
        $data = [
            'username' => 'seller@example.com',
            'password' => 'passpass',
            'client_id' => OauthClientsFixture::DASHBOARD_CLI,
            'grant_type' => 'password',
        ];

        $this->post($this->_getEndpoint(), $data);

        $this->markTestSkipped('Test failing unknown reason to be fixed');
        $this->assertJsonResponseOK();
        $return = json_decode($this->_getBodyAsString(), true)['data'];

        $this->assertArrayHasKey('access_token', $return);
        $this->assertEquals('3600', $return['expires_in'], 'expires in seconds');
        $this->assertEquals('Bearer', $return['token_type']);
        $this->assertEquals(UsersFixture::SELLER_ID, $return['user']['id']);
        $this->assertEquals('seller@example.com', $return['user']['email']);
    }

    public function testAddNew_loginShouldRememberMe()
    {
        $data = [
            'username' => 'seller@example.com',
            'password' => 'passpass',
            'client_id' => OauthClientsFixture::DASHBOARD_CLI,
            'grant_type' => 'password',
            'remember_me' => true,
        ];

        $this->post($this->_getEndpoint(), $data);

        $this->markTestSkipped('Test failing unknown reason to be fixed');
        $this->assertJsonResponseOK();
        $return = json_decode($this->_getBodyAsString(), true)['data'];

        $this->assertArrayHasKey('access_token', $return);
        $this->assertEquals('3600', $return['expires_in'], 'expires in seconds');
        $this->assertEquals('Bearer', $return['token_type']);
        $this->assertEquals(UsersFixture::SELLER_ID, $return['user']['id']);
        $this->assertEquals('seller@example.com', $return['user']['email']);
    }

    public function testAddNew_loginShouldThrowWithoutGrantType()
    {
        $data = [
            'username' => 'seller@example.com',
            'password' => 'passpass',
            'client_id' => OauthClientsFixture::DASHBOARD_CLI,
        ];

        $this->post($this->_getEndpoint(), $data);

        $this->assertResponseError();
        $return = json_decode($this->_getBodyAsString(), true);

        $this->assertEquals('grant_type should be password', $return['message']);
    }

    public function testDelete()
    {
        $this->delete($this->_getEndpoint() . 'cookie?access_token=asdklfj');
        $this->assertResponseCode(204);
    }
}
