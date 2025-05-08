<?php

namespace Src;

use Src\Logging\Logger;

require_once 'DBCredentials.php';

Class DBConnection extends DBCredentials
{
  protected ?\PDO $pdo;

  public function __construct()
  {
    $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
    $options = [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
    ];
    try {
      $this->pdo = new \PDO($dsn, $this->user, $this->password, $options);
      Logger::logText('Database connection successful');
    } catch (\PDOException $e) {
      Logger::logText('FATAL ERROR: Database connection failed: ', $e->getMessage());
    }
  }

  public function __destruct()
  {
    $this->pdo = null;
  }
}