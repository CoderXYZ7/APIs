<?php
// php/delete_account.php
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
        // Authenticate and delete the account
        // ON DELETE CASCADE in the SQL schema will automatically delete associated messages
        $stmt = $pdo->prepare("DELETE FROM accounts WHERE account_id = ? AND secret_key = ?");
        $stmt->execute([$accountId, $secretKey]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Account and all associated messages deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid account ID or secret key, or account not found.']);
        }

    } catch (PDOException $e) {
        error_log("Error deleting account: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete account.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
