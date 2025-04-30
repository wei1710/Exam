<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

Class Track extends DBConnection
{
  public function __construct()
  {
    parent::__construct();
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

  public function hasTracks(int $albumId): bool
  {
      $sql = "SELECT COUNT(*) FROM Track WHERE AlbumId = :albumId";
      
      try {
          $stmt = $this->pdo->prepare($sql);
          $stmt->bindParam(':albumId', $albumId, \PDO::PARAM_INT);
          $stmt->execute();
          $count = $stmt->fetchColumn();
          return $count > 0;
      } catch (\PDOException $e) {
          Logger::logText("Error checking tracks for album {$albumId}: ", $e->getMessage());
          return false;
      }
  }
}