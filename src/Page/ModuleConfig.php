<?php

namespace Page;

class ModuleConfig
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'invokables' => [
                    
                ],
                'factories' => [
                    Storage\StorageInterface::class => Storage\Adapter\PdoFactory::class,
                    Action\CacheAction::class =>
                        Action\CacheFactory::class,
                    Action\PageAction::class =>
                        Action\PageFactory::class,    
                ],
            ],
            'routes' => [
                'home'=>[
                    // 'name' => 'home',
                    // 'path' => '/[{lang:[a-z]{2}}[/[{page:.+}]]]',
                    'path' => '/[{page}]',
                    'middleware' => Action\CacheAction::class,
                    // 'allowed_methods' => ['GET'],
                ],
                'admin-page' => [
                    'name' => 'admin-page',
                    'path' => '/admin/page[/{action:add|edit}[/{id}]]',
                    'middleware' => Action\PageAction::class,
                    'allowed_methods' => ['GET'],
                ],
            ],
            'templates' => [
                'paths'     => [
                    'page'    => ['templates/page'],
                ],
            ],
            'page' => [
                'storage' => [
                    'connection_string' => null, //if this is set connection_data is ignored
                    'connection_data' => [
                        'adapter'     => 'mysql',
                        //'unix_socket' => 'path/to/unix',
                        // 'host'        => 'localhost(127.0.0.1)',
                        'host'        => 'localhost',
                        'username'    => 'root',
                        'password'    => '',
                        'dbname'      => 'rpkcms',
                    ],
                    'attributes'  => [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => 
                            "SET NAMES utf8; SET time_zone = 'Europe/Bucharest'"
                    ],
                ],
                'cache' => [
                    'path' => getcwd().'/public/data/cache/html/',
                ],
            ],
        ];
    }
}
