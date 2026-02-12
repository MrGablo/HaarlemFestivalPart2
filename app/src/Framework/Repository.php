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
            //create connection string
            $connectionString = 'mysql:host=' . Config::DB_SERVER_NAME . ';dbname=' . Config::DB_NAME . ';charset=utf8mb4';
            //create new PDO connection
            self::$connection = new \PDO(
                $connectionString,
                Config::DB_USERNAME,
                Config::DB_PASSWORD
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