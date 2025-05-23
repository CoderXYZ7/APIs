<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// File to store the last IP address (outside web root)
$ip_file = '../data/last_ip.txt';

// Function to get the real IP address
function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // IP from shared internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // IP passed from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        // IP from remote address
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Get current visitor's IP
$current_ip = getRealIpAddr();
$current_timestamp = date('Y-m-d H:i:s');

// Create data directory if it doesn't exist
$data_dir = dirname($ip_file);
if (!is_dir($data_dir)) {
    if (!mkdir($data_dir, 0755, true)) {
        $response['error'] = 'Could not create data directory';
        echo json_encode($response);
        exit();
    }
}

// Read the last IP from file (if exists)
$last_ip_data = null;
if (file_exists($ip_file)) {
    $file_content = file_get_contents($ip_file);
    if ($file_content) {
        $last_ip_data = json_decode($file_content, true);
    }
}

// Prepare response
$response = [
    'success' => true,
    'current_visitor' => [
        'ip' => $current_ip,
        'timestamp' => $current_timestamp
    ]
];

// Add last visitor info if available
if ($last_ip_data) {
    $response['last_visitor'] = [
        'ip' => $last_ip_data['ip'],
        'timestamp' => $last_ip_data['timestamp']
    ];
} else {
    $response['last_visitor'] = null;
    $response['message'] = 'You are the first visitor!';
}

// Save current visitor as the "last" visitor for next request
$current_data = [
    'ip' => $current_ip,
    'timestamp' => $current_timestamp
];

if (file_put_contents($ip_file, json_encode($current_data)) === false) {
    $response['warning'] = 'Could not save IP data. Make sure the directory is writable.';
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>