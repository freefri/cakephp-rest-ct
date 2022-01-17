<?php

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    $routes->scope('/api/v2', function (RouteBuilder $builder) {
        // Register scoped middleware for in scopes.
        $builder->registerMiddleware('csrf', new CsrfProtectionMiddleware([
            'httponly' => true,
        ]));
        $builder->connect('/ping/*', \App\Controller\Api2PingController::route());
        $builder->connect('/users/*', \App\Controller\Api2UsersController::route());
        $builder->connect('/authentication/*', \App\Controller\Api2AuthenticationController::route());
    });

    $routes->setRouteClass(DashedRoute::class);
    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->connect('/api', \App\Controller\Api2RootController::route());
        $builder->connect('/', \App\Controller\Api2RootController::route());
        $builder->fallbacks();
    });
};
