<?php
// php/send_message.php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $senderAccountId = $input['sender_account_id'] ?? '';
    $senderSecretKey = $input['sender_secret_key'] ?? '';
    $receiverAccountId = $input['receiver_account_id'] ?? '';
    $messageContent = $input['message_content'] ?? '';

    // Basic input validation
    if (empty($senderAccountId) || empty($senderSecretKey) || empty($receiverAccountId) || empty($messageContent)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit();
    }

    $pdo = getDbConnection();

    try {
        // 1. Authenticate sender and check if account is active
        $stmt = $pdo->prepare("SELECT id, expires_at FROM accounts WHERE account_id = ? AND secret_key = ?");
        $stmt->execute([$senderAccountId, $senderSecretKey]);
        $senderAccount = $stmt->fetch();

        if (!$senderAccount || strtotime($senderAccount['expires_at']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid sender account ID or secret key, or account expired.']);
            exit();
        }

        // 2. Check if receiver account exists and is active
        $stmt = $pdo->prepare("SELECT id, expires_at FROM accounts WHERE account_id = ?");
        $stmt->execute([$receiverAccountId]);
        $receiverAccount = $stmt->fetch();

        if (!$receiverAccount || strtotime($receiverAccount['expires_at']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Receiver account not found or expired.']);
            exit();
        }

        // 3. Insert message
        $stmt = $pdo->prepare("INSERT INTO messages (sender_account_id, receiver_account_id, message_content) VALUES (?, ?, ?)");
        $stmt->execute([$senderAccountId, $receiverAccountId, $messageContent]);

        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);

    } catch (PDOException $e) {
        error_log("Error sending message: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
