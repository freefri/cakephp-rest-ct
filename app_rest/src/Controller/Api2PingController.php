<?php

namespace App\Controller;

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

    public function initialize(): void
    {
        parent::initialize();
    }

    protected function getMandatoryParams()
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
        Cache::write('testingCachePing', 'hello-cache-ping');
        $toRet = [
            '0' => LegacyI18n::getLocale(),
            '1' => $_SERVER['HTTP_HOST'],
            '2' => $_SERVER['APPLICATION_ENV'],
            '3' => new FrozenTime(),
        ];
        $this->return = $toRet;
    }
}
