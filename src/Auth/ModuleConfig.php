<?php

namespace Auth;

class ModuleConfig
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    Action\AuthCallbackAction::class    => Action\AuthCallbackFactory::class,
                    Action\AuthAction::class            => Action\AuthFactory::class,
                    Action\LogoutAction::class          => Action\LogoutFactory::class,
                    Middleware\Auth::class              => Middleware\Auth::class,
                    Middleware\Authorize::class         => Middleware\AuthorizeFactory::class,
                    
                    //user related
                    Storage\StorageInterface::class     => Storage\Adapter\PdoFactory::class,
                    Middleware\User::class              => Middleware\UserFactory::class,
                    Action\RoleAction::class            => Action\RoleFactory::class,
                    Action\UserAction::class            => Action\UserFactory::class,
                ],
            ],
            'middleware_pipeline' => [
                'auth' => [
                    'path'       => '/auth',
                    'middleware' => Middleware\Auth::class,
                    'priority'   => 10,
                ],
            ],
            //https://github.com/opauth/opauth/wiki/Opauth-configuration
            'opauth' => [
                'path'                  => '/auth/',
                'debug'                 => false,
                'callback_url'          => '/auth/callback',
                'callback_transport'    => 'session',
                'security_salt'         => 'beuRGx1rZ27GUgwCCG1sG4dD',
                'security_iteration'    => '300', //Higher value, better security, slower hashing.
                'security_timeout'      => '2 minutes',
                'strategy_dir'          => __DIR__.'/Strategy/',
                'Strategy'              => [
                    'Google' => require dirname(__FILE__).'/Strategy/Google/strategy.config.php',
                ],
            ],
            'twig' => [
                'globals' => [
                    'auth' => !empty($_SESSION['auth'])?$_SESSION['auth']:null,
                ],
            ],
            'routes' => [
                'admin-auth-role' => [
                    'name' => 'admin.auth.role',
                    'path' => '/admin/auth/role[/{action:add|edit}[/{id}]]',
                    'middleware' => Action\RoleAction::class,
                    'allowed_methods' => ['GET','POST'],
                ],
                'admin-auth-user' => [
                    'name' => 'admin.auth.user',
                    'path' => '/admin/auth/user[/{action:add|edit}[/{id}]]',
                    'middleware' => Action\UserAction::class,
                    'allowed_methods' => ['GET','POST'],
                ],
            ],
            'templates' => [
                'paths'     => [
                    'user'      => ['templates/auth/user'],
                    'role'      => ['templates/auth/role'],
                    'user-role' => ['templates/auth/user'],
                ],
            ],
            'auth' => [
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
                'mapper' => [
                    'google' => Storage\Mapper\Google::class     
                ],
                'secretKey' => 'pQnaB}Dw(<S)VY`j:2M/%',
            ],
        ];
    }
}
