<?php

return [
    'namespaces' => [
        'site' => 'resources/views/site',
        'user' => [
            'default' => 'resources/views/user',
            'dynamic' => [
                'type' => 'function',
                'function' => function () {
                    return 'user';
                },
                'prepend' => true,
                'base' => 'resources/views'
            ]
        ],
        'admin' => [
            'default' => 'resources/views/admin',
            'dynamic' => [
                'type' => 'sql',
                'request' => 'SELECT `value` AS path FROM `options` WHERE `key` = \'admin_theme\'',
                'field' => 'path',
                'base' => 'resources/views'
            ]
        ]
    ]
];
