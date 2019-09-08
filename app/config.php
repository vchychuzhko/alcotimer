<?php /** General configuration file */
return [
    'support_email_address' => '',
    'web' => [
        'homepage' => 'timer',
        'merge' => [
            'js' => 0,
            'css' => 0
        ],
        'minify' => [
            'js' => 0,
            'css' => 0
        ]
    ],
    'timer_config' => [
        'difference' => 1,
        'default_min_value' => 5,
        'default_max_value' => 20,
        'default_time' => 9,
        'hide_random_time' => 1,
        'min_value' => 1,
        'max_value' => 30,
        'show_loader' => 1
    ],
    'system_routes' => [
        '403' => '403.php',
        '404' => '404.php'
    ]
];
