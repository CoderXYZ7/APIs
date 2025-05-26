<?php
// php/create_account.php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDbConnection();

    try {
        $accountId = generateUuid();
        $secretKey = generateSecureRandomString();
        // Account expires 24 hours from creation
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $pdo->prepare("INSERT INTO accounts (account_id, secret_key, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$accountId, $secretKey, $expiresAt]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Account created successfully.',
            'account_id' => $accountId,
            'secret_key' => $secretKey,
            'expires_at' => $expiresAt
        ]);

    } catch (PDOException $e) {
        error_log("Error creating account: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to create account.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
