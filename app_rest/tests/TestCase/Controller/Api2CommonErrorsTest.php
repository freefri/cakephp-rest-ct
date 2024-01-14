<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Lib\Consts\CacheGrp;
use App\Test\Fixture\OauthAccessTokensFixture;
use Cake\Cache\Cache;
use RestApi\TestSuite\ApiCommonErrorsTest;

abstract class Api2CommonErrorsTest extends ApiCommonErrorsTest
{
    protected function clearUserCache()
    {
        Cache::clear(CacheGrp::EXTRALONG);
        Cache::delete('_getFirst1', CacheGrp::EXTRALONG);
    }

    public function setUp(): void
    {
        parent::setUp();
        if (!$this->currentAccessToken) {
            $this->currentAccessToken = OauthAccessTokensFixture::ACCESS_TOKEN_SELLER;
        }
        $_SERVER['HTTP_ORIGIN'] = 'http://dev.example.com';
        $this->loadAuthToken($this->currentAccessToken);
    }
}
