-- Additional tables for Destinations, Local Experiences, and Festivals & Events

-- Destinations table - managed by admin
CREATE TABLE IF NOT EXISTS destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  location VARCHAR(255),
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Local Experiences table - managed by admin
CREATE TABLE IF NOT EXISTS local_experiences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  type VARCHAR(100),
  price DECIMAL(10, 2),
  duration VARCHAR(100),
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Festivals & Events table - managed by admin
CREATE TABLE IF NOT EXISTS festivals_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  date_start DATETIME,
  date_end DATETIME,
  location VARCHAR(255),
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add metadata column to feedback table for admin feedback support
ALTER TABLE feedback ADD COLUMN IF NOT EXISTS metadata JSON DEFAULT NULL;
