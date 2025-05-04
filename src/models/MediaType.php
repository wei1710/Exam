<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

Class MediaType extends DBConnection
{
  public function __construct()
  {
    parent::__construct();
  }

  public function getAll(): array|false
  {
    $sql = <<<SQL
            SELECT MediaTypeId, Name
            FROM MediaType
            ORDER BY Name
        SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      Logger::logText("Error getting all media types: ", $e->getMessage());
      return false;
    }
  }
}