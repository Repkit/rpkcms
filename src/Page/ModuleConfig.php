<?php

namespace Page;

class ModuleConfig
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                // 'invokables' => [],
                'factories' => [
                    Storage\StorageInterface::class => Storage\Adapter\PdoFactory::class,
                    Action\CacheAction::class =>
                        Action\CacheFactory::class,
                    Action\PageAction::class =>
                        Action\PageFactory::class,
                    Action\CategoryAction::class =>
                        Action\CategoryFactory::class,
                    Action\TemplateAction::class =>
                        Action\TemplateFactory::class,
                    TwigExtension::class =>
                        TwigExtensionFactory::class,    
                ],
            ],
            //middleware for sub directories that are not caught by home route
            'middleware_pipeline' => [
                'not-found' => [
                    'middleware' => Action\CacheAction::class,
                    'priority' => -10000,
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
                    'name' => 'admin.page',
                    'path' => '/admin/page[/{action:add|edit|preview}[/{id}]]',
                    'middleware' => Action\PageAction::class,
                    'allowed_methods' => ['GET','POST'],
                ],
                'admin-page-category' => [
                    'name' => 'admin.page-category',
                    'path' => '/admin/page-category[/{action:add|edit}[/{id}]]',
                    'middleware' => Action\CategoryAction::class,
                    'allowed_methods' => ['GET','POST'],
                ],
                'admin-page-template' => [
                    'name' => 'admin.page-template',
                    'path' => '/admin/page-template[/{action:add|edit}[/{id}]]',
                    'middleware' => Action\TemplateAction::class,
                    'allowed_methods' => ['GET','POST'],
                ],
            ],
            'templates' => [
                'paths'     => [
                    'page'      => ['templates/page'],
                    // 'theme'     => ['templates/theme'],
                    'theme'     => [static::themepath()],
                    'templates' => ['templates'],
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
                        // 'port'        => 3306,
                        'username'    => 'root',
                        'password'    => '',
                        'dbname'      => 'rpkcms',
                    ],
                    'attributes'  => [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => 
                            "SET NAMES utf8; SET time_zone = 'Europe/Bucharest'"
                    ],
                ],
                'cache' => [
                    // 'path' => getcwd().'/public/data/cache/html/',
                    'path' => static::cachepath(),
                ],
            ],
            'twig' => [
                'extensions'     => [
                    TwigExtension::class,
                    new \Twig_Extension_StringLoader(),
                ],
            ],
        ];
    }
    
    public static function cachepath()
    {
        return getcwd().'/public/data/cache/html/';       
    } 
    
    public static function themepath($short = false)
    {
        if($short){
            return 'theme';
        }
        return getcwd().'/templates/theme';
    } 
}
