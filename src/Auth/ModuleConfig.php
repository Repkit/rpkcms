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
                ],
            ],
            // 'routes' => [],
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
                    'Google' => require dirname(__FILE__).'Google/strategy.config.php',
                ],
            ],
            'twig' => [
                'globals' => [
                    'auth' => $_SESSION['auth'],
                ],
            ],
        ];
    }
}
