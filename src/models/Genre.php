<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

Class Genre extends DBConnection
{
  public function __construct()
  {
    parent::__construct();
  }

  public function getAll(): array|false
    {
        $sql = <<<SQL
            SELECT GenreId, Name
            FROM Genre
            ORDER BY Name
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            Logger::logText("Error retrieving genres: ", $e->getMessage());
            return false;
        }
    }
}