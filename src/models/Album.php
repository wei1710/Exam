<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

Class Album extends DBConnection
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

        return $stmt->fetchAll();
      } catch (\PDOException $e) {
        Logger::logText('Error getting all albums: ', $e);
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

  public function getTracksByAlbumId(int $albumId): array|false
  {
    $sql = <<<SQL
        SELECT
            Track.TrackId,
            Track.Name,
            Track.AlbumId,
            Track.MediaTypeId,
            MediaType.Name AS MediaTypeName,
            Track.GenreId,
            Genre.Name AS GenreName,
            Track.Composer,
            Track.Milliseconds,
            Track.Bytes,
            Track.UnitPrice
        FROM
            Track
        INNER JOIN
            MediaType ON Track.MediaTypeId = MediaType.MediaTypeId
        INNER JOIN
            Genre ON Track.GenreId = Genre.GenreId
        WHERE
            Track.AlbumId = :albumId
        ORDER BY
            Track.Name
      SQL;

    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':albumId', $albumId, \PDO::PARAM_INT);
        $stmt->execute();

        $tracks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $tracks;

    } catch (\PDOException $e) {
        Logger::logText("Error getting tracks for Album ID {$albumId}: ", $e->getMessage());
        return false;
    }
  }
}