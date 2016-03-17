<?php

use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\PhpFileProvider;

$configManager = new ConfigManager([
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    Auth\ModuleConfig::class,
    Page\ModuleConfig::class,
], 'data/config-cache.php');

// $cfg = new ArrayObject($configManager->getMergedConfig());
// var_dump($cfg['middleware_pipeline']);exit();
return new ArrayObject($configManager->getMergedConfig());