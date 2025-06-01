<?php

namespace Src\Models\Interfaces;

interface IAlbum
{
    public function getAll(): array|false;
    public function get(int $albumId): array|false;
    public function search(string $title): array|false;
    public function getByArtistId(int $artistId): array|false;
    public function hasAlbums(int $artistId): bool;
    public function create(string $title, int $artistId): array|false;
    public function update(int $albumId, ?string $title, ?int $artistId): array|false;
    public function delete(int $albumId): bool;
}

?>