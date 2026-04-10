<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

App\Utils\Env::load(__DIR__);

$host = getenv('AIVEN_HOST') ?: getenv('DB_HOST');
$port = getenv('AIVEN_PORT') ?: getenv('DB_PORT');
$name = getenv('AIVEN_DB') ?: getenv('DB_DATABASE');
$user = getenv('AIVEN_USER') ?: getenv('DB_USERNAME');
$pass = getenv('AIVEN_PASSWORD') ?: getenv('DB_PASSWORD');

$baseConfig = [
    'adapter' => 'mysql',
    'host' => $host,
    'name' => $name,
    'user' => $user,
    'pass' => $pass,
    'port' => $port,
    'charset' => 'utf8mb4',
];

return [
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => $baseConfig,
        'development' => $baseConfig,
        'testing' => $baseConfig,
    ],
    'version_order' => 'creation',
];