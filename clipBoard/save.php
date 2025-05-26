<?php
// api/save.php

// Set headers for CORS (Cross-Origin Resource Sharing)
// This allows your HTML page (from a different origin if applicable) to make requests to this API.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Handle preflight OPTIONS request (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
// IMPORTANT: Replace with your actual database credentials
$host = 'localhost';
$db   = 'remote_clipboard';
$user = 'root'; // Change this to a less privileged user in production
$pass = '';     // Change this to your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Establish database connection
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Check if id and content are provided
if (!isset($input['id']) || !isset($input['content'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID and content are required.']);
    exit();
}

$id = $input['id'];
$content = $input['content'];

// Validate and sanitize input ID
// Basic sanitization for ID to prevent directory traversal or other attacks
if (!preg_match('/^[a-zA-Z0-9_-]{1,255}$/', $id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID format. Use alphanumeric characters, hyphens, and underscores.']);
    exit();
}

// Check if the ID already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clipboard_entries WHERE id = ?");
$stmt->execute([$id]);
$exists = $stmt->fetchColumn();

if ($exists) {
    // If ID exists, update the content
    $stmt = $pdo->prepare("UPDATE clipboard_entries SET content = ? WHERE id = ?");
    $result = $stmt->execute([$content, $id]);
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Content updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update content.']);
    }
} else {
    // If ID does not exist, insert new content
    $stmt = $pdo->prepare("INSERT INTO clipboard_entries (id, content) VALUES (?, ?)");
    $result = $stmt->execute([$id, $content]);
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Content saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save content.']);
    }
}
?>
