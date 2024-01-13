<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Controller\Api2Controller;
use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class ApiCorsComponent extends Component
{
    public static function load(Controller $controller)
    {
        $controller->loadComponent('ApiCors');
    }

    public function beforeFilter(EventInterface $event)
    {
        /** @var Api2Controller $controller */
        $controller = $event->getSubject();
        if ($controller) {
            $response = $controller->getResponse();
            $response->withDisabledCache();

            $responseBuilder = $response->cors($controller->getRequest());

            $allowedCors = Configure::read('App.Cors.AllowOrigin');
            $currentOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $isAnyOriginAllowed = ($allowedCors[0] ?? null) === '*';
            $isSameCors = in_array($currentOrigin, $allowedCors);
            if ($currentOrigin && ($isAnyOriginAllowed || $isSameCors)) {
                $responseBuilder->allowOrigin([$currentOrigin])
                    ->allowCredentials();
            }
            if ($controller->getRequest()->is('options')) {
                $responseBuilder
                    ->allowMethods(['POST', 'GET', 'PATCH', 'PUT', 'DELETE'])
                    ->allowHeaders([
                        'Authorization',
                        'Content-Type',
                        'Accept-Language',
                        'X-Experience-API-Version'
                    ])
                    ->maxAge(3600);
                $response = $responseBuilder->build();
                $controller->setResponse($response);
                return $response;
            }
            $response = $responseBuilder->build();
            $controller->setResponse($response);
        }
    }
}
