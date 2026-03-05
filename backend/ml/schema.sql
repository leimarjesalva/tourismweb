-- Machine Learning Database Schema for Festival Analytics
-- Create database and tables for ML predictions

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS ibalong_ai;

USE ibalong_ai;

-- Predictions table - stores all prediction results
CREATE TABLE IF NOT EXISTS predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance FLOAT NOT NULL,
    venue_capacity FLOAT NOT NULL,
    weekend INT NOT NULL,
    is_free INT NOT NULL,
    duration_hours FLOAT NOT NULL,
    food_stalls FLOAT NOT NULL,
    weather INT NOT NULL,
    overcrowded INT NOT NULL,
    waste_prediction FLOAT NOT NULL,
    overcrowding_probability FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_overcrowded (overcrowded)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Festival events table - stores festival metadata
CREATE TABLE IF NOT EXISTS festival_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    expected_attendance INT,
    venue_capacity INT,
    is_free INT DEFAULT 0,
    duration_hours FLOAT,
    status ENUM('scheduled', 'ongoing', 'completed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Festival predictions table - stores predictions per festival
CREATE TABLE IF NOT EXISTS festival_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    festival_event_id INT NOT NULL,
    prediction_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    predicted_attendance INT,
    predicted_waste_kg FLOAT,
    overcrowding_risk INT,
    confidence_score FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (festival_event_id) REFERENCES festival_events(id) ON DELETE CASCADE,
    INDEX idx_festival_id (festival_event_id),
    INDEX idx_prediction_timestamp (prediction_timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model performance metrics table - tracks model accuracy over time
CREATE TABLE IF NOT EXISTS model_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_type VARCHAR(50) NOT NULL,
    model_version VARCHAR(50),
    accuracy FLOAT,
    `precision` FLOAT,
    `recall` FLOAT,
    f1_score FLOAT,
    total_predictions INT,
    training_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_model_type (model_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API activity logs - track API usage for monitoring
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(100),
    method VARCHAR(10),
    status_code INT,
    response_time_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_endpoint (endpoint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create sample data for initial testing
INSERT INTO festival_events (event_name, event_date, expected_attendance, venue_capacity, is_free, duration_hours, status)
VALUES 
    ('Ibalong Festival 2026', '2026-11-26', 35000, 50000, 1, 8, 'scheduled'),
    ('Magayon Festival 2026', '2026-05-01', 25000, 40000, 0, 6, 'scheduled'),
    ('Cultural Event March', '2026-03-15', 15000, 25000, 0, 4, 'scheduled');

-- Grant privileges for application user
-- Uncomment and modify as needed:
-- CREATE USER 'ml_app'@'localhost' IDENTIFIED BY 'secure_password';
-- GRANT ALL PRIVILEGES ON ibalong_ai.* TO 'ml_app'@'localhost';
-- FLUSH PRIVILEGES;
