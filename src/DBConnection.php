<?php

namespace Src;

use Src\Logging\Logger;

require_once 'DBCredentials.php';

class DBConnection extends DBCredentials
{
    protected ?\PDO $pdo = null;

    public function __construct()
    {
        parent::__construct();

        $host = $this->getHost();
        $dbname = $this->getDbname();
        $user = $this->getUser();
        $password = $this->getPassword();

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $this->pdo = new \PDO($dsn, $user, $password, $options);
            Logger::logText('Database connection successful');
        } catch (\PDOException $e) {
            Logger::logText('FATAL ERROR: Database connection failed: ', $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function getPdo(): ?\PDO
    {
        return $this->pdo;
    }
}

?>