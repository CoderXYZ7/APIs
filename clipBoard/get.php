<?php
// api/get.php

// Set headers for CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

// Get ID from GET request
$id = $_GET['id'] ?? '';

// Validate and sanitize input ID
if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_-]{1,255}$/', $id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID format.']);
    exit();
}

// Fetch content from the database
$stmt = $pdo->prepare("SELECT content FROM clipboard_entries WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch();

if ($result) {
    echo json_encode(['status' => 'success', 'content' => $result['content']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Content not found for the given ID.']);
}
?>
