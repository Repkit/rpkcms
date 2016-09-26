<?php

return [
    
    'dependencies' => [
        // 'invokables' => [
        //     Plugin\Paginator\Paginator::class => Plugin\Paginator\Paginator::class,
        // ],
        'factories' => [
            Plugin\Paginator\Paginator::class => Plugin\Paginator\PaginatorFactory::class,
        ],
    ],
    
    'plugin-manager' => [
        // page related events
        'page::add-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page::add-insert.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page::add-insert.post' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page::edit-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page::edit-update.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page::cache-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        
        // template related events
        /*'page/template::add-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page/template::add-insert.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page/template::add-insert.post' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page/template::edit-render.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ],
        'page/template::edit-update.pre' => [
            new \RpkPluginManager\Plugin(Plugin\Paginator\Paginator::class),
        ]*/
    ],
];