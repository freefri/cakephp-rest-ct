<?php

namespace App\Controller;

use App\Lib\Consts\CacheGrp;
use App\Lib\I18n\LegacyI18n;
use Cake\Cache\Cache;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\FrozenTime;

class Api2PingController extends Api2Controller
{
    const SECRET = 'pong';

    public function isPublicController(): bool
    {
        return true;
    }

    protected function getMandatoryParams(): array
    {
        return [];
    }

    protected function getData($id)
    {
        if ($id >= 400 && $id < 600) {
            throw new BadRequestException('Rendering exception', $id);
        }
        if ($id != self::SECRET) {
            throw new BadRequestException('Invalid ping');
        }
        Cache::write('testingCachePing', 'hello-cache-ping', CacheGrp::DEFAULT);
        if (Cache::read('testingCachePing') == 'hello-cache-ping') {
            $cache = 'use cache';
        } else {
            $cache = 'errorCache';
        }
        $toRet = [
            '0' => LegacyI18n::getLocale(),
            '1' => env('HTTP_HOST', ''),
            '2' => env('APPLICATION_ENV', ''),
            '3' => $cache,
            '4' => new FrozenTime(),
        ];
        $this->return = $toRet;
    }
}
