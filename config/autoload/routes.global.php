<?php

return [
    'dependencies' => [
        'invokables' => [
            // Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            Zend\Expressive\Router\RouterInterface::class => Page\FastRouteRouter::class,
            App\Action\PingAction::class => App\Action\PingAction::class,
        ],
        'factories' => [
            App\Action\HomePageAction::class => App\Action\HomePageFactory::class,
        ],
    ],

    'routes' => [
        'home' => [
            'name' => 'home',
            'path' => '/',
            'middleware' => App\Action\HomePageAction::class,
            'allowed_methods' => ['GET'],
        ],
        'api.ping' => [
            'name' => 'api.ping',
            'path' => '/api/ping',
            'middleware' => App\Action\PingAction::class,
            'allowed_methods' => ['GET'],
        ],
    ],
];
