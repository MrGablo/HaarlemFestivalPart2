<?php

return
[
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds'      => 'db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'production_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'sqlsrv',
            'host' => getenv('DB_SERVER_NAME') ?: 'mysql',
            'name' => getenv('DB_NAME') ?: 'developmentdb',
            'user' => getenv('DB_USERNAME') ?: 'root',
            'pass' => getenv('DB_PASSWORD') ?: 'secret123',
            'port' => getenv('DB_PORT') ?: '1433',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'developmentdb',
            'user' => 'root',
            'pass' => 'secret123',
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];