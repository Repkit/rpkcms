<?php

namespace Auth\Middleware;

use Zend\Expressive\Router\RouterInterface;

class AuthorizeFactory
{
    public function __invoke($services)
    {
        $router = $services->get(RouterInterface::class);
        $config = $services->get('config');
        $config = $config['auth'];
        // var_dump($config);exit(__FILE__.'::'.__LINE__);
        return new Authorize($router, $config);
    }
}