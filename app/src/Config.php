<?php

namespace App;

class Config
{
    // Fallback/default values (kept for compatibility)
    public const DB_SERVER_NAME = 'mysql';
    public const DB_USERNAME = 'root';          // sometimes YOURUSER@YOURSERVER
    public const DB_PASSWORD = 'secret123';
    public const DB_NAME = 'name';
    public const DEFAULT_USER_PROFILE_IMAGE_PATH = '/assets/img/profiles/default-user.png';

    /**
     * Returns an array with keys:
     * - dsn
     * - user
     * - pass
     * - ssl_ca (optional)
     */
    public static function getDbConfig(): array
    {
        /**
         * 1) Azure/MSSQL style connection string support (legacy)
         * Example:
         * DB_CONNECTION="Server=tcp:...;Initial Catalog=...;User ID=...;Password=...;"
         */
        $envConn = getenv('DB_CONNECTION');
        if ($envConn && trim($envConn) !== '') {
            $pairs = array_filter(array_map('trim', explode(';', $envConn)));
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

                return [
                    'dsn' => $dsn,
                    'user' => $user,
                    'pass' => $pass,
                ];
            }
        }

        /**
         * 2) Aiven MySQL (preferred if present)
         * Read from AIVEN_* first, then DB_* (from docker-compose env).
         */
        $host = getenv('AIVEN_HOST') ?: getenv('DB_HOST');
        $port = getenv('AIVEN_PORT') ?: getenv('DB_PORT');
        $db   = getenv('AIVEN_DB')   ?: getenv('DB_DATABASE');
        $user = getenv('AIVEN_USER') ?: getenv('DB_USERNAME');
        $pass = getenv('AIVEN_PASSWORD') ?: getenv('DB_PASSWORD');

        // Optional CA path mounted into container (for Aiven)
        $sslCa = getenv('DB_SSL_CA') ?: getenv('AIVEN_SSL_CA');

        // If Aiven vars exist, build MySQL DSN from them
        if ($host && $db) {
            $port = $port ?: '3306';
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $host,
                $port,
                $db
            );

            return [
                'dsn' => $dsn,
                'user' => $user ?: '',
                'pass' => $pass ?: '',
                'ssl_ca' => $sslCa ?: null,
            ];
        }

        /**
         * 3) Fallback to local MySQL defaults (old behavior)
         */
        $dsn = 'mysql:host=' . self::DB_SERVER_NAME . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';
        return [
            'dsn' => $dsn,
            'user' => self::DB_USERNAME,
            'pass' => self::DB_PASSWORD,
        ];
    }
}