<?php

namespace Src;

class DBCredentials
{
    protected string $host;
    protected string $dbname;
    protected string $user;
    protected string $password;

    public function __construct()
    {
        $configPath = __DIR__ . '/../config.php';

        if (!file_exists($configPath) || !is_readable($configPath)) {
            error_log("Database configuration file not found or not readable at: " . $configPath);
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error: Configuration issue']);
            exit;
        }

        $dbConfig = require $configPath;

        if (!isset($dbConfig['DB_HOST'], $dbConfig['DB_NAME'], $dbConfig['DB_USER'], $dbConfig['DB_PASS'])) {
            error_log("Database configuration is incomplete in config.php");
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error: Configuration issue']);
            exit;
        }

        $this->host = $dbConfig['DB_HOST'];
        $this->dbname = $dbConfig['DB_NAME'];
        $this->user = $dbConfig['DB_USER'];
        $this->password = $dbConfig['DB_PASS'];
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDbname(): string
    {
        return $this->dbname;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}

?>