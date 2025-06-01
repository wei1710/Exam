<?php

namespace Src\Models;

use Src\Models\BaseModel;
use Src\Models\Interfaces\ITrack;

class Track extends BaseModel implements ITrack
{
    public function getTableName(): string
    {
        return 'Track';
    }

    public function get(int $trackId): array|false
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
            INNER JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId
            INNER JOIN Genre ON Track.GenreId = Genre.GenreId
            WHERE
                Track.TrackId = :trackId
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':trackId', $trackId, \PDO::PARAM_INT);
            $stmt->execute();

            $track = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$track) {
                $this->logError("Track with ID {$trackId} not found.");
                return false;
            }

            return $track;
        } catch (\PDOException $e) {
            $this->logError("Error getting track with ID {$trackId}: ", $e->getMessage());
            return false;
        }
    }

    public function search(string $searchText): array|false
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
            INNER JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId
            INNER JOIN Genre ON Track.GenreId = Genre.GenreId
            WHERE
                Track.Name LIKE :nameSearch
            ORDER BY
                Track.Name
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $like = '%' . $searchText . '%';
            $stmt->bindParam(':nameSearch', $like, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logError("Error searching tracks: ", $e->getMessage());
            return false;
        }
    }

    public function getByComposer(string $composer): array|false
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
            INNER JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId
            INNER JOIN Genre ON Track.GenreId = Genre.GenreId
            WHERE
                Track.Composer LIKE :composer
            ORDER BY
                Track.Name
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $like = '%' . $composer . '%';
            $stmt->bindParam(':composer', $like, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logError("Error getting tracks by composer '{$composer}': ", $e->getMessage());
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
            $this->logError("Error getting tracks for Album ID {$albumId}: ", $e->getMessage());
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
            $this->logError("Error checking tracks for album {$albumId}: ", $e->getMessage());
            return false;
        }
    }

    public function create(array $data): array|false
    {
        $sql = <<<SQL
            INSERT INTO Track
            (Name, AlbumId, MediaTypeId, GenreId, Composer, Milliseconds, Bytes, UnitPrice)
            VALUES
            (:name, :albumId, :mediaTypeId, :genreId, :composer, :milliseconds, :bytes, :unitPrice)
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $data['name'], \PDO::PARAM_STR);
            $stmt->bindParam(':albumId', $data['album_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':mediaTypeId', $data['media_type_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':genreId', $data['genre_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':composer', $data['composer'], \PDO::PARAM_STR);
            $stmt->bindParam(':milliseconds', $data['milliseconds'], \PDO::PARAM_INT);
            $stmt->bindParam(':bytes', $data['bytes'], \PDO::PARAM_INT);
            $stmt->bindParam(':unitPrice', $data['unit_price']);

            $stmt->execute();
            $newId = $this->pdo->lastInsertId();

            return $this->get((int)$newId);
        } catch (\PDOException $e) {
            $this->logError("Error creating track: ", $e->getMessage());
            return false;
        }
    }

    public function update(int $trackId, array $data): array|false
    {
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'Name = :name';
            $params[':name'] = trim($data['name']);
        }
        if (isset($data['album_id'])) {
            $fields[] = 'AlbumId = :album_id';
            $params[':album_id'] = $data['album_id'];
        }
        if (isset($data['media_type_id'])) {
            $fields[] = 'MediaTypeId = :media_type_id';
            $params[':media_type_id'] = $data['media_type_id'];
        }
        if (isset($data['genre_id'])) {
            $fields[] = 'GenreId = :genre_id';
            $params[':genre_id'] = $data['genre_id'];
        }
        if (isset($data['composer'])) {
            $fields[] = 'Composer = :composer';
            $params[':composer'] = trim($data['composer']);
        }
        if (isset($data['milliseconds'])) {
            $fields[] = 'Milliseconds = :milliseconds';
            $params[':milliseconds'] = $data['milliseconds'];
        }
        if (isset($data['bytes'])) {
            $fields[] = 'Bytes = :bytes';
            $params[':bytes'] = $data['bytes'];
        }
        if (isset($data['unit_price'])) {
            $fields[] = 'UnitPrice = :unit_price';
            $params[':unit_price'] = $data['unit_price'];
        }

        if (empty($fields)) {
            return false;
        }

        $setClause = implode(', ', $fields);
        $sql = "UPDATE Track SET {$setClause} WHERE TrackId = :trackId";

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':trackId', $trackId, \PDO::PARAM_INT);
            $stmt->execute();

            return $this->get($trackId);
        } catch (\PDOException $e) {
            $this->logError("Error updating track {$trackId}: ", $e->getMessage());
            return false;
        }
    }

    public function delete(int $trackId): bool
    {
        $sql = "DELETE FROM Track WHERE TrackId = :trackId";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':trackId', $trackId, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->logError("Error deleting track {$trackId}: ", $e->getMessage());
            return false;
        }
    }
}

?>