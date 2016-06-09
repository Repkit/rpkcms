<?php

namespace Page;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Page\Storage\StorageInterface;

class TwigExtensionFactory
{
    public function __invoke(ContainerInterface $Container)
    {
        $pdo      = $Container->get(StorageInterface::class);

        return new TwigExtension($pdo);
    }
}
