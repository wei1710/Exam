<?php

namespace Src\Models;

use Src\Models\BaseModel;
use Src\Models\Interfaces\IMediaType;

class MediaType extends BaseModel implements IMediaType
{
    public function getTableName(): string
    {
        return 'MediaType';
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
            $this->logError("Error getting all media types: ", $e->getMessage());
            return false;
        }
    }
}

?>