<?php

namespace Src\Models\Interfaces;
Interface IArtist {
  public function getAll(): array|false;
  public function get(int $artistId): array|false;
  public function search(string $name): array|false;
  public function create(string $name): array|false;
  public function delete(int $artistId): bool;
}

?>