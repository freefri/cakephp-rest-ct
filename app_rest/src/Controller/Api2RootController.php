<?php

namespace App\Controller;

use App\Lib\I18n\LegacyI18n;
use Cake\Cache\Cache;
use Cake\Routing\Router;

class Api2RootController extends Api2Controller
{
    public function isPublicController(): bool
    {
        return true;
    }

    protected function getMandatoryParams(): array
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
            'version' => '',
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
