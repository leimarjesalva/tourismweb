# 🏛️ LEGAZPI EXPLORER - COMPREHENSIVE SYSTEM DESIGN

**Project Name:** Legazpi Explorer  
**Document Type:** Technical System Design  
**Version:** 1.0  
**Date:** 2025  
**Location:** Legazpi City, Albay, Philippines  

---

## TABLE OF CONTENTS

1. [Executive Summary](#1-executive-summary)
2. [System Architecture](#2-system-architecture)
3. [Technology Stack](#3-technology-stack)
4. [Component Design](#4-component-design)
5. [Database Design](#5-database-design)
6. [API Design](#6-api-design)
7. [Security Design](#7-security-design)
8. [Deployment Architecture](#8-deployment-architecture)
9. [Data Flow & Processing](#9-data-flow--processing)
10. [Module Specifications](#10-module-specifications)
11. [System Interfaces](#11-system-interfaces)
12. [Performance & Scalability](#12-performance--scalability)
13. [Appendices](#13-appendices)

---

## 1. EXECUTIVE SUMMARY

### 1.1 Purpose

The **Legazpi Explorer** is a comprehensive tourism management system designed to support Legazpi City in Albay, Philippines—a premier tourist destination known for the Mayon Volcano and rich cultural heritage. The system provides intelligent event management, visitor analytics, ML-powered crowd and waste predictions, interactive mapping, and itinerary planning.

### 1.2 Scope

| Component | Description |
|-----------|-------------|
| **Public Portal** | Tourism information, events, itineraries, feedback, interactive maps |
| **Admin Dashboard** | Event management, analytics, content moderation, ML predictions |
| **ML Server** | AI predictions for crowd overcapacity and waste estimation |
| **Database** | MySQL with comprehensive tourism data |

### 1.3 Target Users

- **Tourists/Visitors** - Seeking travel information and planning trips
- **Event Organizers** - Managing festivals and community events
- **Administrators** - Managing the platform and content
- **Local Businesses** - Shops, hotels, tourism providers

### 1.4 Key System Capabilities

| Feature | Status | Description |
|---------|--------|-------------|
| Event Management | ✅ Full CRUD | Create, edit, delete events with ML predictions |
| Crowd Prediction | ✅ AI-powered | Overcrowding forecasting using TensorFlow.js |
| Waste Estimation | ✅ ML-based | Garbage generation predictions |
| Interactive Maps | ✅ Leaflet + OSM | OpenStreetMap integration |
| Route Optimization | ✅ OSRM-based | Routing suggestions |
| Itinerary Builder | ✅ Multi-day | Trip planning with hotels |
| Tourism Directory | ✅ Complete | Destinations, experiences, shops |
| Analytics Dashboard | ✅ Visitor tracking | Behavior analytics |
| Feedback System | ✅ User reviews | Ratings and feedback |
| PDF Export | ✅ Downloadable | Generate travel itineraries |

---

## 2. SYSTEM ARCHITECTURE

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PRESENTATION LAYER                                        │
│                                                                                              │
│  ┌───────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                           WEB BROWSER (Client)                                         │  │
│  │                                                                                        │  │
│  │  ┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐              │  │
│  │  │   index.html      │  │ admin_dashboard   │  │    script.js      │              │  │
│  │  │  (Public Portal)  │  │     .html         │  │  (Main Logic)     │              │  │
│  │  │                   │  │  (Admin Panel)    │  │                   │              │  │
│  │  └────────────────────┘  └────────────────────┘  └────────────────────┘              │  │
│  │                                                                                        │  │
│  │  ┌──────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │                              EXTERNAL LIBRARIES                                  │  │  │
│  │  │  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌──────────┐ │  │  │
│  │  │  │ Leaflet.js │  │  Chart.js  │  │html2canvas │  │ jsPDF      │  │TensorFlow │ │  │  │
│  │  │  │   (Maps)   │  │  (Charts)  │  │ (PDF Gen)  │  │ (PDF Exp)  │  │   .js     │ │  │  │
│  │  │  └────────────┘  └────────────┘  └────────────┘  └────────────┘  └──────────┘ │  │  │
│  │  └──────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────────────────────────────────────┘  │
│                                              │                                                │
│                                              ▼                                                │
│                                    HTTP/REST API                                              │
│                                              │                                                │
└──────────────────────────────────────────────┼───────────────────────────────────────────────┘
                                               │
                    ┌───────────────────────────┼───────────────────────────┐
                    │                           │                           │
                    ▼                           ▼                           ▼
┌───────────────────────────────────┐ ┌───────────────────────────┐ ┌───────────────────────────┐
│      BACKEND (PHP 8.x)           │ │    ML SERVER (Node.js)   │ │    EXTERNAL SERVICES    │
│                                   │ │                           │ │                           │
│  ┌─────────────────────────────┐ │ │  ┌─────────────────────┐ │ │  ┌───────────────────┐ │
│  │      API LAYER              │ │ │  │   EXPRESS.JS        │ │ │  │ OpenStreetMap     │ │
│  │  ┌────────────┐ ┌────────┐ │ │ │  │   (Port 3000)       │ │ │  │   (Map Tiles)     │ │
│  │  │  api.php   │ │predic- │ │ │ │  │                     │ │ │  └───────────────────┘ │
│  │  │            │ │tions_  │ │ │ │  │ • /predict          │ │ │  ┌───────────────────┐ │
│  │  │            │ │api.php│ │ │ │  │ • /predict-batch    │ │ │  │     OSRM          │ │
│  │  └────────────┘ └────────┘ │ │ │  │ • /history          │ │ │  │   (Routing)       │ │
│  └───────────────────────────┘ │ │ │  │ • /stats            │ │ │  └───────────────────┘ │
│                                │ │ │  │ • /health           │ │ │  ┌───────────────────┐ │
│  ┌───────────────────────────┐ │ │ │  └─────────────────────┘ │ │  │   Nominatim       │ │
│  │   BUSINESS LOGIC          │ │ │ │                           │ │  │  (Geocoding)      │ │
│  │  ┌──────────┐ ┌────────┐ │ │ │ │  ┌─────────────────────┐  │ │  └───────────────────┘ │
│  │  │   db.php │ │admin_ │ │ │ │ │  │   TENSORFLOW.JS     │  │ │                           │
│  │  │          │ │login  │ │ │ │ │  │                     │  │ │                           │
│  │  └──────────┘ └────────┘ │ │ │ │  │ • Classification    │  │ │                           │
│  │                         │ │ │ │  │   Model (Crowd)    │  │ │                           │
│  │  ┌─────────────────────┐│ │ │ │  │ • Regression        │  │ │                           │
│  │  │  MLServerBridge    ││ │ │ │  │   Model (Waste)    │  │ │                           │
│  │  │  (PHP ↔ Node.js)   ││ │ │ │  └─────────────────────┘  │ │                           │
│  │  └─────────────────────┘│ │ │ │                           │ │                           │
│  │                         │ │ │ │  ┌─────────────────────┐  │ │                           │
│  │  ┌─────────────────────┐│ │ │ │  │    SAVED MODELS    │  │ │                           │
│  │  │   UPLOAD HANDLERS   ││ │ │ │  │  best-overcrowding │  │ │                           │
│  │  │ (image/file upload) ││ │ │ │  │  -model/           │  │ │                           │
│  │  └─────────────────────┘│ │ │ │  │  best-waste-model/ │  │ │                           │
│  └───────────────────────────┘ │ │ │  └─────────────────────┘  │ │                           │
│                              │ │ │                           │ │                           │
└──────────────────────────────┼─┴─┴───────────────────────────┴─┴───────────────────────────┘
                               │
                               ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    DATA LAYER (MySQL)                                        │
│                                                                                              │
│  ┌───────────────────────────────────────────────────────────────────────────────────────┐   │
│  │                            capstone_db                                                 │   │
│  │                                                                                        │   │
│  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐                   │   │
│  │  │   CORE TABLES   │  │   ML TABLES      │  │  ANALYTICS TABLES│                   │   │
│  │  │                  │  │                  │  │                  │                   │   │
│  │  │ • users          │  │ • ml_predictions │  │ • page_views     │                   │   │
│  │  │ • events         │  │ • ml_festival_    │  │ • click_events   │                   │   │
│  │  │ • destinations   │  │   events         │  │ • search_queries│                   │   │
│  │  │ • shops          │  │ • ml_festival_   │  │ • place_analytics│                   │   │
│  │  │ • products       │  │   predictions    │  │ • shop_interact-│                   │   │
│  │  │ • feedback       │  │ • ml_model_      │  │   ions           │                   │   │
│  │  │ • itineraries   │  │   metrics        │  │ • activity_logs │                   │   │
│  │  │                  │  │                  │  │                  │                   │   │
│  │  └──────────────────┘  └──────────────────┘  └──────────────────┘                   │   │
│  │                                                                                        │   │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────┐  │   │
│  │  │                       ITINERARY MANAGEMENT                                      │  │   │
│  │  │  ┌────────────────────────────┐  ┌────────────────────────────────┐             │  │   │
│  │  │  │ itinerary_destinations    │  │ itinerary_hotels               │             │  │   │
│  │  │  │ local_experiences         │  │ festivals_events               │             │  │   │
│  │  │  └────────────────────────────┘  └────────────────────────────────┘             │  │   │
│  │  └─────────────────────────────────────────────────────────────────────────────────┘  │   │
│  └───────────────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                              │
└──────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Layer Descriptions

| Layer | Responsibility | Key Components |
|-------|----------------|----------------|
| **Presentation** | UI rendering, user interaction | HTML, CSS, JavaScript, Leaflet, Chart.js |
| **API Gateway** | Request routing, authentication | PHP API endpoints |
| **Business Logic** | Core functionality, data processing | PHP classes, ML integration |
| **ML Service** | AI predictions | Node.js, TensorFlow.js |
| **Data** | Persistent storage | MySQL Database |

---

## 3. TECHNOLOGY STACK

### 3.1 Frontend Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| HTML5 | Latest | Markup language |
| CSS3 | Latest | Styling, animations |
| JavaScript | ES6+ | Client-side logic |
| Leaflet.js | 1.9.x | Interactive maps |
| Chart.js | 4.x | Data visualization |
| jsPDF | Latest | PDF generation |
| html2canvas | Latest | Screenshot capture |
| TensorFlow.js | 4.x | Browser-based ML |

### 3.2 Backend Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.x | Server-side logic |
| Node.js | 18+ | ML server runtime |
| Express.js | 4.x | ML API framework |
| MySQL | 8.x | Database |

### 3.3 ML Technologies

| Technology | Purpose |
|------------|---------|
| TensorFlow.js | ML model training and inference |
| Node.js | Server-side ML runtime |
| Custom Models | Overcrowding classification, waste regression |

### 3.4 External Services

| Service | Purpose | Authentication |
|---------|---------|----------------|
| OpenStreetMap | Map tiles | None (free) |
| OSRM | Routing engine | None (free) |
| Nominatim | Geocoding | User-Agent required |

---

## 4. COMPONENT DESIGN

### 4.1 Frontend Components

#### 4.1.1 Public Portal (index.html)

```
┌─────────────────────────────────────────────────────────────────┐
│                        PUBLIC PORTAL                            │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │
│  │  Hero  │ │ Events  │ │   Map   │ │ Itiner. │ │Feedback │  │
│  │ Section│ │ Section │ │ Section │ │ Builder │ │ Section │  │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘  │
│                                                                 │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐                │
│  │Destin.  │ │Exper.   │ │Festival │ │  Shop  │                │
│  │ Section │ │ Section │ │ Section │ │ Section│                │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘                │
└─────────────────────────────────────────────────────────────────┘
```

**Components:**
- Hero Section with parallax effect
- Navigation with smooth scrolling
- Event cards with ML predictions
- Interactive Leaflet map
- Itinerary builder form
- Feedback submission form

#### 4.1.2 Admin Dashboard (admin_dashboard.html)

```
┌─────────────────────────────────────────────────────────────────┐
│                     ADMIN DASHBOARD                             │
├──────────────┬──────────────────────────────────────────────────┤
│              │                                                   │
│   SIDEBAR    │              CONTENT AREA                        │
│              │                                                   │
│  • Dashboard │  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐     │
│  • Events    │  │Stats 1 │ │Stats 2 │ │Stats 3 │ │Stats 4 │     │
│  • Itinerary │  └────────┘ └────────┘ └────────┘ └────────┘     │
│  • Analytics │                                                   │
│  • Festival  │  ┌────────────────────────────────────────┐     │
│  • Settings  │  │         Data Table / Charts            │     │
│              │  │                                        │     │
│              │  └────────────────────────────────────────┘     │
│              │                                                   │
└──────────────┴──────────────────────────────────────────────────┘
```

### 4.2 Backend Components

#### 4.2.1 API Layer (api.php)

| Module | Responsibility |
|--------|----------------|
| Authentication | Admin login/session management |
| Events CRUD | Create, read, update, delete events |
| Predictions | ML model integration |
| Itineraries | Trip planning management |
| Directory | Destinations, shops, products |
| Analytics | Visitor tracking, statistics |
| Feedback | User reviews management |

#### 4.2.2 ML Server Bridge (MLServerBridge.php)

```php
class MLServerBridge {
    private $mlServerUrl;
    
    // Connect to ML Server
    public function callMlPrediction($eventData);
    
    // Get overcrowding prediction
    public function predictFestivalCrowding($data);
    
    // Fallback when ML server unavailable
    public function generateFallbackPrediction($data);
}
```

#### 4.2.3 Upload Handlers

| Handler | Purpose |
|---------|---------|
| upload_event_image.php | Event images |
| upload_destination_image.php | Destination images |
| upload_shop_image.php | Shop images |
| upload_product_image.php | Product images |
| upload_experience_image.php | Experience images |
| upload_feedback_image.php | Feedback attachments |

### 4.3 ML Server Components

#### 4.3.1 Express Server (server.js)

**Endpoints:**
```
POST /predict          - Single prediction
POST /predict-batch    - Batch predictions
GET  /history          - Prediction history
GET  /stats            - ML statistics
POST /optimize-route   - Route optimization
GET  /health           - Health check
```

#### 4.3.2 TensorFlow Models

| Model | Type | Architecture |
|-------|------|--------------|
| Overcrowding Model | Classification | Dense(64) → Dense(32) → Sigmoid |
| Waste Model | Regression | Dense(64) → Dense(32) → Linear |

---

## 5. DATABASE DESIGN

### 5.1 Entity-Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    ENTITY RELATIONSHIPS                                      │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                         ┌─────────────────────┐
                         │       users          │
                         ├─────────────────────┤
                         │ id (PK)             │
                         │ email               │
                         │ name                │
                         │ is_admin            │
                         │ created_at          │
                         └─────────┬───────────┘
                                   │
                                   │
                         ┌─────────┴───────────┐
                         │                    │
                         ▼                    ▼
              ┌──────────────────┐    ┌──────────────────┐
              │     events       │    │   itineraries    │
              ├──────────────────┤    ├──────────────────┤
              │ id (PK)          │    │ id (PK)          │
              │ title            │    │ user_email       │
              │ description      │    │ title            │
              │ datetime         │    │ days             │
              │ location         │    │ destinations     │
              │ capacity         │    └────────┬─────────┘
              │ author           │             │
              │ start_lat/lng    │             │
              │ end_lat/lng      │             │
              └────────┬─────────┘             │
                       │                       │
          ┌────────────┴────────────┐          │
          │                         │          │
          ▼                         ▼          ▼
┌──────────────────┐    ┌──────────────────┐  ┌──────────────────┐
│  event_alerts   │    │ ml_predictions   │  │event_predictions │
├──────────────────┤    ├──────────────────┤  ├──────────────────┤
│ id (PK)         │    │ id (PK)          │  │ id (PK)          │
│ event_id (FK)   │    │ event_id (FK)    │  │ event_id (FK)   │
│ alert_type       │    │ attendance       │  │ predicted_vis... │
│ content          │    │ overcrowded       │  │ crowd_status     │
└──────────────────┘    │ waste_prediction │  └──────────────────┘
                        └──────────────────┘

┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│  destinations    │    │ destination_     │    │ local_experiences│
├──────────────────┤    │ categories       │    ├──────────────────┤
│ id (PK)         │    ├──────────────────┤    │ id (PK)          │
│ name            │◄───│ id (PK)          │    │ title            │
│ description     │    │ name             │    │ description      │
│ location        │    └──────────────────┘    │ type             │
│ image           │                            │ price            │
│ category_id (FK)│                            │ duration         │
└──────────────────┘                            │ image            │
                                                 └──────────────────┘

┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│      shops       │    │ product_         │    │    products      │
├──────────────────┤    │ categories       │    ├──────────────────┤
│ id (PK)         │    ├──────────────────┤    │ id (PK)          │
│ name            │◄───│ id (PK)          │◄───│ shop_id (FK)     │
│ description     │    │ shop_id (FK)     │    │ category_id (FK) │
│ address         │    │ name             │    │ name             │
│ contact         │    │ description      │    │ description      │
│ owner_name      │    └──────────────────┘    │ price            │
│ image           │                             │ stock            │
└──────────────────┘                             │ image            │
                                                 └──────────────────┘

┌──────────────────┐    ┌──────────────────┐
│    feedback      │    │   festivals_     │
├──────────────────┤    │    events        │
│ id (PK)         │    ├──────────────────┤
│ user_email      │    │ id (PK)          │
│ user_name       │    │ name             │
│ message         │    │ description     │
│ rating          │    │ date_start       │
│ image           │    │ date_end         │
│ created_at      │    │ location         │
└──────────────────┘    │ image            │
                        └──────────────────┘
```

### 5.2 Database Tables

#### 5.2.1 Core Tables

```sql
-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE,
  name VARCHAR(255),
  is_admin TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  image VARCHAR(255),
  datetime DATETIME,
  location VARCHAR(255),
  capacity INT DEFAULT 0,
  author VARCHAR(255),
  anonymous TINYINT DEFAULT 0,
  start_lat DOUBLE,
  start_lng DOUBLE,
  end_lat DOUBLE,
  end_lng DOUBLE,
  prediction LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event Alerts table
CREATE TABLE event_alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  alert_type VARCHAR(50),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 5.2.2 ML Tables

```sql
-- ML Predictions
CREATE TABLE ml_predictions (
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
);

-- ML Festival Events
CREATE TABLE ml_festival_events (
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
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- ML Festival Predictions
CREATE TABLE ml_festival_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ml_festival_event_id INT NOT NULL,
    prediction_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    predicted_attendance INT,
    predicted_waste_kg FLOAT,
    overcrowding_risk INT,
    confidence_score FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ml_festival_event_id) REFERENCES ml_festival_events(id) ON DELETE CASCADE
);
```

#### 5.2.3 Analytics Tables

```sql
-- Anonymous Sessions
CREATE TABLE anonymous_sessions (
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

-- Page Views
CREATE TABLE page_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255),
  url VARCHAR(255),
  title VARCHAR(255),
  ts DATETIME,
  duration_s INT DEFAULT 0,
  INDEX(session_id), INDEX(ts)
);

-- Click Events
CREATE TABLE click_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255),
  ts DATETIME,
  selector TEXT,
  url VARCHAR(255),
  INDEX(session_id), INDEX(ts)
);

-- Place Analytics
CREATE TABLE place_analytics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  place_name VARCHAR(255),
  view_count INT DEFAULT 0,
  click_count INT DEFAULT 0,
  avg_time_spent_seconds INT DEFAULT 0,
  last_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shop Interactions
CREATE TABLE shop_interactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  shop_id INT,
  shop_name VARCHAR(255),
  view_count INT DEFAULT 0,
  click_count INT DEFAULT 0,
  avg_time_spent_seconds INT DEFAULT 0,
  last_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 5.2.4 Itinerary Tables

```sql
-- Itineraries
CREATE TABLE itineraries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(255),
  user_name VARCHAR(255),
  anonymous TINYINT DEFAULT 0,
  title VARCHAR(255),
  days INT,
  destinations TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Itinerary Destinations
CREATE TABLE itinerary_destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  latitude DOUBLE NOT NULL,
  longitude DOUBLE NOT NULL,
  description TEXT,
  activities TEXT,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Itinerary Hotels
CREATE TABLE itinerary_hotels (
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
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 6. API DESIGN

### 6.1 API Endpoints Overview

| Category | Method | Endpoint | Description |
|----------|--------|----------|-------------|
| **Auth** | POST | /api.php?action=login | Admin login |
| **Auth** | POST | /api.php?action=logout | Admin logout |
| **Auth** | GET | /api.php?action=check_session | Check session |
| **Events** | GET | /api.php?action=list_events | List all events |
| **Events** | POST | /api.php?action=create_event | Create event |
| **Events** | PUT | /api.php?action=edit_event | Update event |
| **Events** | DELETE | /api.php?action=delete_event | Delete event |
| **Events** | POST | /api.php?action=add_event_alert | Add alert |
| **Events** | GET | /api.php?action=get_event_alerts | Get alerts |
| **Predictions** | GET | /api.php?action=predict | Basic prediction |
| **Predictions** | GET | /api.php?action=get_event_predictions | Event predictions |
| **Predictions** | POST | /api.php?action=train_ml | Train ML models |
| **Predictions** | POST | /api.php?action=predict_with_gb | GBM prediction |
| **Itineraries** | POST | /api.php?action=save_itinerary | Save itinerary |
| **Itineraries** | GET | /api.php?action=list_itineraries | List itineraries |
| **Itineraries** | GET | /api.php?action=get_itinerary | Get single |
| **Itineraries** | PUT | /api.php?action=edit_itinerary | Update |
| **Itineraries** | DELETE | /api.php?action=delete_itinerary | Delete |
| **Destinations** | GET | /api.php?action=list_destinations | List destinations |
| **Destinations** | POST | /api.php?action=create_destination | Create |
| **Destinations** | PUT | /api.php?action=edit_destination | Update |
| **Destinations** | DELETE | /api.php?action=delete_destination | Delete |
| **Shops** | GET | /api.php?action=list_shops | List shops |
| **Shops** | POST | /api.php?action=create_shop | Create shop |
| **Shops** | PUT | /api.php?action=edit_shop | Update shop |
| **Shops** | DELETE | /api.php?action=delete_shop | Delete shop |
| **Products** | GET | /api.php?action=list_products | List products |
| **Products** | POST | /api.php?action=create_product | Create product |
| **Products** | PUT | /api.php?action=edit_product | Update product |
| **Products** | DELETE | /api.php?action=delete_product | Delete product |
| **Feedback** | POST | /api.php?action=add_feedback | Add feedback |
| **Feedback** | GET | /api.php?action=list_feedback | List feedback |
| **Feedback** | DELETE | /api.php?action=delete_feedback | Delete feedback |
| **Analytics** | POST | /api.php?action=track_anonymous | Track session |
| **Analytics** | POST | /api.php?action=log_event | Log event |
| **Analytics** | GET | /api.php?action=analytics_summary | Dashboard stats |
| **Routing** | GET | /api.php?action=suggest_optimal_route | Route suggestions |
| **Routing** | GET | /api.php?action=get_event_parade_details | Parade details |

### 6.2 ML Server API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /health | Health check |
| POST | /predict | Single prediction |
| POST | /predict-batch | Batch predictions |
| GET | /history | Prediction history |
| GET | /stats | ML statistics |
| POST | /optimize-route | Route optimization |

### 6.3 API Response Format

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Sample Event",
    "capacity": 5000
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Invalid input parameters"
}
```

**List Response:**
```json
{
  "events": [
    { "id": 1, "title": "Event 1" },
    { "id": 2, "title": "Event 2" }
  ]
}
```

---

## 7. SECURITY DESIGN

### 7.1 Authentication

| Mechanism | Implementation |
|-----------|----------------|
| Admin Login | Session-based authentication |
| Session Management | PHP $_SESSION with secure flags |
| Password Storage | Hash comparison |

### 7.2 Input Validation

| Area | Method |
|------|--------|
| SQL Injection | Prepared statements |
| XSS | Output encoding |
| File Uploads | Extension validation, path sanitization |
| Coordinates | Legazpi boundary polygon check |

### 7.3 Data Protection

| Aspect | Implementation |
|--------|----------------|
| Anonymous Analytics | IP anonymization, no personal data |
| Session Security | HttpOnly, SameSite attributes |
| File Access | Path validation, allowed extensions |

### 7.4 External API Security

| Service | Security |
|---------|----------|
| OpenStreetMap | No authentication (public) |
| OSRM | No authentication (public) |
| Nominatim | User-Agent required |

---

## 8. DEPLOYMENT ARCHITECTURE

### 8.1 Development Environment

```
┌─────────────────────────────────────────────────────────────────┐
│                    DEVELOPMENT SETUP                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│   ┌──────────────┐                                              │
│   │  Windows 11  │                                              │
│   │   (Host)     │                                              │
│   └──────┬───────┘                                              │
│          │                                                      │
│   ┌──────┼───────┐     ┌──────────────┐     ┌──────────────┐  │
│   │      ▼       │     │   XAMPP      │     │   Node.js    │  │
│   │  ┌───────┐   │     │  ┌────────┐  │     │  ┌────────┐  │  │
│   │  │Apache │   │     │  │Apache  │  │     │  │Express │  │  │
│   │  │ :80   │   │     │  │  :80   │  │     │  │ :3000  │  │  │
│   │  └───────┘   │     │  └────────┘  │     │  └────────┘  │  │
│   │               │     │              │     │              │  │
│   │  ┌───────┐   │     │  ┌────────┐  │     │  ┌────────┐  │  │
│   │  │  PHP  │   │     │  │  PHP   │  │     │  │   TF   │  │  │
│   │  │ 8.x   │   │     │  │ 8.x    │  │     │  │  .js   │  │  │
│   │  └───────┘   │     │  └────────┘  │     │  └────────┘  │  │
│   │               │     │              │     │              │  │
│   │  ┌───────┐   │     │  ┌────────┐  │     │              │  │
│   │  │ MySQL │   │     │  │ MySQL  │  │     │              │  │
│   │  │:3306  │   │     │  │ :3306  │  │     │              │  │
│   │  └───────┘   │     │  └────────┘  │     │              │  │
│   └───────────────┘     └──────────────┘     └──────────────┘  │
│                                                                 │
│   Browser Access: http://localhost                              │
│   ML Server:     http://localhost:3000                          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 8.2 Production Architecture (Recommended)

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRODUCTION SETUP                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                         ┌─────────────┐                         │
│                         │   Internet   │                         │
│                         └──────┬──────┘                         │
│                                │                                 │
│                         ┌──────▼──────┐                         │
│                         │    Nginx    │                         │
│                         │  Reverse    │                         │
│                         │   Proxy     │                         │
│                         │  :80, :443  │                         │
│                         └──────┬──────┘                         │
│                                │                                 │
│              ┌─────────────────┼─────────────────┐              │
│              │                 │                 │              │
│      ┌──────▼──────┐   ┌───────▼───────┐  ┌──────▼──────┐    │
│      │   Apache    │   │    Node.js    │  │   Static    │    │
│      │  + PHP-FPM  │   │  + PM2        │  │   Files     │    │
│      │   :8080     │   │    :3000      │  │   Cache     │    │
│      └──────┬──────┘   └───────┬───────┘  └─────────────┘    │
│             │                   │                               │
│             │            ┌──────▼──────┐                         │
│             │            │ TensorFlow  │                         │
│             │            │    .js      │                         │
│             │            │   Models    │                         │
│             │            └─────────────┘                         │
│             │                                                   │
│      ┌──────▼──────┐                                            │
│      │   MySQL 8   │                                            │
│      │  Database   │                                            │
│      │   :3306     │                                            │
│      └─────────────┘                                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 8.3 Port Configuration

| Service | Development | Production |
|---------|-------------|------------|
| Apache | 80 | 8080 |
| MySQL | 3306 | 3306 |
| Node.js (ML) | 3000 | 3000 |
| HTTPS | - | 443 |

---

## 9. DATA FLOW & PROCESSING

### 9.1 Event Creation Flow

```
Admin Input
    │
    ▼
┌─────────────────┐
│  Create Event   │
│  (form submit)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Validate Data  │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────────┐
│ Save   │ │ Generate   │
│ to DB  │ │ Prediction │
└───┬────┘ └─────┬──────┘
    │            │
    ▼            ▼
┌────────┐ ┌────────────┐
│ Return │ │ Store      │
│ JSON   │ │ Prediction │
└────────┘ └────────────┘
```

### 9.2 ML Prediction Flow

```
User/Admin Request
       │
       ▼
┌─────────────────┐
│  API Endpoint   │
│  /predict       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Detect Event   │
│  Type           │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Load ML Models │
│  (TensorFlow.js)│
└────────┬────────┘
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────────┐
│Classify│ │  Linear    │
│ Over-  │ │ Regression │
│crowding│ │   (Waste)  │
└───┬────┘ └─────┬──────┘
    │            │
    └─────┬──────┘
          │
          ▼
┌─────────────────┐
│  Generate       │
│  Predictions    │
└────────┬────────┘
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────────┐
│ Store  │ │ Return     │
│ in DB  │ │ JSON       │
└────────┘ └────────────┘
```

### 9.3 Route Optimization Flow

```
User Request
       │
       ▼
┌─────────────────┐
│  Get Event      │
│  Coordinates    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Call OSRM      │
│  Routing API    │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────────┐
│ Route  │ │ Score     │
│ Options│ │ by Crowd  │
└───┬────┘ └─────┬──────┘
    │            │
    └─────┬──────┘
          │
          ▼
┌─────────────────┐
│  Return Ranked  │
│  Routes         │
└─────────────────┘
```

---

## 10. MODULE SPECIFICATIONS

### 10.1 Event Management Module

**Purpose:** Admin module for creating, editing, and managing community events and festivals.

**Features:**
- CRUD operations for events
- Event image upload
- Capacity and location management
- Route coordinates (start/end points)
- Event alerts (DO/DO NOT, BRING/DO NOT BRING)
- Auto-generated ML predictions

**Data Flow:**
```
Admin Form → api.php (create_event) → Database (events)
                                      ↓
                              MLServerBridge → ML Server
                                      ↓
                              Database (ml_predictions)
                                      ↓
                              Return prediction to admin
```

### 10.2 ML Predictions Module

**Purpose:** Machine learning system for predicting event overcrowding and waste generation.

**Features:**
- Overcrowding classification (Binary)
- Waste regression (Linear)
- Scenario-based predictions (low/medium/high)
- Historical data training
- Fallback heuristics

**ML Model Architecture:**

```
Input: [attendance, capacity, weekend, is_free, duration, food_stalls, weather]
       (7 features)
         ▼
    [Dense Layer 1]
    - Units: 64
    - Activation: ReLU
         ▼
    [Dense Layer 2]
    - Units: 32
    - Activation: ReLU
         ▼
    ┌────┴────┐
    │         │
    ▼         ▼
┌───────┐ ┌───────┐
│Sigmoid│ │Linear │
│(Class)│ │(Waste)│
└───────┘ └───────┘
```

### 10.3 Interactive Mapping Module

**Purpose:** Leaflet-based mapping system for visualizing events and optimizing routes.

**Features:**
- Display all events on map
- Risk-level markers
- Route optimization using OSRM
- Multiple route options
- Legazpi City boundary validation
- Real-time route suggestions

**Key Functions:**
- `is_in_legazpi(lat, lng)` - Coordinate validation
- `predictOptimalRoutes()` - Generate route alternatives

### 10.4 Itinerary Builder Module

**Purpose:** User-facing module for creating personalized travel itineraries.

**Features:**
- Multi-day trip planning
- Destination selection
- Hotel recommendations
- PDF export functionality

### 10.5 Analytics Module

**Purpose:** Comprehensive analytics system for understanding user behavior.

**Features:**
- Anonymous session tracking
- Page view analytics
- Click event logging
- Place/shop interaction tracking
- Geographic distribution

### 10.6 Feedback Module

**Purpose:** User feedback collection and display.

**Features:**
- Star rating (1-5)
- Anonymous posting
- Image attachments
- Admin moderation

---

## 11. SYSTEM INTERFACES

### 11.1 User Interfaces

| Interface | File | Description |
|-----------|------|-------------|
| Public Portal | index.html | Main website |
| Admin Login | admin_login.html | Admin authentication |
| Admin Dashboard | admin_dashboard.html | Admin control panel |

### 11.2 External Interfaces

| Interface | Provider | Purpose |
|-----------|----------|---------|
| Map Tiles | OpenStreetMap | Base map rendering |
| Routing | OSRM | Route calculation |
| Geocoding | Nominatim | Address lookup |
| Boundary | OSM/Nominatim | Legazpi boundary |

---

## 12. PERFORMANCE & SCALABILITY

### 12.1 Performance Considerations

| Area | Implementation |
|------|----------------|
| Database Indexing | Indexed on foreign keys, dates |
| Query Optimization | Prepared statements |
| Image Caching | Frontend mirrors |
| ML Model Loading | Lazy loading |
| API Response | JSON compression |

### 12.2 Scalability Recommendations

| Component | Scaling Strategy |
|-----------|------------------|
| PHP Backend | Horizontal scaling with load balancer |
| ML Server | Separate dedicated server |
| Database | Read replicas for heavy queries |
| Static Files | CDN distribution |

---

## 13. SOFTWARE REQUIREMENTS

### 13.1 Development Environment

| Item | Application/Software | Recommended |
|------|---------------------|-------------|
| **Operating System** | Windows/macOS/Linux | Windows 11 or Ubuntu 22.04 LTS |
| **Web Server** | Apache (via XAMPP) | XAMPP 8.x or Apache 2.4+ |
| **PHP** | Server-side Runtime | PHP 8.1 or higher |
| **Database** | MySQL | MySQL 8.0 or MariaDB 10.6+ |
| **Node.js** | ML Server Runtime | Node.js 18.x LTS or higher |
| **Package Manager** | npm | npm 9.x or higher |
| **Web Browser** | Chrome/Firefox/Edge | Chrome 110+ or Firefox 110+ |

### 13.2 Production Environment

| Item | Application/Software | Recommended |
|------|---------------------|-------------|
| **Operating System** | Linux Server | Ubuntu 22.04 LTS or Debian 11 |
| **Web Server** | Nginx or Apache | Nginx 1.24+ or Apache 2.4+ |
| **PHP** | PHP-FPM | PHP 8.1+ with FPM |
| **Database** | MySQL | MySQL 8.0 (InnoDB) |
| **Node.js** | Runtime | Node.js 18.x LTS via PM2 |
| **Process Manager** | PM2 | PM2 5.x for Node.js |
| **SSL Certificate** | Let's Encrypt | Free auto-renewable |

### 13.3 Software Dependencies

#### 13.3.1 Frontend Dependencies

| Library | Version | Purpose |
|---------|---------|---------|
| Leaflet.js | 1.9.x | Interactive maps |
| Chart.js | 4.x | Data visualization |
| jsPDF | Latest | PDF generation |
| html2canvas | Latest | Screenshot capture |
| TensorFlow.js | 4.x | Browser ML inference |

#### 13.3.2 Backend Dependencies

| Component | Version | Purpose |
|-----------|---------|---------|
| PHP | 8.x | Server-side logic |
| MySQL | 8.x | Database |
| cURL (PHP) | Built-in | HTTP requests |

#### 13.3.3 ML Server Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| Node.js | 18+ | Runtime |
| Express.js | 4.x | Web framework |
| @tensorflow/tfjs | 4.x | ML framework |
| @tensorflow/tfjs-backend-cpu | 4.x | CPU backend |
| mysql2/promise | Latest | MySQL driver |
| cors | Latest | CORS support |

---

## 14. HARDWARE REQUIREMENTS

### 14.1 Development Environment (Minimum)

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **Processor** | Intel Core i5 / AMD Ryzen 5 | Intel Core i7 / AMD Ryzen 7 |
| **RAM** | 8 GB | 16 GB |
| **Storage** | 20 GB SSD | 50 GB SSD |
| **Display** | 1280x720 | 1920x1080 |
| **Network** | Internet connection | Broadband (10+ Mbps) |

### 14.2 Development Environment (Recommended)

| Component | Specification |
|-----------|---------------|
| **Processor** | Intel Core i7-12700K / AMD Ryzen 7 5800X |
| **RAM** | 32 GB DDR4 |
| **Storage** | 512 GB NVMe SSD |
| **Graphics** | Integrated Intel UHD 620+ / AMD Radeon |
| **Network** | Stable broadband (25+ Mbps) |

### 14.3 Production Environment (Minimum)

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **CPU** | 2 cores @ 2.0 GHz | 4 cores @ 2.5 GHz |
| **RAM** | 4 GB | 8 GB |
| **Storage** | 40 GB SSD | 100 GB SSD |
| **Bandwidth** | 1 TB monthly | Unlimited |
| **Backup** | Not required | Daily automated |

### 14.4 Production Environment (Recommended)

| Component | Specification |
|-----------|---------------|
| **CPU** | 4+ cores (Intel Xeon / AMD EPYC) |
| **RAM** | 16 GB ECC DDR4 |
| **Storage** | 256+ GB NVMe SSD + 1 TB HDD for backups |
| **Network** | 100+ Mbps dedicated bandwidth |
| **Backup** | Automated daily + offsite |
| **Redundancy** | RAID 1+0 for data protection |

### 14.5 ML Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **CPU** | 4 cores | 8+ cores |
| **RAM** | 8 GB | 16 GB |
| **Storage** | 10 GB | 50 GB SSD |
| **GPU** | Not required | NVIDIA GPU (optional for training) |

### 14.6 Network Requirements

| Requirement | Specification |
|-------------|---------------|
| **Internet Connection** | Stable broadband |
| **Upload Speed** | 5+ Mbps |
| **Download Speed** | 25+ Mbps |
| **Latency** | < 100ms to external APIs |
| **Firewall** | Ports: 80, 443, 3306 (restricted), 3000 (restricted) |

---

## 15. APPENDICES

### Appendix A: File Structure

```
capstone/
├── backend/
│   ├── api.php                    # Main API
│   ├── db.php                     # Database connection
│   ├── MLServerBridge.php         # ML integration
│   ├── admin_login.php            # Authentication
│   ├── admin_session.php          # Session management
│   ├── ml/
│   │   ├── server.js             # ML server
│   │   ├── train.js              # Training script
│   │   └── best-*-model/          # Trained models
│   ├── uploads/                   # File uploads
│   ├── data/                      # Configuration data
│   └── schema.sql                 # Database schema
├── frontend/
│   ├── index.html                 # Public portal
│   ├── admin_dashboard.html       # Admin panel
│   ├── script.js                  # Main JavaScript
│   ├── style.css                  # Styling
│   └── img/                       # Image assets
└── docs/
    ├── SYSTEM_DESIGN.md           # This document
    ├── ARCHITECTURE.md            # Architecture details
    ├── SEQUENCE_DIAGRAM.md        # Sequence diagrams
    └── DATABASE_SETUP_GUIDE.md   # Setup instructions
```

### Appendix B: Configuration

| Parameter | Default | Description |
|-----------|---------|-------------|
| DB_HOST | localhost | MySQL host |
| DB_USER | root | MySQL username |
| DB_PASS | (empty) | MySQL password |
| DB_NAME | capstone_db | Database name |
| ML_SERVER_URL | http://localhost:3000 | ML server address |
| SESSION_LIFETIME | 7200 | Session timeout (seconds) |

### Appendix C: Dependencies

**Frontend:**
- Leaflet.js 1.9.x
- Chart.js 4.x
- jsPDF latest
- html2canvas latest
- TensorFlow.js 4.x

**Backend:**
- PHP 8.x
- MySQL 8.x

**ML Server:**
- Node.js 18+
- Express.js 4.x
- TensorFlow.js 4.x

---

## DOCUMENT INFORMATION

| Item | Details |
|------|---------|
| Version | 1.0 |
| Last Updated | 2025 |
| Author | Capstone Development Team |
| Status | Approved |

---

*End of System Design Document*

