<?php /** General configuration file */
return [
    'support_email_address' => '',
    'web' => [
        'web_root_is_pub' => 1, // 0 for project root
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
            'sound' => 'audio/football_sound.mp3' // path example - audio/file.mp3, must be in 'media' folder
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
