<?php
// php/db_config.php

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'anonymous_messaging');
define('DB_USER', 'anonChat'); // <<< IMPORTANT: Change this to your MariaDB username
define('DB_PASS', 'anonPsw'); // <<< IMPORTANT: Change this to your MariaDB password

// Set content type to JSON for all API responses
header('Content-Type: application/json');

// Function to get a PDO database connection
function getDbConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better security and performance
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the error (e.g., to a file) and return a generic error message
        error_log("Database connection error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit(); // Terminate script execution
    }
}

// Function to generate a UUID (for account_id)
function generateUuid() {
    // This is a simple UUID v4 generator. For production, consider a more robust library.
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Function to generate a secure random string (for secret_key)
function generateSecureRandomString($length = 64) {
    return bin2hex(random_bytes($length / 2)); // Generates a string of hex characters
}
?>
