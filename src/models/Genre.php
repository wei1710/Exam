<?php

namespace Src\Models;

use Src\Models\BaseModel;
use Src\Models\Interfaces\IGenre;

class Genre extends BaseModel implements IGenre
{
    public function getTableName(): string
    {
        return 'Genre';
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
            $this->logError("Error retrieving genres: ", $e->getMessage());
            return false;
        }
    }
}

?>