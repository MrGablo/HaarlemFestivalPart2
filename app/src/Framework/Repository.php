<?php

namespace App\Framework;

use App\Config;
use PDO;

class Repository
{
    private static ?PDO $connection = null;

    protected function getConnection(): PDO
    {
        if (self::$connection === null) {
            $this->connect();
        }
        return self::$connection;
    }

    private function connect(): void
    {
        try {
            $db = Config::getDbConfig();

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            // If CA cert is provided, force SSL validation using Aiven CA
            if (!empty($db['ssl_ca'])) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $db['ssl_ca'];
                // Optional: verify server cert (usually fine with CA)
                // $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            }

            self::$connection = new PDO(
                $db['dsn'],
                $db['user'],
                $db['pass'],
                $options
            );
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
}
