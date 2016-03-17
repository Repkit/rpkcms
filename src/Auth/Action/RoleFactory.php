<?php

namespace Auth\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Auth\Storage\StorageInterface;

class RoleFactory
{
    public function __invoke(ContainerInterface $Container)
    {
        $pdo      = $Container->get(StorageInterface::class);
        $router   = $Container->get(RouterInterface::class);
        $template = ($Container->has(TemplateRendererInterface::class))
            ? $Container->get(TemplateRendererInterface::class)
            : null;

        return new RoleAction($pdo, $router, $template);
    }
}
