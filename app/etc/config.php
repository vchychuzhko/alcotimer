<?php /** General configuration file */
return [
    'backend' => [
        'enabled' => 0,
        'front_name' => 'admin',
    ],
    'developer_mode' => 0, // Show all errors and exceptions on the frontend
    'show_forbidden' => 0, // NotFound response will be returned instead
    'support_email_address' => '',
    'web' => [
        'homepage' => 'timer/index/index',
        'logo' => 'pub/media/images/logo.png',
        'web_root_is_pub' => 1, // 0 for project root
        'js' => [
            'minify' => 1,
            'merge' => 0,
            'symlink' => 1, // Use symbolic link in developer mode
        ],
        'css' => [
            'minify' => 1,
            'merge' => 0,
            'symlink' => 1, // Use symbolic link in developer mode
        ],
    ],
    'cache' => [
        'etc' => 1,
        'layout' => 1,
        'full_page' => 1,
    ],
    'timer_config' => [
        'show_hint' => 1,
        'general' => [
            'defaultTime' => 8,
            'sound' => 'pub/media/audio/football_sound.mp3',
        ],
        'settings' => [
            'hideRandomTime' => 1,
            'maxTime' => 10,
            'minTime' => 2,
            'showLoader' => 1
        ],
        'slider_config' => [
            'difference' => 1,
            'maxValue' => 15,
            'minValue' => 1,
        ]
    ]
];
