<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Logging/Logger.php';
require_once __DIR__ . '/../src/models/Album.php';
require_once __DIR__ . '/../src/models/Track.php';
require_once __DIR__ . '/../src/models/Artist.php';

use Src\models\Album;
use Src\models\Track;
use Src\models\Artist;
use Src\Logging\Logger;

$method = $_SERVER['REQUEST_METHOD'];

// Parse the URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // get /exam/albums or /exam/albums/5 or /exam/albums?s=...
$path = trim($path, '/'); // remove leading/trailing slashes
$parts = explode('/', $path);

// Remove the "exam" part if it exists
if (!empty($parts) && $parts[0] === 'exam') {
    array_shift($parts);
}

$resource = array_shift($parts);

switch ($resource) {
    case 'albums':
        handleAlbum($method, $parts);
        break;

    case 'artists':
        handleArtist($method, $parts);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        break;
}

function handleArtist(string $method, array $parts): void
{
    $artistModel = new Artist();
    $albumModel = new Album();

    // DELETE /artists/{id}
    if ($method === 'DELETE' && count($parts) === 1 && is_numeric($parts[0])) {
        $artistId = (int)$parts[0];

        $artist = $artistModel->get($artistId);
        if ($artist === false) {
            http_response_code(404);
            echo json_encode(['error' => "Artist with ID {$artistId} not found."]);
            return;
        }

        if ($albumModel->hasAlbums($artistId)) {
            http_response_code(400);
            echo json_encode(['error' => "Cannot delete artist {$artistId} because they have albums."]);
            return;
        }

        $success = $artistModel->delete($artistId);

        if ($success) {
            http_response_code(200);
            echo json_encode(['message' => "Artist {$artistId} deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Failed to delete artist {$artistId}."]);
        }
        return;
    }

    // POST /artists
    if ($method === 'POST' && empty($parts)) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name']) || trim($input['name']) === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Missing or empty artist name']);
            return;
        }

        $name = trim($input['name']);
        $artist = $artistModel->create($name);

        if ($artist === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create artist']);
        } else {
            http_response_code(201);
            echo json_encode($artist);
        }
        return;
    }

    // GET requests
    if ($method === 'GET') {
        $searchQuery = $_GET['s'] ?? null;

        // GET /artists?s=query
        if ($searchQuery !== null) {
            $artists = $artistModel->search($searchQuery);

            if ($artists === false || empty($artists)) {
                http_response_code(404);
                echo json_encode(['error' => "No artists found matching '{$searchQuery}'"]);
            } else {
                http_response_code(200);
                echo json_encode($artists);
            }

        // GET /artists/{id}/albums
        } elseif (count($parts) === 2 && is_numeric($parts[0]) && $parts[1] === 'albums') {
            $artistId = (int)$parts[0];

            if ($artistModel->get($artistId) === false) {
                http_response_code(404);
                echo json_encode(['error' => "Artist with ID {$artistId} not found."]);
                return;
            }

            $albums = $albumModel->getByArtistId($artistId);

            if ($albums === false || empty($albums)) {
                http_response_code(404);
                echo json_encode(['error' => "No albums found for artist ID {$artistId}."]);
            } else {
                http_response_code(200);
                echo json_encode($albums);
            }

        // GET /artists/{id}
        } elseif (count($parts) === 1 && is_numeric($parts[0])) {
            $artistId = (int)$parts[0];
            $artist = $artistModel->get($artistId);

            if ($artist === false) {
                http_response_code(404);
                echo json_encode(['error' => "Artist with ID {$artistId} not found."]);
            } else {
                http_response_code(200);
                echo json_encode($artist);
            }

        // GET /artists
        } elseif (empty($parts)) {
            $artists = $artistModel->getAll();

            if ($artists === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to retrieve artists']);
            } else {
                http_response_code(200);
                echo json_encode($artists);
            }

        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request path for artists']);
        }

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed for artists']);
    }
}

/**
 * Handle /albums endpoints
 */
