<?php

/*first is checked middleware and if passed by this auth route came into action*/
// https://github.com/zendframework/zend-expressive/issues/249
return [
    'auth' => [
        'authorize' => [
            'middleware' => [
                'Page\Action\PageAction' => ['admin'],
                'Page\Action\TemplateAction' => ['admin'],
                'Page\Action\CategoryAction' => ['admin'],
                'Page\Action\StatusAction' => ['admin'],
                'Auth\Action\RoleAction' => ['admin'],
                'Auth\Action\UserAction' => ['admin']
            ],
            'route' => [
                // 'admin.page' => ['admin']
            ],
        ],
    ],
];