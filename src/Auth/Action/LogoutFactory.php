<?php
namespace Auth\Action;

class LogoutFactory
{
    public function __invoke($services)
    {
        $config = $services->get('config');
        $config = $config['opauth'];
        return new LogoutAction($config);
    }
}