function handleAlbum(string $method, array $parts): void
{
    $albumModel = new Album();
    $trackModel = new Track();

    // CREATE (POST /albums)
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['title'], $input['artist_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing title or artist_id']);
            return;
        }

        $title = trim($input['title']);
        $artistId = (int)$input['artist_id'];

        if ($title === '' || $artistId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid title or artist_id']);
            return;
        }

        $newAlbum = $albumModel->create($title, $artistId);

        if ($newAlbum === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create album']);
        } else {
            http_response_code(201);
            echo json_encode($newAlbum);
        }
        return;
    }

    // UPDATE (PUT /albums/{id})
    if ($method === 'PUT' && count($parts) === 1 && is_numeric($parts[0])) {
        $albumId = (int)$parts[0];
        $album = $albumModel->get($albumId);

        if ($album === false) {
            http_response_code(404);
            echo json_encode(['error' => "Album with ID {$albumId} not found."]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        $title = isset($input['title']) ? trim($input['title']) : null;
        $artistId = isset($input['artist_id']) ? (int)$input['artist_id'] : null;

        if (($title === null || $title === '') && ($artistId === null || $artistId <= 0)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid fields to update']);
            return;
        }

        $updatedAlbum = $albumModel->update($albumId, $title, $artistId);

        if ($updatedAlbum === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update album']);
        } else {
            http_response_code(200);
            echo json_encode($updatedAlbum);
        }
        return;
    }

    // DELETE (DELETE /albums/{id})
    if ($method === 'DELETE' && count($parts) === 1 && is_numeric($parts[0])) {
        $albumId = (int)$parts[0];

        $album = $albumModel->get($albumId);
        if ($album === false) {
            http_response_code(404);
            echo json_encode(['error' => "Album with ID {$albumId} not found."]);
            return;
        }

        if ($trackModel->hasTracks($albumId)) {
            http_response_code(400);
            echo json_encode(['error' => "Album ID {$albumId} cannot be deleted because it has tracks."]);
            return;
        }

        $success = $albumModel->delete($albumId);

        if ($success) {
            http_response_code(200);
            echo json_encode(['message' => "Album ID {$albumId} deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Failed to delete album ID {$albumId}."]);
        }
        return;
    }

    // READ (GET)
    if ($method === 'GET') {
        $searchQuery = $_GET['s'] ?? null;

        // GET /albums?s=...
        if ($searchQuery !== null) {
            $albums = $albumModel->search($searchQuery);

            if ($albums === false) {
                http_response_code(404);
                echo json_encode(['error' => "Album with title '{$searchQuery}' not found."]);
            } else {
                http_response_code(200);
                echo json_encode($albums);
            }

            // GET /albums
        } elseif (empty($parts)) {
            $albums = $albumModel->getAll();

            if ($albums === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to retrieve albums.']);
            } elseif (empty($albums)) {
                http_response_code(200);
                echo json_encode([]);
            } else {
                http_response_code(200);
                echo json_encode($albums);
            }

            // GET /albums/{id}
        } elseif (count($parts) === 1 && is_numeric($parts[0])) {
            $albumId = (int)$parts[0];
            $album = $albumModel->get($albumId);

            if ($album === false) {
                http_response_code(404);
                echo json_encode(['error' => "Album with ID {$albumId} not found."]);
            } else {
                http_response_code(200);
                echo json_encode($album);
            }

            // GET /albums/{id}/tracks
        } elseif (count($parts) === 2 && is_numeric($parts[0]) && $parts[1] === 'tracks') {
            $albumId = (int)$parts[0];

            $album = $albumModel->get($albumId);
            if ($album === false) {
                http_response_code(404);
                echo json_encode(['error' => "Album with ID {$albumId} not found."]);
                return;
            }

            $tracks = $trackModel->getTracksByAlbumId($albumId);

            if ($tracks === false || empty($tracks)) {
                http_response_code(404);
                echo json_encode(['error' => "No tracks found for album ID {$albumId}."]);
            } else {
                http_response_code(200);
                echo json_encode($tracks);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request path.']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
    }
}
