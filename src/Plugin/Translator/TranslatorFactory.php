<?php

namespace Plugin\Translator;

use Interop\Container\ContainerInterface;
use Page\Storage\StorageInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
// use Zend\Expressive\Router\RouterInterface;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $Container)
    {
        $pdo = $Container->get(StorageInterface::class);
        $config = $Container->get('config');
        $pageConf = $config['page'];
        unset($config);
        
        // $router   = $Container->get(RouterInterface::class);
        $template = ($Container->has(TemplateRendererInterface::class))
            ? $Container->get(TemplateRendererInterface::class)
            : null;

        return new Translator($pdo, $pageConf, $template);
    }
}
