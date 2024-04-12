<?php

namespace LmcUserOtp;

use Laminas\Router\Http\Literal;

return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            'lmcuserotp' => __DIR__ . '/../view'
        ]
    ],
    'router' => [
        'routes' => [
            'lmcuser' => [
                'type' => Literal::class,
                'priority' => 1000,
                'options' => [
                    'route' => '/user',
                    'defaults' => [
                        'controller' => 'lmcuser',
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'otp' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/otp',
                            'defaults' => [
                                'controller' => 'lmcuserotp',
                                'action' => 'otp'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
