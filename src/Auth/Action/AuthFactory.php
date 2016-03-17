<?php
namespace Auth\Action;

class AuthFactory
{
    public function __invoke($services)
    {
        $config = $services->get('config');
        $config = $config['opauth'];
        return new AuthAction($config);
    }
}
