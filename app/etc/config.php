<?php /** General configuration file */
return [
    'backend' => [
        'enabled' => 0,
        'front_name' => 'admin',
    ],
    'developer_mode' => 0, // Show all errors and exceptions on the frontend and disable minification
    'show_forbidden' => 0, // NotFound response will be returned instead
    'support_email_address' => '',
    'web' => [
        'homepage' => 'timer_index_index',
        'logo' => 'pub/media/images/logo.png',
        'web_root_is_pub' => 1, // 0 for project root
        'js' => [
            'minify' => 1, // Minification is disabled if developer mode is on
            'merge' => 0,
        ],
        'css' => [
            'minify' => 1, // Minification is disabled if developer mode is on
            'merge' => 0,
        ],
    ],
    'cache' => [
        'etc' => 1,
        'layout' => 1,
        'full_page' => 1,
    ],
    'timer_config' => [
        'timer' => [
            'default_time' => 8,
            'sound' => 'pub/media/audio/football_sound.mp3'
        ],
        'settings' => [
            'hide_random_time' => 1,
            'max_time' => 10,
            'min_time' => 2,
            'show_loader' => 1
        ],
        'random_range' => [
            'difference' => 1,
            'min_value' => 1,
            'max_value' => 15,
        ]
    ]
];
