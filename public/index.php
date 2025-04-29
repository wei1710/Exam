<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../src/DBConnection.php';
require_once __DIR__ . '/../src/Logging/Logger.php';
require_once __DIR__ . '/../src/models/Album.php';

use Src\models\Album;
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

$resource = array_shift($parts); // now we expect 'albums'

// Route handling
switch ($resource) {
    case 'albums':
        handleAlbum($method, $parts);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        break;
}

/**
 * Handle /albums endpoints
 */
function handleAlbum(string $method, array $parts): void
{
    $albumModel = new Album();

    if ($method === 'GET') {
        $searchQuery = $_GET['s'] ?? null;

        if ($searchQuery !== null) {
            // Search by title
            $album = $albumModel->search($searchQuery);

            if ($album === false) {
                http_response_code(404);
                echo json_encode(['error' => "Album with title '{$searchQuery}' not found."]);
            } else {
                http_response_code(200);
                echo json_encode($album);
            }

        } elseif (empty($parts)) {
            // Get all albums
            $albums = $albumModel->getAll();

            if ($albums === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to retrieve albums.']);
                Logger::logText('API Error: Failed to retrieve albums', new \Exception('Database error during getAll'));
            } elseif (empty($albums)) {
                http_response_code(200);
                echo json_encode([]);
            } else {
                http_response_code(200);
                echo json_encode($albums);
            }

        } elseif (count($parts) === 1 && is_numeric($parts[0])) {
            // Get album by ID
            $albumId = (int)$parts[0];
            $album = $albumModel->get($albumId);

            if ($album === false) {
                http_response_code(404);
                echo json_encode(['error' => "Album with ID {$albumId} not found."]);
            } else {
                http_response_code(200);
                echo json_encode($album);
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