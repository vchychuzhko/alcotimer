<?php /** General configuration file */
$config = [
    'support_email_address' => '',
    'routes' => [
        '' => 'homepage.php'
    ],
    'timer_config' => [
        'difference' => 1,
        'min_value' => 1,
        'max_value' => 30,
        'default_min_value' => 5,
        'default_max_value' => 20,
    ],
    'system_routes' => [
        '403' => '403.php',
        '404' => '404.php',
        'maintenance' => 'maintenance.php'
    ]
];
