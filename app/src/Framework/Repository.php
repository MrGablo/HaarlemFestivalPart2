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
            // Build connection from environment or config
            $db = Config::getDbConfig();
            $connectionString = $db['dsn'];
            $username = $db['user'];
            $password = $db['pass'];

            // Create new PDO connection
            self::$connection = new \PDO(
                $connectionString,
                $username,
                $password
            );
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database Connection Failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }
}
