<?php

namespace Src\Models;

use Src\Models\BaseModel;

class Playlist extends BaseModel
{
  public function getTableName(): string
  {
    return 'Playlist';
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
      $this->logError("Error retrieving playlists: ", $e->getMessage());
      return false;
    }
  }

  public function get(int $playlistId): array|false
  {
    $sql = <<<SQL
          SELECT
              Playlist.PlaylistId,
              Playlist.Name AS PlaylistName,
              Track.TrackId,
              Track.Name AS TrackName,
              Track.AlbumId,
              Track.MediaTypeId,
              MediaType.Name AS MediaTypeName,
              Track.GenreId,
              Genre.Name AS GenreName,
              Track.Composer,
              Track.Milliseconds,
              Track.Bytes,
              Track.UnitPrice
          FROM Playlist
          LEFT JOIN PlaylistTrack ON Playlist.PlaylistId = PlaylistTrack.PlaylistId
          LEFT JOIN Track ON PlaylistTrack.TrackId = Track.TrackId
          LEFT JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId
          LEFT JOIN Genre ON Track.GenreId = Genre.GenreId
          WHERE Playlist.PlaylistId = :playlistId
          ORDER BY Track.Name
      SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':playlistId', $playlistId, \PDO::PARAM_INT);
      $stmt->execute();
      $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      if (empty($rows)) {
        return false;
      }

      // Build result structure
      $playlist = [
        'PlaylistId' => $rows[0]['PlaylistId'],
        'Name' => $rows[0]['PlaylistName'],
        'Tracks' => []
      ];

      foreach ($rows as $row) {
        if ($row['TrackId'] !== null) {
          $playlist['Tracks'][] = [
            'TrackId' => $row['TrackId'],
            'Name' => $row['TrackName'],
            'AlbumId' => $row['AlbumId'],
            'MediaTypeId' => $row['MediaTypeId'],
            'MediaTypeName' => $row['MediaTypeName'],
            'GenreId' => $row['GenreId'],
            'GenreName' => $row['GenreName'],
            'Composer' => $row['Composer'],
            'Milliseconds' => $row['Milliseconds'],
            'Bytes' => $row['Bytes'],
            'UnitPrice' => $row['UnitPrice']
          ];
        }
      }

      return $playlist;
    } catch (\PDOException $e) {
      $this->logError("Error retrieving playlist with tracks: ", $e->getMessage());
      return false;
    }
  }

  public function search(string $name): array|false
  {
    $sql = <<<SQL
          SELECT PlaylistId, Name
          FROM Playlist
          WHERE Name LIKE :name
          ORDER BY Name
      SQL;

    try {
      $stmt = $this->pdo->prepare($sql);
      $like = '%' . $name . '%';
      $stmt->bindParam(':name', $like, \PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      $this->logError("Error searching playlists: ", $e->getMessage());
      return false;
    }
  }

  public function create(string $name): array|false
  {
      $sql = <<<SQL
          INSERT INTO Playlist (Name)
          VALUES (:name)
      SQL;

      try {
          $stmt = $this->pdo->prepare($sql);
          $trimmed = trim($name);
          $stmt->bindParam(':name', $trimmed, \PDO::PARAM_STR);
          $stmt->execute();

          $newId = $this->pdo->lastInsertId();

          return [
              'PlaylistId' => (int)$newId,
              'Name' => $trimmed
          ];
      } catch (\PDOException $e) {
          $this->logError("Error creating playlist: ", $e->getMessage());
          return false;
      }
  }

  public function addTrack(int $playlistId, int $trackId): bool
  {
      $sql = <<<SQL
          INSERT INTO PlaylistTrack (PlaylistId, TrackId)
          VALUES (:playlistId, :trackId)
      SQL;

      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':playlistId', $playlistId, \PDO::PARAM_INT);
          $stmt->bindParam(':trackId', $trackId, \PDO::PARAM_INT);
          return $stmt->execute();
      } catch (\PDOException $e) {
          $this->logError("Error adding track {$trackId} to playlist {$playlistId}: ", $e->getMessage());
          return false;
      }
  }

  public function removeTrack(int $playlistId, int $trackId): bool
  {
      $sql = <<<SQL
          DELETE FROM PlaylistTrack
          WHERE PlaylistId = :playlistId AND TrackId = :trackId
      SQL;

      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':playlistId', $playlistId, \PDO::PARAM_INT);
          $stmt->bindParam(':trackId', $trackId, \PDO::PARAM_INT);
          return $stmt->execute();
      } catch (\PDOException $e) {
          $this->logError("Error removing track {$trackId} from playlist {$playlistId}: ", $e->getMessage());
          return false;
      }
  }

  public function delete(int $playlistId): bool
  {
      $sql = "DELETE FROM Playlist WHERE PlaylistId = :playlistId";

      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':playlistId', $playlistId, \PDO::PARAM_INT);
          return $stmt->execute();
      } catch (\PDOException $e) {
          $this->logError("Error deleting playlist {$playlistId}: ", $e->getMessage());
          return false;
      }
  }

  public function hasPlaylistTracks(int $playlistId): bool
  {
      $sql = "SELECT COUNT(*) FROM PlaylistTrack WHERE PlaylistId = :playlistId";

      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':playlistId', $playlistId, \PDO::PARAM_INT);
          $stmt->execute();
          return $stmt->fetchColumn() > 0;
      } catch (\PDOException $e) {
          $this->logError("Error checking tracks in playlist {$playlistId}: ", $e->getMessage());
          return true;
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
      $this->logError("Error checking if track {$trackId} is in a playlist: ", $e->getMessage());
      return true;
    }
  }
}
