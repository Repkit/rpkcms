<?php

namespace Plugin;

use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\PhpFileProvider;
use Zend\Stdlib\ArrayUtils;

class ModuleConfig
{
    public function __invoke()
    {
        $cachedConfigFile = 'data/cache/plugin-manager-config-cache.php';
        
        if (null !== $cachedConfigFile && is_file($cachedConfigFile)) {
            
            // Try to load the cached config
            $config = require $cachedConfigFile;
            
        }else{
            
            $config = [ 'plugin-manager' => [] ];
            $plugins = glob(__DIR__.'/*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_MARK);
            foreach($plugins as $index => $plugin){
                $config = ArrayUtils::merge($config, include $plugin.'config.php');
            }
            $plugins = null;
            unset($plugins);
            
            if(defined('PRODUCTION')){
                if(PRODUCTION){
                    // cache config file
                    file_put_contents($cachedConfigFile, '<?php return ' . var_export($config, true) . ";\n");
                }
            }
            
        }
        
        
        return $config;
        
    }
}
