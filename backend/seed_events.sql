-- Create sample events for testing
USE ibalong_ai;

-- Insert sample events (if table is empty)
INSERT INTO events (title, location, capacity, datetime, image) VALUES 
('Legazpi City Festival 2026', 'Legazpi City, Albay', 10000, '2026-03-15 14:00:00', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30'),
('Summer Community Gathering', 'Legazpi City Downtown', 5000, '2026-04-20 10:00:00', 'https://images.unsplash.com/photo-1519671482677-504be0ffbc87'),
('Albay Grand Fiesta', 'Legazpi City Sports Complex', 15000, '2026-05-01 06:00:00', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87');

-- Insert ML predictions for events
INSERT INTO ml_predictions (event_id, attendance, waste_prediction, overcrowding_probability) 
SELECT id, capacity * 0.7, capacity * 0.6, 0.35 FROM events WHERE location LIKE '%Legazpi%' LIMIT 3
ON DUPLICATE KEY UPDATE attendance=VALUES(attendance);

SELECT 'Events created successfully!' as status;
SELECT COUNT(*) as total_events FROM events;
