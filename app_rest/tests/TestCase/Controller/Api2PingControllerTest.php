<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\Api2Controller;
use App\Controller\Api2PingController;
use App\Lib\Consts\Languages;
use App\Lib\I18n\LegacyI18n;

class Api2PingControllerTest extends Api2CommonErrorsTest
{
    protected $fixtures = [
        'app.Users'
    ];

    protected function _getEndpoint(): string
    {
        return Api2Controller::ROUTE_PREFIX . '/ping/';
    }

    public function testGetData_gets()
    {
        $lang = Languages::ENG;
        LegacyI18n::setLocale($lang);
        $this->get($this->_getEndpoint() . Api2PingController::SECRET . '?migrations=false');
        $this->assertJsonResponseOK();
        $bodyDecoded = json_decode($this->_getBodyAsString(), true);
        $this->assertEquals($lang, $bodyDecoded['data'][0]);
        $this->assertEquals('dev.example.com', $bodyDecoded['data'][1]);
        $this->assertEquals('use cache', $bodyDecoded['data'][3]);
    }

    public function testGetData_withoutSecret()
    {
        $this->get($this->_getEndpoint() . 'invalid');
        $this->assertResponseError($this->_getBodyAsString());
    }

    public function testAddNew()
    {
        $this->post($this->_getEndpoint(), ['hello' => 'world']);
        $this->assertResponseFailure($this->_getBodyAsString());
    }
}
