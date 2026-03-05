# 🏛️ LEGAZPI EXPLORER - CONCEPTUAL DESIGN MODEL

## 1. SYSTEM OVERVIEW

**Project Name:** Legazpi Explorer  
**Project Type:** Full-Stack Web Application with ML Integration  
**Core Functionality:** A comprehensive tourism management system for Legazpi City, Albay, Philippines, featuring event management, itinerary planning, ML-powered crowd/waste predictions, and interactive mapping.  
**Target Users:**
- Tourists/Visitors seeking travel information
- Event organizers managing festivals
- Administrators managing the platform
- Local businesses (shops, hotels)

---

## 2. SYSTEM ARCHITECTURE

### 2.1 Layered Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│  (index.html, admin_dashboard.html, style.css, script.js)   │
└────────────────────────────┬────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│                    BUSINESS LOGIC LAYER                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │  PHP API Layer  │  │ ML Server (Node)│  │   Frontend   │ │
│  │  (api.php)      │  │ (TensorFlow.js) │  │  JavaScript  │ │
│  └────────┬────────┘  └────────┬─────────┘  └──────┬───────┘ │
│           │                    │                    │         │
└───────────┼────────────────────┼────────────────────┼─────────┘
            │                    │                    │
┌───────────▼────────────────────▼────────────────────▼─────────┐
│                      DATA ACCESS LAYER                       │
│         (db.php - MySQL Database Connection)                 │
└────────────────────────────┬────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│                       DATA LAYER                             │
│              (MySQL - capstone_db Database)                  │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Technology Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| Frontend | HTML5, CSS3, JavaScript | User interface, interactive maps |
| Maps | Leaflet.js + OpenStreetMap | Free mapping solution |
| Charts | Chart.js | Data visualization |
| Backend | PHP 8.x | API endpoints, business logic |
| ML Server | Node.js + Express | ML predictions, model serving |
| ML Models | TensorFlow.js | Overcrowding & waste prediction |
| Database | MySQL | Persistent data storage |

---

## 3. DATABASE CONCEPTUAL MODEL

### 3.1 Entity-Relationship Diagram (ERD)

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│    users     │       │    events    │       │ destinations │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id (PK)      │       │ id (PK)      │       │ id (PK)      │
│ email        │       │ title        │       │ name         │
│ name         │       │ description  │       │ description  │
│ is_admin     │       │ image        │       │ location     │
│ created_at   │       │ datetime     │       │ image        │
└──────────────┘       │ capacity     │       │ category_id  │
                      │ location     │       └──────┬───────┘
                      │ author       │              │
                      │ start_lat/lng│              │
                      │ end_lat/lng  │              │
                      └──────┬───────┘              │
                             │                      │
                      ┌──────▼───────┐        ┌──────▼───────┐
                      │ ml_predictions│        │ destination  │
                      ├──────────────┤        │ _categories  │
                      │ id (PK)      │        ├──────────────┤
                      │ event_id (FK)│        │ id (PK)      │
                      │ attendance   │        │ name         │
                      │ waste_pred   │        │ description  │
                      │ overcrowded  │        └──────────────┘
                      │ probability  │
                      └──────────────┘

┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│    shops     │       │  products    │       │ itineraries  │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id (PK)      │       │ id (PK)      │       │ id (PK)      │
│ name         │       │ shop_id (FK) │       │ user_email   │
│ description  │       │ category_id  │       │ title        │
│ address      │       │ name         │       │ days         │
│ contact      │       │ price        │       │ destinations │
│ owner_name   │       │ image        │       │ created_at   │
│ image        │       └──────────────┘       └──────────────┘
└──────────────┘             │
                    ┌────────▼────────┐
                    │ product_categories│
                    ├─────────────────┤
                    │ id (PK)         │
                    │ shop_id (FK)    │
                    │ name            │
                    │ description     │
                    └─────────────────┘

┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│  feedback    │       │ festivals    │       │ experiences  │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id (PK)      │       │ id (PK)      │       │ id (PK)      │
│ user_email   │       │ name         │       │ title        │
│ user_name    │       │ description  │       │ description  │
│ message      │       │ date_start   │       │ type         │
│ rating       │       │ date_end     │       │ price        │
│ image        │       │ location     │       │ duration     │
│ created_at   │       │ image        │       │ image        │
└──────────────┘       └──────────────┘       └──────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    ANALYTICS TABLES                          │
├─────────────────┬─────────────────┬─────────────────────────┤
│ page_views      │ click_events    │ search_queries          │
├─────────────────┼─────────────────┼─────────────────────────┤
│ id (PK)         │ id (PK)        │ id (PK)                │
│ session_id (FK) │ session_id(FK) │ session_id (FK)        │
│ url             │ selector       │ query_text              │
│ title           │ url            │ url                     │
│ ts              │ ts             │ ts                      │
│ duration_s      │                │                         │
└─────────────────┴─────────────────┴─────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              ITINERARY MANAGEMENT TABLES                    │
├───────────────────────┬───────────────────────┬─────────────┤
│ itinerary_destinations│ itinerary_hotels     │ local_      │
├───────────────────────┼───────────────────────┤ experiences │
│ id (PK)              │ id (PK)              ├─────────────┤
│ name                 │ name                 │ id (PK)     │
│ category             │ category             │ title       │
│ latitude             │ latitude             │ description │
│ longitude            │ longitude            │ type        │
│ description         │ rating               │ price       │
│ activities          │ rate_per_night       │ duration    │
│ image               │ phone                │ image       │
│ created_at          │ address              │             │
└─────────────────────┴───────────────────────┴─────────────┘
```

---

## 4. FUNCTIONAL MODULES

### 4.1 Module 1: Event Management System

**Description:** Admin module for creating, editing, and managing community events and festivals.

**Features:**
- Create/Edit/Delete events with images
- Set event capacity, location, date/time
- Configure start/end coordinates for route planning
- Auto-generate ML predictions for new events
- Event alerts (DO/DO NOT, BRING/DO NOT BRING)

**Key Entities:**
- `events` table
- `event_alerts` table

**API Endpoints:**
```
POST   /api.php?action=create_event
PUT    /api.php?action=edit_event
DELETE /api.php?action=delete_event
GET    /api.php?action=list_events
POST   /api.php?action=set_event_route
```

---

### 4.2 Module 2: ML-Powered Predictions

**Description:** Machine learning system for predicting event overcrowding and waste generation.

**Features:**
- Predict expected attendance based on event attributes
- Calculate overcrowding probability
- Estimate waste generation (kg)
- Provide scenario-based predictions (low/medium/high)
- Historical data training

**ML Models:**
1. **Classification Model (Overcrowding)**
   - Input: attendance, capacity, weekend, is_free, duration, food_stalls, weather
   - Output: Probability of overcrowding (0-1)

2. **Regression Model (Waste Prediction)**
   - Input: Same features
   - Output: Predicted waste in kg

**Key Entities:**
- `ml_predictions` table
- `ml_festival_events` table
- `ml_festival_predictions` table

**API Endpoints:**
```
GET /api.php?action=predict
GET /api.php?action=get_event_predictions
POST /api.php?action=train_ml
POST /api.php?action=predict_with_gb
```

---

### 4.3 Module 3: Interactive Mapping & Routing

**Description:** Leaflet-based mapping system for visualizing events and optimizing routes.

**Features:**
- Display all events on map with risk-level markers
- Route optimization using OSRM (Open Source Routing Machine)
- Multiple route options (Official, Inland Bypass, Coastal Alternate)
- Legazpi City boundary validation
- Real-time route suggestions

**Key Functions:**
- `is_in_legazpi(lat, lng)` - Validates coordinates within city limits
- `predictOptimalRoutes()` - Generates route alternatives
- OSRM API integration for routing

**API Endpoints:**
```
GET /api.php?action=suggest_optimal_route
GET /api.php?action=get_event_parade_details
```

---

### 4.4 Module 5: Itinerary Builder

**Description:** User-facing module for creating personalized travel itineraries.

**Features:**
- Multi-day trip planning
- Destination selection (predefined + admin-added)
- Hotel recommendations with ratings & prices
- Transportation fare calculations
- Weather forecasts
- PDF export functionality

**Key Entities:**
- `itineraries` table
- `itinerary_destinations` table
- `itinerary_hotels` table

**API Endpoints:**
```
POST /api.php?action=save_itinerary
GET  /api.php?action=list_itineraries
GET  /api.php?action=get_itinerary
PUT  /api.php?action=edit_itinerary
DELETE /api.php?action=delete_itinerary
```

---

### 4.5 Module 6: Tourism Directory

**Description:** Directory of local attractions, experiences, festivals, and shops.

**Features:**
- Destinations with categories
- Local experiences (tours, activities)
- Festivals & events with video support
- Novelty shops with product catalogs

**Key Entities:**
- `destinations` table
- `destination_categories` table
- `local_experiences` table
- `festivals_events` table
- `shops` table
- `products` table

**API Endpoints:**
```
GET /api.php?action=list_destinations
GET /api.php?action=list_experiences
GET /api.php?action=list_festivals
GET /api.php?action=list_shops
GET /api.php?action=list_products
```

---

### 4.6 Module 7: Analytics & Tracking

**Description:** Comprehensive analytics system for understanding user behavior and event performance.

**Features:**
- Anonymous session tracking
- Page view analytics
- Click event logging
- Search query tracking
- Visitor geolocation
- Place/shop interaction tracking

**Key Entities:**
- `anonymous_sessions` table
- `page_views` table
- `click_events` table
- `search_queries` table
- `place_analytics` table
- `shop_interactions` table
- `activity_logs` table

**API Endpoints:**
```
POST /api.php?action=track_anonymous
POST /api.php?action=log_event
GET  /api.php?action=analytics_summary
GET  /api.php?action=get_visitor_geolocation
```

---

### 4.7 Module 8: Feedback System

**Description:** User feedback collection and display system.

**Features:**
- Star rating (1-5)
- Anonymous posting option
- Image attachment support
- Admin moderation

**Key Entities:**
- `feedback` table

**API Endpoints:**
```
POST /api.php?action=add_feedback
GET  /api.php?action=list_feedback
DELETE /api.php?action=delete_feedback
```

---

## 5. USER INTERFACE COMPONENTS

### 5.1 Public Website (index.html)

| Section | Description | Key Components |
|---------|-------------|----------------|
| Hero | Landing page with parallax effect | Navigation, CTA buttons |
| Community Events | Event cards with ML predictions | Map, Charts, Risk indicators |
| Destinations | Tourist spots list | Card modals with details |
| Create Itinerary | Trip planning tool | Form, Hotel selection, Weather |
| Local Experiences | Activities & tours | Cards with pricing |
| Festivals | Event listings with video | Modal player |
| Shop | Novelty stores & products | Category filters, Product grids |
| Footer | Contact & feedback | Feedback form |

### 5.2 Admin Dashboard (admin_dashboard.html)

| Section | Description |
|---------|-------------|
| Events Management | CRUD operations, ML predictions |
| Itinerary Management | Destination/Hotel CRUD |
| Analytics | Visitor stats, page views, top searches |
| Festival Predictions | ML-powered forecasting |

---

## 6. DATA FLOW DIAGRAMS

### 6.1 Event Creation Flow

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

### 6.2 Prediction Request Flow

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
│  (PHP/Node.js)  │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────────┐
│Logistic│ │  Linear    │
│Regres. │ │ Regression │
└───┬────┘ └─────┬──────┘
    │            │
    └─────┬──────┘
          │
          ▼
┌─────────────────┐
│  Generate       │
│  Predictions    │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌────────────┐
│ Store  │ │ Return     │
│ in DB  │ │ JSON       │
└────────┘ └────────────┘
```

