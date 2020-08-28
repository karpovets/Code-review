<?php

return [
    'controllers' => [
        'value' => [
            'namespaces' => [
                // Ключ - неймспейс для ajax-классов,
                // api - приставка экшенов, о ней мы поговорим чуть позже
                '\\Crmgenesis\\ResponseCompany\\Controller' => 'api',
            ],
        ],
        'readonly' => true,
    ],
];
