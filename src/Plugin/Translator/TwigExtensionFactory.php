<?php

namespace Plugin\Translator;

use Interop\Container\ContainerInterface;
use Page\Storage\StorageInterface;

class TwigExtensionFactory
{
    public function __invoke(ContainerInterface $Container)
    {
        $pdo      = $Container->get(StorageInterface::class);

        return new TwigExtension($pdo);
    }
}
