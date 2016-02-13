<?php

use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\PhpFileProvider;

$configManager = new ConfigManager([
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    Test\ModuleConfig::class,
], 'data/config-cache.php');

return new ArrayObject($configManager->getMergedConfig());