---

## 7. SECURITY CONSIDERATIONS

| Aspect | Implementation |
|--------|----------------|
| Admin Authentication | Session-based auth (`admin_session.php`) |
| SQL Injection | Prepared statements in PHP |
| Input Validation | Server-side validation |
| Coordinate Validation | Legazpi boundary polygon check |
| Anonymous Tracking | IP anonymization, no personal data |
| File Uploads | Path validation, allowed extensions |

---

## 8. INTEGRATION POINTS

### 8.1 External APIs

| Service | Purpose | Authentication |
|---------|---------|----------------|
| OpenStreetMap | Map tiles | None (free) |
| OSRM | Routing engine | None (free) |
| Nominatim | Geocoding | User-Agent required |

### 8.2 Internal Modules

| Module | Communication |
|--------|---------------|
| ML Server (Node.js) | HTTP REST API |
| PHP Backend | Internal function calls |
| Frontend | Fetch API to PHP |

---

## 9. DEPLOYMENT ARCHITECTURE

### Development Environment
```
Windows 11 + XAMPP
├── Apache (Port 80) - PHP Backend
├── MySQL (Port 3306) - Database
└── Node.js (Port 3000) - ML Server
```

### Recommended Production
```
Linux Server (Ubuntu/Debian)
├── Nginx (Reverse Proxy)
│   ├── :80 → Apache (:8080)
│   └── :3000 → Node.js PM2
├── Apache + PHP-FPM
├── MySQL 8
└── PM2 (Node.js process manager)
```

---

## 10. KEY DESIGN DECISIONS

| Decision | Rationale |
|----------|-----------|
| **Leaflet + OpenStreetMap** | Free, no API key required, works in Philippines |
| **OSRM for Routing** | Free alternative to Google Maps |
| **TensorFlow.js for ML** | Runs in browser + Node.js, no GPU needed |
| **PHP Backend** | Legacy compatibility, XAMPP stack |
| **Anonymous Analytics** | GDPR-friendly, no consent required |
| **Legazpi Boundary Check** | Data integrity, prevent invalid coordinates |
| **Scenario-Based Predictions** | Better decision-making (low/medium/high) |

---

## 11. SYSTEM CAPABILITIES SUMMARY

✅ **Event Management** - Full CRUD with ML predictions  
✅ **Crowd Prediction** - AI-powered overcrowding forecasting  
✅ **Waste Estimation** - Garbage generation predictions  
✅ **Interactive Maps** - Leaflet + OpenStreetMap integration  
✅ **Route Optimization** - OSRM-based routing suggestions  
✅ **Itinerary Builder** - Multi-day trip planning with hotels  
✅ **Tourism Directory** - Destinations, experiences, shops  
✅ **Analytics Dashboard** - Visitor behavior tracking  
✅ **Feedback System** - User reviews and ratings  
✅ **PDF Export** - Downloadable itineraries  

---

*Document Version: 1.0*  
*Generated: 2025*
*Project: Legazpi Explorer - Capstone Project*
