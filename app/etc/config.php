<?php /** General configuration file */
return [
    'backend' => [
        'enabled' => 0,
        'front_name' => 'admin',
    ],
    'developer_mode' => 0, // Show all errors and exceptions on the frontend
    'support_email_address' => '',
    'default_locale' => 'en_US',
    'web' => [
        'homepage' => '/timer',
        'logo' => 'pub/media/images/logo.svg',
        'js' => [
            'minify' => 1,
            'symlink' => 1, // Use symbolic link in developer mode
        ],
        'css' => [
            'minify' => 1,
            'symlink' => 1, // Use symbolic link in developer mode
        ],
    ],
    'cache' => [
        'etc' => 1,
        'layout' => 1,
        'full_page' => 1,
        'translations' => 1,
    ],
    'timer' => [
        'show_hint' => 1,
        'general' => [
            'defaultTime' => 8,
            'sound' => 'pub/media/audio/alert_sound.mp3',
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
    ],
];
