<?php

namespace App\Controller;

use App\Lib\I18n\LegacyI18n;
use App\Model\Entity\User;
use Cake\Cache\Cache;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

class Api2RootController extends Api2Controller
{
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

    protected function getList()
    {
        Cache::write('testingCachePing', 'hello-cache-ping');
        $title = 'cake-rest-ct';
        $toRet = [
            'title' => $title,
            'lang' => LegacyI18n::getLocale(),
            'version' => $_SERVER['HTTP_HOST'],
            '_links' => [
                'self' => [
                    'title' => $title,
                    'href' => explode('?', Router::url(null, true))[0]
                ],
                'documentation' => [
                    'title' => 'API docs',
                    'href' => 'https://courseticket-nilus.gitlab.io/API-v2'
                ],
                'old_documentation' => [
                    'title' => 'API docs - deprecated',
                    'href' => 'https://courseticket-nilus.gitlab.io/API-v1'
                ]
            ],
        ];
        $this->return = $toRet;
    }
}
