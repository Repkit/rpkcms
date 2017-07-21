<?php

return [
    
    'dependencies' => [
        'factories' => [
            Plugin\Translator\Translator::class => Plugin\Translator\TranslatorFactory::class,
            Plugin\Translator\TwigExtension::class => Plugin\Translator\TwigExtensionFactory::class,
        ],
    ],
    
    'plugin-manager' => [
        // page related events
        'page::add-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
        'page::add-insert.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
        'page::add-insert.post' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
        'page::edit-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
        'page::edit-update.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Translator\Translator::class),
        ],
    ],
    'twig' => [
        'extensions'     => [
            Plugin\Translator\TwigExtension::class,
        ],
    ],
];