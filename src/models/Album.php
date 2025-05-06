<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

class Album extends DBConnection
{

  public function __construct()
  {
    parent::__construct();
  }

  public function getAll(): array|false
  {
    $sql = <<<SQL
        SELECT
            Album.AlbumId,
            Album.Title,
            Album.ArtistId,
            Artist.Name AS ArtistName
        FROM
            Album
        INNER JOIN
            Artist ON Album.ArtistId = Artist.ArtistId
        ORDER BY
            Album.Title
      SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();

      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      Logger::logText('Error getting all albums: ', $e);
      return false;
    }
  }

  public function get(int $albumId): array|false
  {
    $sql = <<<SQL
        SELECT
            Album.AlbumId,
            Album.Title,
            Album.ArtistId,
            Artist.Name AS ArtistName
        FROM
            Album
        INNER JOIN
            Artist ON Album.ArtistId = Artist.ArtistId
        WHERE
            Album.AlbumId = :albumId
      SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam('albumId', $albumId, \PDO::PARAM_INT);
      $stmt->execute();

      $album = $stmt->fetch(\PDO::FETCH_ASSOC);

      if (!$album) {
        Logger::logText("Album with ID {$albumId} not found.");
        return false;
      }
      return $album;
    } catch (\PDOException $e) {
      Logger::logText("Error getting album with ID {$albumId}: ", $e->getMessage());
      return false;
    }
  }

  public function search(string $title): array|false
  {
    $sql = <<<SQL
        SELECT
            Album.AlbumId,
            Album.Title,
            Album.ArtistId,
            Artist.Name AS ArtistName
        FROM
            Album
        INNER JOIN
            Artist ON Album.ArtistId = Artist.ArtistId
        WHERE
            Album.Title LIKE :title_search
        ORDER BY
            Album.Title
      SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $searchTerm = '%' . $title . '%';
      $stmt->bindParam(':title_search', $searchTerm, \PDO::PARAM_STR);
      $stmt->execute();

      $albums = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      return $albums;
    } catch (\PDOException $e) {
      Logger::logText("Error getting albums with Title {$title}: ", $e->getMessage());
      return false;
    }
  }

  public function create(string $title, int $artistId): array|false
  {
    $sql = <<<SQL
        INSERT INTO Album (Title, ArtistId)
        VALUES (:title, :artistId)
    SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':title', $title, \PDO::PARAM_STR);
      $stmt->bindParam(':artistId', $artistId, \PDO::PARAM_INT);
      $stmt->execute();

      $albumId = $this->pdo->lastInsertId();

      return [
        'AlbumId' => (int)$albumId,
        'Title' => $title,
        'ArtistId' => $artistId
      ];
    } catch (\PDOException $e) {
      Logger::logText("Error creating album: ", $e->getMessage());
      return false;
    }
  }

  public function update(int $albumId, ?string $title, ?int $artistId): array|false
  {
    $fields = [];
    $params = [':albumId' => $albumId];

    if ($title !== null && trim($title) !== '') {
      $fields[] = 'Title = :title';
      $params[':title'] = trim($title);
    }

    if ($artistId !== null && $artistId > 0) {
      $fields[] = 'ArtistId = :artistId';
      $params[':artistId'] = $artistId;
    }

    if (empty($fields)) {
      return false;
    }

    $sql = "UPDATE Album SET " . implode(', ', $fields) . " WHERE AlbumId = :albumId";

    try {
      $stmt = $this->pdo->prepare($sql);
      foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
      }
      $stmt->execute();

      return $this->get($albumId);
    } catch (\PDOException $e) {
      Logger::logText("Error updating album {$albumId}: ", $e->getMessage());
      return false;
    }
  }

  public function delete(int $albumId): bool
  {
    $sql = "DELETE FROM Album WHERE AlbumId = :albumId";

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':albumId', $albumId, \PDO::PARAM_INT);
      return $stmt->execute();
    } catch (\PDOException $e) {
      Logger::logText("Error deleting album {$albumId}: ", $e->getMessage());
      return false;
    }
  }

  public function getByArtistId(int $artistId): array|false
  {
    $sql = <<<SQL
            SELECT
                Album.AlbumId,
                Album.Title,
                Album.ArtistId,
                Artist.Name AS ArtistName
            FROM
                Album
            INNER JOIN
                Artist ON Album.ArtistId = Artist.ArtistId
            WHERE
                Album.ArtistId = :artistId
            ORDER BY
                Album.Title
        SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':artistId', $artistId, \PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      Logger::logText("Error getting albums for artist ID {$artistId}: ", $e->getMessage());
      return false;
    }
  }

  public function hasAlbums(int $artistId): bool
  {
    $sql = "SELECT COUNT(*) FROM Album WHERE ArtistId = :artistId";

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':artistId', $artistId, \PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchColumn() > 0;
    } catch (\PDOException $e) {
      Logger::logText("Error checking albums for artist {$artistId}: ", $e->getMessage());
      return false;
    }
  }
}
