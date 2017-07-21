<?php

return [
    'dependencies' => [
        // 'invokables' => [
        //     Plugin\Mailer\MailerAction::class => Plugin\Mailer\MailerAction::class,
        // ],
        'factories' => [
            Plugin\Mailer\MailerAction::class => Plugin\Mailer\MailerActionFactory::class,
        ],
    ],

    'routes' => [
        [
            'name' => 'mail',
            'path' => '/mail/send',
            'middleware' => Plugin\Mailer\MailerAction::class,
            'allowed_methods' => ['POST'],
        ],
    ],
];