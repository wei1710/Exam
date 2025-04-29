<?php

namespace Src;

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
    
    $this->pdo = new \PDO($dsn, $this->user, $this->password, $options);
  }

  public function __destruct()
  {
    $this->pdo = null;
  }
}