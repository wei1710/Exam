<?php

namespace Src\Models\Interfaces;
Interface IPlaylist {
  public function getAll(): array|false;
  public function get(int $playlistId): array|false;
  public function search(string $name): array|false;
  public function hasPlaylistTracks(int $playlistId): bool;
  public function hasTrack(int $trackId): bool;
  public function create(string $name): array|false;
  public function addTrack(int $playlistId, int $trackId): bool;
  public function removeTrack(int $playlistId, int $trackId): bool;
  public function delete(int $playlistId): bool;
}

?>