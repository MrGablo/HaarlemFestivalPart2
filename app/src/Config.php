<?php
namespace App;

class Config {
    // Fallback/default values (kept for compatibility)
    public const DB_SERVER_NAME = 'mysql';
    public const DB_USERNAME = 'root';
    public const DB_PASSWORD = 'secret123';
    public const DB_NAME = 'name';
    public const DEFAULT_USER_PROFILE_IMAGE_PATH = '/assets/img/default-user.png';

    // Returns an array with keys: dsn, user, pass.
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

            // Handle MSSQL / Azure-style connection strings
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

        // Fallback to MySQL DSN
        $dsn = 'mysql:host=' . self::DB_SERVER_NAME . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';
        return ['dsn' => $dsn, 'user' => self::DB_USERNAME, 'pass' => self::DB_PASSWORD];
    }
}
