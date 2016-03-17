<?php

namespace Auth\Middleware;

use Interop\Container\ContainerInterface;
use Auth\Storage\StorageInterface;

class UserFactory
{
    public function __invoke(ContainerInterface $Container)
    {
        $pdo = $Container->get(StorageInterface::class);
        $config = $Container->get('config');
        $config = $config['auth'];
        return new User($pdo, $config);
    }
}