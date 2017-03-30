<?php

return [
    
    'dependencies' => [
        'factories' => [
            Plugin\Translator\Translator::class => Plugin\Translator\TranslatorFactory::class,
        ],
    ],
    
    'plugin-manager' => [
        // page related events
        'page::edit-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
        'page::edit-update.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
    ],
];