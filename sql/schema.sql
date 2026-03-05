CREATE SCHEMA `messaging_service_db` ;

USE `messaging_service_db` ;

-- Create api_clients table
CREATE TABLE api_clients (
    id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create api_keys table
CREATE TABLE api_keys (
    id CHAR(36) NOT NULL,
    client_id CHAR(36) NOT NULL,
    api_key CHAR(64) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_api_key (api_key),
    KEY idx_client_id (client_id),
    CONSTRAINT fk_api_keys_client
        FOREIGN KEY (client_id) REFERENCES api_clients(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE outgoing_messages (
    id CHAR(36) NOT NULL,
    client_id CHAR(36) NOT NULL,
    api_key_id CHAR(36) NULL,
    channel ENUM('email','sms','whatsapp') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    metadata JSON NULL,
    provider VARCHAR(100) NOT NULL,
    status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
    provider_response TEXT NULL,
    error_message TEXT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_client_id (client_id),
    KEY idx_api_key_id (api_key_id),
    KEY idx_status (status),
    KEY idx_channel (channel),
    KEY idx_requested_at (requested_at),
    KEY idx_queue (status, requested_at),

    CONSTRAINT fk_outgoing_messages_client
        FOREIGN KEY (client_id)
        REFERENCES api_clients(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_outgoing_messages_api_key
        FOREIGN KEY (api_key_id)
        REFERENCES api_keys(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;