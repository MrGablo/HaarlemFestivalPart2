<?php
namespace App;

/**
 * Application configuration class.
 * 
 * Reads database configuration from environment variables if available,
 * otherwise falls back to default values for local development.
 * 
 * @package App
 */
class Config 
{
    /**
     * Get the database server name.
     * 
     * Reads from environment variable DB_SERVER_NAME or uses default.
     * 
     * @return string The database server hostname
     */
    public static function getDbServerName(): string
    {
        // 优先读取 .env 文件（如果使用 dotenv）
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_SERVER_NAME'])) {
                return $env['DB_SERVER_NAME'];
            }
        }
        return getenv('DB_SERVER_NAME') ?: ($_ENV['DB_SERVER_NAME'] ?? 'mysql');
    }

    /**
     * Get the database username.
     * 
     * Reads from environment variable DB_USERNAME or uses default.
     * 
     * @return string The database username
     */
    public static function getDbUsername(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_USERNAME'])) {
                return $env['DB_USERNAME'];
            }
        }
        return getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root');
    }

    /**
     * Get the database password.
     * 
     * Reads from environment variable DB_PASSWORD or uses default.
     * 
     * @return string The database password
     */
    public static function getDbPassword(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_PASSWORD'])) {
                return $env['DB_PASSWORD'];
            }
        }
        return getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? 'secret123');
    }

    /**
     * Get the database name.
     * 
     * Reads from environment variable DB_NAME or uses default.
     * 
     * @return string The database name
     */
    public static function getDbName(): string
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            if (isset($env['DB_NAME'])) {
                return $env['DB_NAME'];
            }
        }
        return getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'name');
    }

    /**
     * Get the database port.
     * 
     * Reads from environment variable DB_PORT or uses default.
     * Azure SQL Server uses port 1433, MySQL uses 3306.
     * 
     * @return int The database port
     */
    public static function getDbPort(): int
    {
        $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? null);
        if ($port === null) {
            // Auto-detect port based on server type
            return self::isSqlServer() ? 1433 : 3306;
        }
        return (int)$port;
    }

    /**
     * Check if using Azure SQL Server (SQL Server) or MySQL.
     * 
     * Azure SQL Server uses SQL Server protocol, not MySQL.
     * 
     * @return bool True if using SQL Server, false if MySQL
     */
    public static function isSqlServer(): bool
    {
        $server = self::getDbServerName();
        return strpos($server, 'database.windows.net') !== false;
    }

    // Legacy constants for backward compatibility (deprecated)
    /** @deprecated Use getDbServerName() instead */
    public const DB_SERVER_NAME = 'mysql';
    /** @deprecated Use getDbUsername() instead */
    public const DB_USERNAME = 'root';
    /** @deprecated Use getDbPassword() instead */
    public const DB_PASSWORD = 'secret123';
    /** @deprecated Use getDbName() instead */
    public const DB_NAME = 'name';
}
