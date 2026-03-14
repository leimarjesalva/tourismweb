# 🏛️ LEGAZPI EXPLORER - COMPREHENSIVE CONCEPTUAL DESIGN

**Project Name:** Legazpi Explorer  
**Project Type:** Full-Stack Web Application with ML Integration  
**Version:** 2.0  
**Date:** 2025  
**Location:** Legazpi City, Albay, Philippines  

---

## 1. EXECUTIVE SUMMARY

### 1.1 Purpose
The **Legazpi Explorer** is a comprehensive tourism management system designed to support Legazpi City in Albay, Philippines—a popular tourist destination known for the Mayon Volcano and rich cultural heritage. The system provides intelligent event management, visitor analytics, ML-powered crowd and waste predictions, interactive mapping, and itinerary planning.

### 1.2 Scope
| Component | Description |
|-----------|-------------|
| **Public Portal** | Tourism information, events, itineraries, feedback |
| **Admin Dashboard** | Event management, analytics, content moderation |
| **ML Server** | AI predictions for crowd overcapacity and waste estimation |
| **Database** | MySQL with comprehensive tourism data |

### 1.3 Target Users
- **Tourists/Visitors** - Seeking travel information and planning trips
- **Event Organizers** - Managing festivals and community events
- **Administrators** - Managing the platform and content
- **Local Businesses** - Shops, hotels, tourism providers

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
│  │  │                   │  │  (Admin Panel)   │  │                   │              │  │
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
│  └─────────────────────────┘ │ │ │  └─────────────────────┘  │ │                           │
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

### 2.2 Technology Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Frontend** | HTML5, CSS3, JavaScript | User interface, interactive maps |
| **Maps** | Leaflet.js + OpenStreetMap | Free mapping solution |
| **Charts** | Chart.js | Data visualization |
| **PDF Generation** | jsPDF + html2canvas | Export itineraries |
| **ML (Browser)** | TensorFlow.js | Client-side ML inference |
| **Backend** | PHP 8.x | API endpoints, business logic |
| **ML Server** | Node.js + Express | ML predictions, model serving |
| **ML Models** | TensorFlow.js | Overcrowding & waste prediction |
| **Database** | MySQL | Persistent data storage |

---

## 3. FUNCTIONAL MODULES

### 3.1 Module Overview

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    MODULE ARCHITECTURE                                       │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   EVENT     │    │     ML      │    │   MAPPING   │    │  ITINERARY  │    │  TOURISM    │
│ MANAGEMENT  │    │ PREDICTIONS │    │   & ROUTING │    │   BUILDER   │    │  DIRECTORY  │
├─────────────┤    ├─────────────┤    ├─────────────┤    ├─────────────┤    ├─────────────┤
│• CRUD Events│    │• Overcrowd  │    │• Interactive│    │• Multi-day  │    │• Destinations│
│• Event Alerts│    │  Prediction │    │  Maps        │    │  Trip Plans │    │• Experiences │
│• Images     │    │• Waste Est. │    │• Route Opt.  │    │• PDF Export │    │• Festivals   │
│• Capacity   │    │• Historical  │    │• OSRM        │    │• Hotels     │    │• Shops       │
│             │    │  Analysis    │    │  Integration │    │• Weather    │    │• Products    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  ANALYTICS  │    │  FEEDBACK  │    │    ADMIN    │
│   TRACKING  │    │   SYSTEM   │    │  DASHBOARD  │
├─────────────┤    ├─────────────┤    ├─────────────┤
│• Page Views │    │• Star       │    │• Event Mgmt │
│• Click Data │    │  Ratings    │    │• Shop Mgmt  │
│• Geolocation│    │• Anonymous  │    │• Analytics  │
│• Popularity │    │  Posting    │    │• Predictions│
│• Sessions   │    │• Image      │    │• Settings   │
│             │    │  Attachments│    │             │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 3.2 Module 1: Event Management System

