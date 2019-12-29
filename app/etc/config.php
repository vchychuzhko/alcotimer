<?php /** General configuration file */
return [
    'support_email_address' => '',
    'web' => [
        'pub_path' => '', // '' or 'pub/' for root
        'homepage' => 'timer',
        'show_forbidden' => 0,
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
            'default_time' => 8,
            'sound' => 'media/audio/football_sound.mp3' // path example - media/audio/file.mp3
        ],
        'settings' => [
            'default_min_value' => 2,
            'default_max_value' => 10,
            'hide_random_time' => 1,
            'show_loader' => 1
        ],
        'random_range' => [
            'difference' => 1,
            'min_value' => 1,
            'max_value' => 15,
        ]
    ]
];
