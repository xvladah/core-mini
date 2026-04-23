<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

return [
    'services' => [
        'serviceA' => [
            'listen_host' => '127.0.0.1',
            'listen_port' => 9000,
            'sleep_time'  => 1.0,
            'log_file'    => __DIR__ . '/logs/serviceA.log',
            'db' => [
                'dsn'      => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
                'user'     => 'dbuser',
                'password' => 'dbpass',
                'options'  => [
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            ]
        ],
        'serviceB' => [
            'listen_host' => '127.0.0.1',
            'listen_port' => 9001,
            'sleep_time'  => 2.0,
            'log_file'    => __DIR__ . '/logs/serviceB.log',
            'db' => [
                'dsn'      => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
                'user'     => 'dbuser',
                'password' => 'dbpass',
                'options'  => [
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            ]
        ],
        // ... další služby
    ],

    'watchdog' => [
        'max_memory_mb' => 128,
        'max_uptime_sec' => 6 * 3600,
        'max_cycles' => 100000,
    ]
];