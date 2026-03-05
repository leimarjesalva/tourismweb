# Capstone Database Setup Guide - phpMyAdmin

Based on your project's schema files, here's what you need to put in your phpMyAdmin MySQL database:

---

## 1. Create the Database

Run this SQL in phpMyAdmin:

```
sql
CREATE DATABASE IF NOT EXISTS capstone_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE capstone_db;
```

---

## 2. Core Tables Required

Run `backend/schema.sql` to create all tables:

| Table | Purpose |
|-------|---------|
| **users** | User accounts (email, name, admin status) |
| **events** | Community events, festivals with dates, locations, capacity |
| **shops** | Local shops/stores with contact info, owners |
| **destinations** | Tourist spots in Legazpi (Mayon Volcano, beaches, monuments) |
| **destination_categories** | Categories for destinations (volcano, beach, museum, etc.) |
| **products** | Products sold by shops |
| **product_categories** | Product categories per shop |
| **feedback** | User feedback and ratings |
| **itineraries** | User-created travel itineraries |
| **event_alerts** | DO/DO NOT, BRING/DO NOT BRING alerts for events |
| **attendance_history** | Historical event attendance data |

---

## 3. Analytics & Tracking Tables

| Table | Purpose |
|-------|---------|
| **activity_logs** | Page/activity logs for analytics |
| **anonymous_sessions** | Visitor tracking without login |
| **page_views** | Anonymous page view tracking |
| **click_events** | User click events |
| **search_queries** | Search history |
| **place_analytics** | Most viewed/clicked places |
| **shop_interactions** | Shop view/click tracking |

---

## 4. Machine Learning Prediction Tables

| Table | Purpose |
|-------|---------|
| **ml_predictions** | ML waste & crowd predictions |
| **ml_festival_events** | Festival metadata for predictions |
| **ml_festival_predictions** | Prediction results per festival |
| **ml_model_metrics** | Model accuracy tracking |
| **ml_api_logs** | API usage monitoring |

---

## 5. Itinerary Management Tables

| Table | Purpose |
|-------|---------|
| **itinerary_destinations** | Admin-managed destinations |
| **itinerary_hotels** | Admin-managed hotels |
| **local_experiences** | Local tour experiences |
| **festivals_events** | Festival schedule management |

---

## 6. Quick Setup Steps

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create database named `capstone_db`
3. Click "Import" tab
4. Import `backend/schema.sql`
5. (Optional) Import `backend/schema_additions.sql`
6. (Optional) Import `backend/seed_events.sql` for sample data

---

## 7. Key Fields to Populate

### Sample User (Admin):
```
sql
INSERT INTO users (email, name, is_admin) VALUES 
('admin@capstone.com', 'Administrator', 1);
```

### Sample Destinations:
```
sql
INSERT INTO destination_categories (name, description) VALUES 
('Volcano', 'Volcanic attractions'),
('Beach', 'Beaches and coastal areas'),
('Museum', 'Historical and cultural museums'),
('Park', 'Parks and recreational areas');

INSERT INTO destinations (name, description, location, category_id) VALUES 
('Mayon Volcano', 'Famous symmetric volcano', 'Legazpi City, Albay', 1),
('Legazpi Boulevard', 'Coastal walkway', 'Legazpi City, Albay', 2),
('Cagsawa Ruins', 'Historical site', 'Daraga, Albay', 3);
```

### Sample Shops:
```
sql
INSERT INTO shops (name, description, address, owner_name) VALUES 
('Bicol Souvenirs', 'Local handicrafts', 'Legazpi City', 'John Doe'),
('Mayon Delights', 'Local food products', 'Legazpi City', 'Jane Smith');
```

---

## Important Notes

- Database name: `capstone_db`
- Default charset: `utf8mb4`
- Some tables require coordinates (lat/lng) for mapping features
- The ML tables are for predicting event overcrowding and waste generation
- The system uses MariaDB/MySQL (10.4.20-MariaDB)
