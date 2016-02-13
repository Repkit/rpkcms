<?php

namespace Test;

class ModuleConfig
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    Action\HomePageAction::class =>
                        Action\HomePageFactory::class,
                    Action\AdminPageAction::class =>
                        Action\AdminPageFactory::class,
                    Action\CacheAction::class =>
                        Action\CacheFactory::class,
                ],
            ],
            'routes' => [
                [
                    'name' => 'test-home',
                    'path' => '/test',
                    'middleware' => Action\HomePageAction::class,
                    'allowed_methods' => ['GET'],
                ],
                [
                    'name' => 'test-admin',
                    'path' => '/admin/test[/{action:add|edit}[/{id}]]',
                    'middleware' => Action\AdminPageAction::class,
                    'allowed_methods' => ['GET'],
                ],
                'home'=>[
                    'name' => 'home',
                    'path' => '/[{lang:[a-z]{2}}[/[{page:.+}]]]',
                    'middleware' => Action\CacheAction::class,
                    // 'allowed_methods' => ['GET'],
                ],
            ],
            'templates' => [
                'paths'     => [
                    'test'    => ['templates/test'],
                ],
            ],
        ];
    }
}
