<?php

namespace Src;

use Src\Logging\Logger;

// Ensure DBCredentials.php is included correctly.
// Based on your structure (both in src/ directory), this relative path should be correct.
require_once 'DBCredentials.php';

class DBConnection extends DBCredentials
{
    // Declare pdo property, allowing it to be null
    protected ?\PDO $pdo = null; // Initialize to null explicitly

    public function __construct()
    {
        parent::__construct();
        // Retrieve credentials from the parent DBCredentials class
        // These properties are populated by the logic in DBCredentials.php
        $host = $this->getHost();
        $dbname = $this->getDbname();
        $user = $this->getUser();
        $password = $this->getPassword();

        // Construct the DSN string
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        // Set PDO options for error handling and fetching
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, // Fetch rows as associative arrays
            \PDO::ATTR_EMULATE_PREPARES => false // Use native prepared statements for better security and performance
        ];

        try {
            // Attempt to establish the database connection
            $this->pdo = new \PDO($dsn, $user, $password, $options);
            // Log successful connection (assuming Logger is correctly set up)
            Logger::logText('Database connection successful');
        } catch (\PDOException $e) {
            // Log connection failure with the error message
            Logger::logText('FATAL ERROR: Database connection failed: ', $e->getMessage());
            // In a production API, you might want to return a generic error response here
            // and avoid exposing the specific database error message to the client.
            // For now, we'll let the script continue or exit based on your overall flow.
        }
    }

    // Destructor to close the database connection when the object is destroyed
    public function __destruct()
    {
        $this->pdo = null; // Setting PDO instance to null closes the connection
    }

    // Method to get the PDO instance
    public function getPdo(): ?\PDO
    {
        return $this->pdo;
    }
}

?>