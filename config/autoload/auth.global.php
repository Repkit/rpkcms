<?php

/*first is checked middleware and if passed by this auth route came into action*/
// https://github.com/zendframework/zend-expressive/issues/249
return [
    'auth' => [
        'authorize' => [
            'middleware' => [
                'Page\Action\PageAction' => ['admin']
            ],
            'route' => [
                // 'admin.page' => ['admin']
            ],
        ],
    ],
];