**Purpose:** Admin module for creating, editing, and managing community events and festivals.

**Features:**
- Create/Edit/Delete events with images
- Set event capacity, location, date/time
- Configure start/end coordinates for route planning
- Auto-generate ML predictions for new events
- Event alerts (DO/DO NOT, BRING/DO NOT BRING)

**Data Model:**
```
events
├── id (PK)
├── title
├── description
├── image
├── datetime
├── location
├── capacity
├── author
├── start_lat, start_lng
├── end_lat, end_lng
└── created_at

event_alerts
├── id (PK)
├── event_id (FK)
├── alert_type (DO/DO_NOT/BRING/DO_NOT_BRING)
└── content
```

**API Endpoints:**
```
POST   /api.php?action=create_event
PUT    /api.php?action=edit_event
DELETE /api.php?action=delete_event
GET    /api.php?action=list_events
POST   /api.php?action=add_event_alert
GET    /api.php?action=get_event_alerts
```

---

### 3.3 Module 2: ML-Powered Predictions

**Purpose:** Machine learning system for predicting event overcrowding and waste generation.

**Features:**
- Predict expected attendance based on event attributes
- Calculate overcrowding probability (0-100%)
- Estimate waste generation (kg)
- Provide scenario-based predictions (low/medium/high)
- Historical data training

**ML Models:**

| Model | Type | Input Features | Output |
|-------|------|----------------|--------|
| **Classification** | Binary | attendance, capacity, weekend, is_free, duration, food_stalls, weather | Overcrowding probability (0-1) |
| **Regression** | Linear | Same features | Waste in kg |

**Architecture:**
```
Input: [attendance, capacity, weekend, is_free, duration, food_stalls, weather]
         │
         ▼
    [Dense Layer 1] - 64 units, ReLU
         │
         ▼
    [Dense Layer 2] - 32 units, ReLU
         │
         ▼
    ┌────┴────┐
    │         │
    ▼         ▼
┌───────┐ ┌───────┐
│ Sigmoid│ │Linear │
│(Class)│ │(Waste)│
└───────┘ └───────┘
```

**API Endpoints:**
```
GET  /api.php?action=predict
GET  /api.php?action=get_event_predictions
POST /api.php?action=train_ml
POST /api.php?action=predict_with_gb
```

---

### 3.4 Module 3: Interactive Mapping & Routing

**Purpose:** Leaflet-based mapping system for visualizing events and optimizing routes.

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

### 3.5 Module 4: Itinerary Builder

**Purpose:** User-facing module for creating personalized travel itineraries.

**Features:**
- Multi-day trip planning
- Destination selection (predefined + admin-added)
- Hotel recommendations with ratings & prices
- Transportation fare calculations
- Weather forecasts
- PDF export functionality

**Data Model:**
```
itineraries
├── id (PK)
├── user_email
├── user_name
├── anonymous
├── title
├── days
├── destinations (JSON)
└── created_at

itinerary_destinations
├── id (PK)
├── name
├── category
├── latitude, longitude
├── description
├── activities
└── image

itinerary_hotels
├── id (PK)
├── name
├── category
├── latitude, longitude
├── rating
├── rate_per_night
├── phone
├── address
├── features
└── image
```

**API Endpoints:**
```
POST   /api.php?action=save_itinerary
GET    /api.php?action=list_itineraries
GET    /api.php?action=get_itinerary
PUT    /api.php?action=edit_itinerary
DELETE /api.php?action=delete_itinerary
GET    /api.php?action=list_itinerary_destinations
GET    /api.php?action=list_itinerary_hotels
```

---

### 3.6 Module 5: Tourism Directory

**Purpose:** Directory of local attractions, experiences, festivals, and shops.

**Features:**
- Destinations with categories
- Local experiences (tours, activities)
- Festivals & events with video support
- Novelty shops with product catalogs

