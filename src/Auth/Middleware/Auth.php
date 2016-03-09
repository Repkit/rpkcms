<?php

namespace Auth\Middleware;

use Zend\Expressive\AppFactory;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Auth\Action;

class Auth
{
    public function __invoke($Services)
    {
        $middleware = AppFactory::create($Services);

        $middleware->route('/callback', [
            BodyParamsMiddleware::class,
            Action\AuthCallbackAction::class,
        ], ['GET', 'POST']);
        
        $config = $Services->get('config');
        $strategies = $config['opauth']['Strategy'];
        // var_dump($strategies);exit('$strategies');
        
        foreach ($strategies as $strategy) {
            // var_dump($strategy);exit('$strategy');
            $middleware->get($strategy['path'], Action\AuthAction::class);
            $middleware->get($strategy['callback_url'], Action\AuthAction::class);
        }
        
        $middleware->get('/logout', Action\LogoutAction::class);

        $middleware->pipeRoutingMiddleware();
        $middleware->pipeDispatchMiddleware();

        return $middleware;
    }
}