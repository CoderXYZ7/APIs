CREATE DATABASE IF NOT EXISTS remote_clipboard;

USE remote_clipboard;

CREATE TABLE IF NOT EXISTS clipboard_entries (
    id VARCHAR(255) PRIMARY KEY,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE USER 'remote_clipboard'@'localhost' IDENTIFIED BY 'clipPSW';
GRANT SELECT, INSERT, UPDATE, DELETE ON remote_clipboard.* TO 'remote_clipboard'@'localhost';
FLUSH PRIVILEGES;