**Data Model:**
```
destinations
├── id (PK)
├── name
├── description
├── location
├── image
└── category_id (FK)

destination_categories
├── id (PK)
├── name
└── description

local_experiences
├── id (PK)
├── title
├── description
├── type
├── price
├── duration
└── image

festivals_events
├── id (PK)
├── name
├── description
├── date_start
├── date_end
├── location
└── image

shops
├── id (PK)
├── name
├── description
├── address
├── contact
├── owner_name
├── image
└── clicks

products
├── id (PK)
├── shop_id (FK)
├── category_id (FK)
├── name
├── description
├── price
├── stock
└── image
```

---

### 3.7 Module 6: Analytics & Tracking

**Purpose:** Comprehensive analytics system for understanding user behavior and event performance.

**Features:**
- Anonymous session tracking (GDPR-friendly)
- Page view analytics
- Click event logging
- Search query tracking
- Visitor geolocation
- Place/shop interaction tracking

**Data Model:**
```
anonymous_sessions
├── id (PK)
├── session_id (UNIQUE)
├── ip_address
├── city
├── country
├── device_type
├── first_visit
├── last_visit
├── page_views
└── total_time_minutes

page_views
├── id (PK)
├── session_id (FK)
├── url
├── title
├── ts
└── duration_s

click_events
├── id (PK)
├── session_id (FK)
├── ts
├── selector
└── url

place_analytics
├── id (PK)
├── place_name
├── view_count
├── click_count
├── avg_time_spent_seconds
└── last_viewed

shop_interactions
├── id (PK)
├── shop_id
├── shop_name
├── view_count
├── click_count
├── avg_time_spent_seconds
└── last_viewed
```

---

### 3.8 Module 7: Feedback System

**Purpose:** User feedback collection and display system.

**Features:**
- Star rating (1-5)
- Anonymous posting option
- Image attachment support
- Admin moderation
- Live scrolling feedback carousel

**Data Model:**
```
feedback
├── id (PK)
├── user_email
├── user_name
├── anonymous
├── message
├── rating
├── image
└── created_at
```

---

## 4. DATABASE DESIGN

### 4.1 Entity-Relationship Diagram

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
              ┌──────────────────┐ ┐
              ┌────────────────── │     events       │  │    itineraries   │
              ├──────────────────┤  ├──────────────────┤
              │ id (PK)          │  │ id (PK)          │
              │ title            │  │ user_email       │
              │ description      │  │ title            │
              │ datetime         │  │ days             │
              │ location         │  │ destinations     │
              │ capacity         │  └────────┬─────────┘
              │ author           │           │
              │ start_lat/lng    │           │
              │ end_lat/lng      │           │
              └────────┬─────────┘           │
                       │                     │
          ┌────────────┴────────────┐        │
          │                         │        │
          ▼                         ▼        ▼
┌──────────────────┐    ┌──────────────────┐  ┌──────────────────┐
│  event_alerts   │    │ ml_predictions   │  │event_predictions │
├──────────────────┤    ├──────────────────┤  ├──────────────────┤
│ id (PK)         │    │ id (PK)          │  │ id (PK)          │
│ event_id (FK)   │    │ event_id (FK)    │  │ event_id (FK)   │
│ alert_type       │    │ attendance       │  │ predicted_vis... │
│ content          │    │ overcrowded      │  │ crowd_status     │
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

---

## 5. USER INTERFACE DESIGN

### 5.1 Public Website (index.html)

| Section | Description | Key Components |
|---------|-------------|----------------|
| **Hero** | Landing page with parallax effect | Navigation, CTA buttons |
| **Community Events** | Event cards with ML predictions | Map, Charts, Risk indicators |
| **Destinations** | Tourist spots list | Card modals with details |
| **Create Itinerary** | Trip planning tool | Form, Hotel selection, Weather |
| **Local Experiences** | Activities & tours | Cards with pricing |
| **Festivals** | Event listings with video | Modal player |
| **Shop** | Novelty stores & products | Category filters, Product grids |
| **Footer** | Contact & feedback | Feedback form |

