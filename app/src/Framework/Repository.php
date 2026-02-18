<?php


namespace App\Framework;

use App\Config;
use PDO;

class Repository
{
    private static ?PDO $connection = null;
    protected function getConnection(): PDO
    {
        if(self::$connection === null){
            $this->connect();
        }
        return self::$connection;
    }
    private function connect(): void
    {
        try {
            $serverName = Config::getDbServerName();
            $dbName = Config::getDbName();
            $username = Config::getDbUsername();
            $password = Config::getDbPassword();
            $port = Config::getDbPort();

            if (Config::isSqlServer()) {
                $connectionString = "sqlsrv:Server={$serverName},{$port};Database={$dbName}";
            } else {
                $connectionString = "mysql:host={$serverName};port={$port};dbname={$dbName};charset=utf8mb4";
            }

            self::$connection = new \PDO($connectionString, $username, $password);
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database Connection Failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }
    
}