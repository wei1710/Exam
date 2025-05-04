<?php

namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

class Playlist extends DBConnection
{
  public function __construct()
  {
    parent::__construct();
  }

  public function getAll(): array|false
  {
    $sql = <<<SQL
            SELECT PlaylistId, Name
            FROM Playlist
            ORDER BY Name
        SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      Logger::logText("Error retrieving playlists: ", $e->getMessage());
      return false;
    }
  }

  public function hasTrack(int $trackId): bool
  {
    $sql = "SELECT COUNT(*) FROM PlaylistTrack WHERE TrackId = :trackId";

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':trackId', $trackId, \PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchColumn() > 0;
    } catch (\PDOException $e) {
      Logger::logText("Error checking if track {$trackId} is in a playlist: ", $e->getMessage());
      return true;
    }
  }
}
