<?php

namespace App\Lib\Helpers;

use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\ServerRequest;
use Cake\I18n\FrozenTime;
use DateTimeZone;

class CookieHelper
{
    const REMEMBER_NAME_API2 = 'rememberapi2';
    const ENCRIPT_METHOD = 'AES-256-CBC';
    const ENCRIPT_KEY = '2348980345538646';
    const ENCRIPT_IV = '5348214865486458';

    private function _getCookieName(): string
    {
        return 'app_rest';
    }

    public function writeApi2Remember($accessToken, $expires = null)
    {
        $encryptedToken = openssl_encrypt(
            $accessToken,
            self::ENCRIPT_METHOD,
            self::ENCRIPT_KEY,
            null,
            self::ENCRIPT_IV
        );
        if (!$expires) {
            $expires = Configure::read('Platform.User.rememberExpires');
        }
        $expirationTime = new FrozenTime("+ $expires seconds", new DateTimeZone('GMT'));
        $key = $this->_getCookieName() . '[' . self::REMEMBER_NAME_API2 . ']';
        return new Cookie($key, $encryptedToken, $expirationTime);
    }

    public function readApi2Remember(ServerRequest $request)
    {
        $token = $request->getCookie($this->_getCookieName() . '.' . self::REMEMBER_NAME_API2);
        return openssl_decrypt(
            $token,
            self::ENCRIPT_METHOD,
            self::ENCRIPT_KEY,
            null,
            self::ENCRIPT_IV
        );
    }

    public function popApi2Remember(ServerRequest $request)
    {
        $token = $this->readApi2Remember($request);
        $this->writeApi2Remember(time(), 1);
        return $token;
    }
}
