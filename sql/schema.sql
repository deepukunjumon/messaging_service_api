CREATE SCHEMA `messaging_microservice_db` ;

CREATE TABLE api_clients (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status TINYINT NOT NULL DEFAULT 1, -- -1 = deleted, 0 = inactive, 1 = active
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE api_keys (
    id CHAR(36) PRIMARY KEY,
    client_id CHAR(36) NOT NULL,
    api_key CHAR(64) NOT NULL UNIQUE,
    status TINYINT NOT NULL DEFAULT 1,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES api_clients(id)
);

CREATE TABLE outgoing_messages (
    id CHAR(36) PRIMARY KEY,

    client_id CHAR(36) NOT NULL,
    api_key_id CHAR(36) NULL,

    channel ENUM('email', 'sms', 'whatsapp') NOT NULL,

    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NULL,
    body TEXT NOT NULL,

    provider VARCHAR(100) NOT NULL,
    provider_message_id VARCHAR(255) NULL,

    status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',

    error_message TEXT NULL,
    attempts INT NOT NULL DEFAULT 0,

    metadata JSON NULL,

    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_client_id (client_id),
    INDEX idx_status (status),
    INDEX idx_channel (channel),
    INDEX idx_requested_at (requested_at)
);