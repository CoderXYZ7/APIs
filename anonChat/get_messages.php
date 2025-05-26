<?php
// php/get_messages.php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $accountId = $input['account_id'] ?? '';
    $secretKey = $input['secret_key'] ?? '';

    // Basic input validation
    if (empty($accountId) || empty($secretKey)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit();
    }

    $pdo = getDbConnection();

    try {
        // 1. Authenticate account and check if active
        $stmt = $pdo->prepare("SELECT id, expires_at FROM accounts WHERE account_id = ? AND secret_key = ?");
        $stmt->execute([$accountId, $secretKey]);
        $account = $stmt->fetch();

        if (!$account || strtotime($account['expires_at']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid account ID or secret key, or account expired.']);
            exit();
        }

        // 2. Retrieve unread messages for this account
        // We fetch messages where read_at is NULL or where the receiver is this account and mark them as read.
        $stmt = $pdo->prepare("SELECT id, sender_account_id, message_content, sent_at FROM messages WHERE receiver_account_id = ? AND read_at IS NULL ORDER BY sent_at ASC");
        $stmt->execute([$accountId]);
        $messages = $stmt->fetchAll();

        // 3. Mark retrieved messages as read
        if (!empty($messages)) {
            $messageIds = array_column($messages, 'id');
            $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
            $updateStmt = $pdo->prepare("UPDATE messages SET read_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)");
            $updateStmt->execute($messageIds);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Messages retrieved successfully.',
            'messages' => $messages
        ]);

    } catch (PDOException $e) {
        error_log("Error getting messages: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve messages.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
