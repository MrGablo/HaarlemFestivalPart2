<?php
namespace App;

class Config 
{
    // Legacy constants for backward compatibility
    public const DB_SERVER_NAME = 'mysql';
    public const DB_USERNAME = 'root';
    public const DB_PASSWORD = 'secret123';
    public const DB_NAME = 'name';

    public static function getDbServerName(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_SERVER_NAME'])) {
                return $env['DB_SERVER_NAME'];
            }
        }
        return getenv('DB_SERVER_NAME') ?: ($_ENV['DB_SERVER_NAME'] ?? self::DB_SERVER_NAME);
    }

    public static function getDbUsername(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_USERNAME'])) {
                return $env['DB_USERNAME'];
            }
        }
        return getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? self::DB_USERNAME);
    }

    public static function getDbPassword(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_PASSWORD'])) {
                return $env['DB_PASSWORD'];
            }
        }
        return getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? self::DB_PASSWORD);
    }

    public static function getDbName(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_NAME'])) {
                return $env['DB_NAME'];
            }
        }
        return getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? self::DB_NAME);
    }

    public static function getDbPort(): int
    {
        $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? null);
        if ($port === null) {
            return self::isSqlServer() ? 1433 : 3306;
        }
        return (int)$port;
    }

    public static function isSqlServer(): bool
    {
        $server = self::getDbServerName();
        return strpos($server, 'database.windows.net') !== false;
    }

    public static function getDbConfig(): array
    {
        $env = getenv('DB_CONNECTION');
        if ($env && trim($env) !== '') {
            $pairs = array_filter(array_map('trim', explode(';', $env)));
            $map = [];
            foreach ($pairs as $p) {
                $parts = explode('=', $p, 2);
                if (count($parts) === 2) {
                    $map[trim($parts[0])] = trim($parts[1]);
                }
            }

            if (isset($map['Server']) || isset($map['Initial Catalog']) || isset($map['Database'])) {
                $server = $map['Server'] ?? '';
                $server = preg_replace('/^tcp:/i', '', $server);
                $database = $map['Initial Catalog'] ?? ($map['Database'] ?? '');
                $user = $map['User ID'] ?? ($map['UID'] ?? '');
                $pass = $map['Password'] ?? ($map['PWD'] ?? '');
                $dsn = 'sqlsrv:Server=' . $server . ';Database=' . $database;
                return ['dsn' => $dsn, 'user' => $user, 'pass' => $pass];
            }
        }

        // Fallback to individual environment variables or constants
        $serverName = self::getDbServerName();
        $dbName = self::getDbName();
        $username = self::getDbUsername();
        $password = self::getDbPassword();
        $port = self::getDbPort();

        if (self::isSqlServer()) {
            $dsn = "sqlsrv:Server={$serverName},{$port};Database={$dbName}";
        } else {
            $dsn = "mysql:host={$serverName};port={$port};dbname={$dbName};charset=utf8mb4";
        }

        return ['dsn' => $dsn, 'user' => $username, 'pass' => $password];
    }
}
