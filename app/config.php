<?php /** General configuration file */
return [
    'app_mode' => 'production',
    'support_email_address' => '',
    'routes' => [
        '' => 'homepage.php'
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
