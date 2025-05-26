-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS anonymous_messaging;

-- Use the newly created database
USE anonymous_messaging;

-- Table for anonymous accounts
CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id VARCHAR(36) UNIQUE NOT NULL, -- A unique identifier for the account (e.g., a UUID)
    secret_key VARCHAR(64) UNIQUE NOT NULL, -- A secret key used for authentication and message retrieval
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL -- Timestamp when the account will expire (e.g., 24 hours from creation)
);

-- Table for messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_account_id VARCHAR(36) NOT NULL,
    receiver_account_id VARCHAR(36) NOT NULL,
    message_content TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL, -- Timestamp when the message was read, null if unread
    FOREIGN KEY (sender_account_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
);


CREATE USER 'anonChat'@'localhost' IDENTIFIED BY 'anonPsw';
GRANT ALL PRIVILEGES ON anonymous_messaging.* TO 'anonChat'@'localhost';
FLUSH PRIVILEGES;