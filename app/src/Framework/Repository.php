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
        try
        {
            // Build connection from environment or config
            $db = Config::getDbConfig();
            $connectionString = $db['dsn'];
            $username = $db['user'];
            $password = $db['pass'];

            // create new PDO connection
            self::$connection = new \PDO(
                $connectionString,
                $username,
                $password
            );

            //tell PDO to throw erro
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        catch(\PDOException $e){
            //Handle connection error
            error_log($e->getMessage());
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
    
}