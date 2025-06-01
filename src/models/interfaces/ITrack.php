<?php

namespace Src\Models\Interfaces;

interface ITrack
{
    public function get(int $trackId): array|false;
    public function search(string $searchText): array|false;
    public function getByComposer(string $composer): array|false;
    public function getTracksByAlbumId(int $albumId): array|false;
    public function hasTracks(int $albumId): bool;
    public function create(array $data): array|false;
    public function update(int $trackId, array $data): array|false;
    public function delete(int $trackId): bool;
}

?>