### 5.2 Admin Dashboard (admin_dashboard.html)

| Section | Description |
|---------|-------------|
| **Overview** | Statistics cards, quick actions |
| **Events Management** | CRUD operations, ML predictions, Alerts |
| **Itinerary Management** | Destination/Hotel CRUD |
| **Analytics** | Visitor stats, page views, top searches |
| **Festival Predictions** | ML-powered forecasting |
| **Settings** | Admin profile, system configuration |

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
| **Admin Authentication** | Session-based auth (`admin_session.php`) |
| **SQL Injection** | Prepared statements in PHP |
| **Input Validation** | Server-side validation |
| **Coordinate Validation** | Legazpi boundary polygon check |
| **Anonymous Analytics** | IP anonymization, no personal data |
| **File Uploads** | Path validation, allowed extensions |
| **Google OAuth** | Server-side token validation |

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

## 10. SYSTEM CAPABILITIES SUMMARY

| Feature | Status |
|---------|--------|
| **Event Management** | ✅ Full CRUD with ML predictions |
| **Crowd Prediction** | ✅ AI-powered overcrowding forecasting |
| **Waste Estimation** | ✅ Garbage generation predictions |
| **Interactive Maps** | ✅ Leaflet + OpenStreetMap integration |
| **Route Optimization** | ✅ OSRM-based routing suggestions |
| **Itinerary Builder** | ✅ Multi-day trip planning with hotels |
| **Tourism Directory** | ✅ Destinations, experiences, shops |
| **Analytics Dashboard** | ✅ Visitor behavior tracking |
| **Feedback System** | ✅ User reviews and ratings |
| **PDF Export** | ✅ Downloadable itineraries |

---

## 11. KEY DESIGN DECISIONS

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

## 12. API ENDPOINTS REFERENCE

### Authentication
```
POST /api.php?action=login
POST /api.php?action=logout
GET  /api.php?action=check_session
```

### Events
```
GET    /api.php?action=list_events
POST   /api.php?action=create_event
PUT    /api.php?action=edit_event
DELETE /api.php?action=delete_event
POST   /api.php?action=add_event_alert
GET    /api.php?action=get_event_alerts
```

### Predictions
```
GET /api.php?action=predict
GET /api.php?action=get_event_predictions
POST /api.php?action=train_ml
POST /api.php?action=predict_with_gb
```

### Itineraries
```
POST   /api.php?action=save_itinerary
GET    /api.php?action=list_itineraries
GET    /api.php?action=get_itinerary
PUT    /api.php?action=edit_itinerary
DELETE /api.php?action=delete_itinerary
GET    /api.php?action=list_itinerary_destinations
GET    /api.php?action=list_itinerary_hotels
```

### Directory
```
GET /api.php?action=list_destinations
GET /api.php?action=list_experiences
GET /api.php?action=list_festivals
GET /api.php?action=list_shops
GET /api.php?action=list_products
```

### Analytics
```
POST /api.php?action=track_anonymous
POST /api.php?action=log_event
GET  /api.php?action=analytics_summary
GET  /api.php?action=most_viewed_places
GET  /api.php?action=most_clicked_shops
```

### Feedback
```
POST /api.php?action=add_feedback
GET  /api.php?action=list_feedback
DELETE /api.php?action=delete_feedback
```

### Routing
```
GET /api.php?action=suggest_optimal_route
GET /api.php?action=get_event_parade_details
```

---

## 13. CONCLUSION

The **Legazpi Explorer** system provides a comprehensive solution for tourism management in Legazpi City. With its combination of event management, ML-powered predictions, interactive mapping, and analytics tracking, it offers a robust platform for both tourists and administrators. The system is designed to be scalable, secure, and easy to maintain, making it suitable for both development and production environments.

---

*Document Version: 2.0*  
*Generated: 2025*  
*Project: Legazpi Explorer - Capstone Project*

