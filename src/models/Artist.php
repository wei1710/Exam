<?php

namespace Src\Models;

use Src\Models\BaseModel;

class Artist extends BaseModel
{
  public function getTableName(): string
  {
    return 'Artist';
  }

  public function getAll(): array|false
  {
    $sql = <<<SQL
            SELECT ArtistId, Name
            FROM Artist
            ORDER BY Name
        SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      $this->logError("Error getting all artists: ", $e->getMessage());
      return false;
    }
  }

  public function get(int $artistId): array|false
  {
    $sql = "SELECT ArtistId, Name FROM Artist WHERE ArtistId = :artistId";

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':artistId', $artistId, \PDO::PARAM_INT);
      $stmt->execute();

      $artist = $stmt->fetch(\PDO::FETCH_ASSOC);

      if (!$artist) {
        $this->logError("Artist with ID {$artistId} not found.");
        return false;
      }

      return $artist;
    } catch (\PDOException $e) {
      $this->logError("Error getting artist {$artistId}: ", $e->getMessage());
      return false;
    }
  }

  public function search(string $name): array|false
  {
    $sql = <<<SQL
          SELECT ArtistId, Name
          FROM Artist
          WHERE Name LIKE :name
          ORDER BY Name
      SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $searchTerm = '%' . $name . '%';
      $stmt->bindParam(':name', $searchTerm, \PDO::PARAM_STR);
      $stmt->execute();

      $artists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $artists;
    } catch (\PDOException $e) {
      $this->logError("Error searching for artists by name '{$name}': ", $e->getMessage());
      return false;
    }
  }

  public function create(string $name): array|false
  {
      $sql = "INSERT INTO Artist (Name) VALUES (:name)";

      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
          $stmt->execute();

          $artistId = $this->pdo->lastInsertId();

          return [
              'ArtistId' => (int)$artistId,
              'Name' => $name
          ];
      } catch (\PDOException $e) {
          $this->logError("Error creating artist: ", $e->getMessage());
          return false;
      }
  }

  public function delete(int $artistId): bool
  {
      $sql = "DELETE FROM Artist WHERE ArtistId = :artistId";

      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':artistId', $artistId, \PDO::PARAM_INT);
          return $stmt->execute();
      } catch (\PDOException $e) {
          $this->logError("Error deleting artist {$artistId}: ", $e->getMessage());
          return false;
      }
  }
}
