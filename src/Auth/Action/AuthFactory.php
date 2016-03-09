<?php
namespace Auth\Action;

class AuthFactory
{
    public function __invoke($services)
    {
        $config = $services->get('config');
        $config = $config['opauth'];
        // var_dump($config);exit(__CLASS__);
        return new AuthAction($config);
    }
}
