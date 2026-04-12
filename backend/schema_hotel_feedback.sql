-- Hotels Table for Itinerary Management
CREATE TABLE IF NOT EXISTS hotels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  address VARCHAR(255),
  category VARCHAR(50),
  phone VARCHAR(20),
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  price_per_night DECIMAL(10, 2),
  features TEXT,
  image VARCHAR(255) DEFAULT NULL,
  average_rating DECIMAL(3, 2) DEFAULT 0,
  total_ratings INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Update feedback table to support better rating/feedback categorization
ALTER TABLE feedback ADD COLUMN IF NOT EXISTS image_url VARCHAR(255);
ALTER TABLE feedback ADD COLUMN IF NOT EXISTS reviewer_type VARCHAR(50) DEFAULT 'guest';
ALTER TABLE feedback ADD COLUMN IF NOT EXISTS helpful_count INT DEFAULT 0;
ALTER TABLE feedback ADD COLUMN IF NOT EXISTS unhelpful_count INT DEFAULT 0;

-- View for aggregated ratings by target type
CREATE OR REPLACE VIEW ratings_summary AS
SELECT 
  target_type,
  target_id,
  target_name,
  COUNT(*) as total_reviews,
  ROUND(AVG(rating), 2) as average_rating,
  MIN(rating) as min_rating,
  MAX(rating) as max_rating,
  SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
  SUM(CASE WHEN rating < 4 AND rating >= 3 THEN 1 ELSE 0 END) as neutral_reviews,
  SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_reviews,
  MAX(created_at) as latest_review
FROM feedback
WHERE target_type IS NOT NULL
GROUP BY target_type, target_id, target_name;
