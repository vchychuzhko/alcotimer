<?php /** General configuration file */
return [
    'support_email_address' => '',
    'web' => [
        'pub_path' => '', // '' or 'pub/' for root
        'homepage' => 'timer',
        'js' => [
            'minify' => 0,
            'merge' => 0
        ],
        'css' => [
            'minify' => 0,
            'merge' => 0
        ]
    ],
    'cache' => [
        'etc' => 1,
        'layout' => 1,
        'full-page' => 1
    ],
    'timer_config' => [
        'timer' => [
            'default_time' => 9
        ],
        'settings' => [
            'default_min_value' => 5,
            'default_max_value' => 20,
            'hide_random_time' => 1,
            'show_loader' => 1
        ],
        'random_range' => [
            'difference' => 1,
            'min_value' => 1,
            'max_value' => 30,
        ]
    ]
];
