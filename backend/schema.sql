-- Run this SQL in phpMyAdmin or via MySQL client to create required tables
CREATE DATABASE IF NOT EXISTS capstone_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE capstone_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE,
  name VARCHAR(255),
  is_admin TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  image VARCHAR(255) DEFAULT NULL,
  datetime DATETIME,
  location VARCHAR(255),
  capacity INT DEFAULT 0,
  author VARCHAR(255),
  anonymous TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shops (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  address VARCHAR(255),
  contact VARCHAR(255),
  owner_name VARCHAR(255),
  image VARCHAR(255) DEFAULT NULL,
  clicks INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  location VARCHAR(255),
  image VARCHAR(255) DEFAULT NULL,
  category_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS destination_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE destinations ADD CONSTRAINT FOREIGN KEY (category_id) REFERENCES destination_categories(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS product_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  shop_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  shop_id INT NOT NULL,
  category_id INT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2),
  stock INT DEFAULT 0,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(255),
  user_name VARCHAR(255),
  anonymous TINYINT DEFAULT 0,
  message TEXT,
  rating INT DEFAULT 5,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS itineraries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(255),
  user_name VARCHAR(255),
  anonymous TINYINT DEFAULT 0,
  title VARCHAR(255),
  days INT,
  destinations TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS attendance_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  location VARCHAR(255),
  date DATE,
  attendance INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Store page/activity logs for analytics
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(255),
  user_name VARCHAR(255),
  anonymous TINYINT DEFAULT 0,
  page VARCHAR(255),
  action VARCHAR(100),
  meta JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event alerts (DO/DO NOT, BRING/DO NOT BRING)
CREATE TABLE IF NOT EXISTS event_alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  alert_type VARCHAR(50),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Anonymous session tracking (visitor analytics without login)
CREATE TABLE IF NOT EXISTS anonymous_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) UNIQUE,
  ip_address VARCHAR(45),
  city VARCHAR(100),
  country VARCHAR(100),
  device_type VARCHAR(50),
  first_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  page_views INT DEFAULT 1,
  total_time_minutes INT DEFAULT 0
);

-- Raw event tables for anonymous tracking
CREATE TABLE IF NOT EXISTS page_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255),
  url VARCHAR(255),
  title VARCHAR(255),
  ts DATETIME,
  duration_s INT DEFAULT 0,
  INDEX(session_id), INDEX(ts)
);

CREATE TABLE IF NOT EXISTS click_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255),
  ts DATETIME,
  selector TEXT,
  url VARCHAR(255),
  INDEX(session_id), INDEX(ts)
);

CREATE TABLE IF NOT EXISTS search_queries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255),
  ts DATETIME,
  query_text VARCHAR(255),
  url VARCHAR(255),
  INDEX(session_id), INDEX(ts)
);

-- Track which places are most viewed/clicked
CREATE TABLE IF NOT EXISTS place_analytics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  place_name VARCHAR(255),
  view_count INT DEFAULT 0,
  click_count INT DEFAULT 0,
  avg_time_spent_seconds INT DEFAULT 0,
  last_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Track which shops are most clicked
CREATE TABLE IF NOT EXISTS shop_interactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  shop_id INT,
  shop_name VARCHAR(255),
  view_count INT DEFAULT 0,
  click_count INT DEFAULT 0,
  avg_time_spent_seconds INT DEFAULT 0,
  last_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Store AI predictions for events
CREATE TABLE IF NOT EXISTS event_predictions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  event_name VARCHAR(255),
  predicted_visitors INT,
  predicted_garbage_kg DECIMAL(10, 2),
  crowd_status VARCHAR(50),
  prediction_date DATE,
  phase VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========== MACHINE LEARNING PREDICTIONS TABLES ==========

-- ML Predictions table - stores all ML prediction results
CREATE TABLE IF NOT EXISTS ml_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
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
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_created_at (created_at),
    INDEX idx_overcrowded (overcrowded),
    INDEX idx_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ML Festival events table - stores festival metadata for predictions
CREATE TABLE IF NOT EXISTS ml_festival_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    event_name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    expected_attendance INT,
    venue_capacity INT,
    is_free INT DEFAULT 0,
    duration_hours FLOAT,
    status ENUM('scheduled', 'ongoing', 'completed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ML Festival predictions table - stores predictions per festival
CREATE TABLE IF NOT EXISTS ml_festival_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ml_festival_event_id INT NOT NULL,
    prediction_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    predicted_attendance INT,
    predicted_waste_kg FLOAT,
    overcrowding_risk INT,
    confidence_score FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ml_festival_event_id) REFERENCES ml_festival_events(id) ON DELETE CASCADE,
    INDEX idx_festival_id (ml_festival_event_id),
    INDEX idx_prediction_timestamp (prediction_timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ML Model metrics table - tracks model accuracy over time
CREATE TABLE IF NOT EXISTS ml_model_metrics (
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

-- ML API activity logs - track API usage for monitoring
CREATE TABLE IF NOT EXISTS ml_api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(100),
    method VARCHAR(10),
    status_code INT,
    response_time_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_endpoint (endpoint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration: add start/end coordinate columns to events table (for admin-set routes)
-- Run these statements if your `events` table does not already have the columns.
ALTER TABLE events
  ADD COLUMN IF NOT EXISTS start_lat DOUBLE NULL,
  ADD COLUMN IF NOT EXISTS start_lng DOUBLE NULL,
  ADD COLUMN IF NOT EXISTS end_lat DOUBLE NULL,
  ADD COLUMN IF NOT EXISTS end_lng DOUBLE NULL;

-- Admin-managed itinerary destinations (separate from hardcoded ones)
CREATE TABLE IF NOT EXISTS itinerary_destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  latitude DOUBLE NOT NULL,
  longitude DOUBLE NOT NULL,
  description TEXT,
  activities TEXT,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin-managed itinerary hotels (separate from hardcoded ones)
CREATE TABLE IF NOT EXISTS itinerary_hotels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  latitude DOUBLE NOT NULL,
  longitude DOUBLE NOT NULL,
  rating DECIMAL(3,1),
  rate_per_night INT,
  phone VARCHAR(50),
  address TEXT,
  description TEXT,
  features TEXT,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Admin-managed local experiences
CREATE TABLE IF NOT EXISTS local_experiences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  type VARCHAR(100),
  price DECIMAL(10, 2),
  duration VARCHAR(100),
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin-managed festivals and events
CREATE TABLE IF NOT EXISTS festivals_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  date_start DATE,
  date_end DATE,
  location VARCHAR(255),
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;