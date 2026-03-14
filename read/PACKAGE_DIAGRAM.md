# Package Diagram - Legazpi Explorer System

## Overview
This document shows the complete package structure of the Legazpi Explorer tourism management system, including the Frontend, Backend API, ML Server, and Database components.

```

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                              LEGAZPI EXPLORER SYSTEM                                       │
│                                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                    FRONTEND                                         │  │
│  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐                   │  │
│  │  │    index.html    │  │admin_dashboard   │  │   script.js      │                   │  │
│  │  │  (Public Portal) │  │    _.html        │  │ (Main Logic)    │                   │  │
│  │  │                  │  │ (Admin Panel)    │  │                  │                   │  │
│  │  │  • Destinations │  │                  │  │ • Event Loading │                   │  │
│  │  │  • Experiences  │  │ • Event Mgmt     │  │ • Map Init      │                   │  │
│  │  │  • Festivals    │  │ • Shop Mgmt      │  │ • Charts        │                   │  │
│  │  │  • Itinerary    │  │ • Predictions    │  │ • Predictions   │                   │  │
│  │  │  • Shop         │  │ • Analytics      │  │ • Routing       │                   │  │
│  │  │  • Feedback     │  │ • Settings       │  │ • Itinerary     │                   │  │
│  │  └────────┬─────────┘  └────────┬─────────┘  └────────┬─────────┘                   │  │
│  │           │                     │                     │                             │  │
│  │           └─────────────────────┴─────────────────────┘                             │  │
│  │                                 │                                                   │  │
│  │                    ┌────────────┴────────────┐                                        │  │
│  │                    │     style.css         │                                        │  │
│  │                    │  (Styling & Layout)   │                                        │  │
│  │                    └───────────────────────┘                                        │  │
│  │                                                                                     │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────┐   │  │
│  │  │                            ASSETS                                            │   │  │
│  │  │  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐                   │   │  │
│  │  │  │     img/      │  │    assets/     │  │   uploads/    │                   │   │  │
│  │  │  │  (Images)     │  │ (Static Files) │  │  (User Files) │                   │   │  │
│  │  │  └────────────────┘  └────────────────┘  └────────────────┘                   │   │  │
│  │  └─────────────────────────────────────────────────────────────────────────────┘   │  │
│  └─────────────────────────────────────────────────────────────────────────────────────┘  │
│                                            │                                               │
│                              HTTP/REST API │                                               │
│                                            ▼                                               │
│  ┌─────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                  BACKEND (PHP)                                       │  │
│  │                                                                                      │  │
│  │  ┌───────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │                           API LAYER                                            │  │  │
│  │  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐            │  │  │
│  │  │  │     api.php      │  │api_endpoints_    │  │ event_predic-   │            │  │  │
│  │  │  │ (Main Router)   │  │ extended.php     │  │ tions_api.php  │            │  │  │
│  │  │  │                  │  │                  │  │                │            │  │  │
│  │  │  │ • Action Router │  │ • Extended API   │  │ • Generate     │            │  │  │
│  │  │  │ • JSON Response│  │ • Festival API   │  │ • Get Preds    │            │  │  │
│  │  │  │ • Error Handle │  │ • Route API      │  │ • Save Preds   │            │  │  │
│  │  │  └────────┬─────────┘  └────────┬─────────┘  └────────┬─────────┘            │  │  │
│  │  │           │                     │                     │                      │  │  │
│  │  │           └──────────┬───────────┴──────────┬───────────┘                      │  │  │
│  │  │                    ┌──────────────┐        │                                   │  │  │
│  │  │                    │ predictions_ │        │                                   │  │  │
│  │  │                    │ api.php      │◄───────┘                                   │  │  │
│  │  │                    └──────────────┘                                            │  │  │
│  │  └───────────────────────────────────────────────────────────────────────────────┘  │  │
│  │                                            │                                        │  │
│  │  ┌─────────────────────────────────────────┴────────────────────────────────────┐  │  │
│  │  │                           BUSINESS LOGIC LAYER                               │  │  │
│  │  │                                                                              │  │  │
│  │  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐         │  │  │
│  │  │  │ db.php           │  │ admin_login.php  │  │ admin_session    │         │  │  │
│  │  │  │ (Database)       │  │ (Auth)          │  │ .php (Session)   │         │  │  │
│  │  │  │                  │  │                  │  │                  │         │  │  │
│  │  │  │ • Connection     │  │ • Login Check    │  │ • Session Mgmt  │         │  │  │
│  │  │  │ • Query Helper  │  │ • Password Hash  │  │ • Auth Verify   │         │  │  │
│  │  │  └──────────────────┘  └──────────────────┘  └──────────────────┘         │  │  │
│  │  │                                                                              │  │  │
│  │  │  ┌──────────────────────────────────────────────────────────────────────┐   │  │  │
│  │  │  │                     ML INTEGRATION                                   │   │  │  │
│  │  │  │  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────────┐ │   │  │  │
│  │  │  │  │ MLServerBridge   │  │  ml_api_client   │  │  ml_predict    │ │   │  │  │
│  │  │  │  │     .php         │  │      .php        │  │     .php       │ │   │  │  │
│  │  │  │  │                  │  │                  │  │                 │ │   │  │  │
│  │  │  │  │ • HTTP Client    │  │ • API Wrapper    │  │ • Direct Pred  │ │   │  │  │
│  │  │  │  │ • Fallback      │  │ • Error Handle   │  │ • Model Load   │ │   │  │  │
│  │  │  │  │ • Cache        │  │ • Retry Logic   │  │ • Normalize    │ │   │  │  │
│  │  │  │  └──────────────────┘  └──────────────────┘  └─────────────────┘ │   │  │  │
│  │  │  └──────────────────────────────────────────────────────────────────────┘   │  │  │
│  │  │                                                                              │  │  │
│  │  │  ┌──────────────────────────────────────────────────────────────────────┐   │  │  │
│  │  │  │                     UPLOAD HANDLERS                                   │   │  │  │
│  │  │  │  upload_event_image.php  |  upload_destination_image.php             │   │  │  │
│  │  │  │  upload_shop_image.php   |  upload_product_image.php                │   │  │  │
│  │  │  │  upload_experience_image.php | upload_festival_image.php            │   │  │  │
│  │  │  │  upload_feedback_image.php | upload_itinerary_image.php            │   │  │  │
│  │  │  │  upload_admin_profile.php  |  verify_google.php                    │   │  │  │
│  │  │  └──────────────────────────────────────────────────────────────────────┘   │  │  │
│  │  └────────────────────────────────────────────────────────────────────────────┘  │  │
│  │                                            │                                        │  │
│  │                              ┌─────────────┴─────────────┐                            │  │
│  │                              │   DATA LAYER             │                            │  │
│  │                              │   (MySQL Database)      │                            │  │
│  │                              └─────────────┬─────────────┘                            │  │
│  └────────────────────────────────────────────┼────────────────────────────────────────┘  │
│                                                 │                                         │
│                                  MySQL Connection │                                         │
│                                                 ▼                                         │
│  ┌─────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                               DATABASE (MySQL)                                       │  │
│  │                                                                                      │  │
│  │  ┌───────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │                        MAIN TABLES                                            │  │  │
│  │  │                                                                                │  │  │
│  │  │  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ │  │  │
│  │  │  │   users    │ │   events   │ │   shops    │ │destinations│ │ products  │ │  │  │
│  │  │  │            │ │            │ │            │ │            │ │            │ │  │  │
│  │  │  │ • id       │ │ • id       │ │ • id       │ │ • id       │ │ • id       │ │  │  │
│  │  │  │ • email    │ │ • title    │ │ • name     │ │ • name     │ │ • name     │ │  │  │
│  │  │  │ • name     │ │ • datetime │ │ • address  │ │ • location │ │ • price    │ │  │  │
│  │  │  │ • is_admin │ │ • location │ │ • contact  │ │ • image    │ │ • stock    │ │  │  │
│  │  │  │            │ │ • capacity  │ │ • owner    │ │ • category │ │ • shop_id  │ │  │  │
│  │  │  └────────────┘ └────────────┘ └────────────┘ └────────────┘ └────────────┘ │  │  │
│  │  │                                                                                │  │  │
│  │  │  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ │  │  │
│  │  │  │feedback    │ │itineraries │ │ destination│ │product_    │ │  festival  │ │  │  │
│  │  │  │            │ │            │ │_categories │ │_categories │ │s_events   │ │  │  │
│  │  │  │ • id       │ │ • id       │ │ • id       │ │ • id       │ │ • id       │ │  │  │
│  │  │  │ • message  │ │ • title    │ │ • name     │ │ • name     │ │ • name     │ │  │  │
│  │  │  │ • rating   │ │ • days     │ │            │ │ • shop_id  │ │ • date     │ │  │  │
│  │  │  │ • image    │ │ •destinations│            │ │            │ │ • location │ │  │  │
│  │  │  └────────────┘ └────────────┘ └────────────┘ └────────────┘ └────────────┘ │  │  │
│  │  │                                                                                │  │  │
│  │  │  ┌──────────────────────────────────────────────────────────────────────────┐  │  │  │
│  │  │  │             LOCAL EXPERIENCES                                            │  │  │  │
│  │  │  │  ┌─────────────────────────────────────────┐ ┌─────────────────────────┐ │  │  │  │
│  │  │  │  │         local_experiences               │ │  festivals_events     │ │  │  │  │
│  │  │  │  │ • id                                   │ │  • id                 │ │  │  │  │
│  │  │  │  │ • title                                │ │  • name               │ │  │  │  │
│  │  │  │  │ • description                          │ │  • description        │ │  │  │  │
│  │  │  │  │ • type, price, duration, image        │ │  • date_start/end     │ │  │  │  │
│  │  │  │  └─────────────────────────────────────────┘ └─────────────────────────┘ │  │  │  │
│  │  │  └──────────────────────────────────────────────────────────────────────────┘  │  │  │
│  │  └───────────────────────────────────────────────────────────────────────────────┘  │  │
│  │                                            │                                        │  │
│  │  ┌─────────────────────────────────────────┴───────────────────────────────────────┐  │  │
│  │  │                          ML TABLES                                             │  │  │
│  │  │                                                                                 │  │  │
│  │  │  ┌────────────────┐  ┌──────────────────┐  ┌────────────────────┐              │  │  │
│  │  │  │ ml_predictions │  │ ml_festival_    │  │  ml_festival_     │              │  │  │
│  │  │  │                │  │ events          │  │  predictions      │              │  │  │
│  │  │  │ • id           │  │ • id            │  │  • id             │              │  │  │
│  │  │  │ • event_id     │  │ • event_id      │  │  • festival_id    │              │  │  │
│  │  │  │ • attendance   │  │ • event_name    │  │  • attendance     │              │  │  │
│  │  │  │ • overcrowded  │  │ • event_date    │  │  • waste_kg      │              │  │  │
│  │  │  │ • waste_pred  │  │ • expected_    │  │  • overcrowding   │              │  │  │
│  │  │  │                │  │   attendance    │  │  • confidence    │              │  │  │
│  │  │  └────────────────┘  └──────────────────┘  └────────────────────┘              │  │  │
│  │  │                                                                                 │  │  │
│  │  │  ┌──────────────────────┐  ┌────────────────────┐                            │  │  │
│  │  │  │ ml_model_metrics     │  │ ml_api_logs       │                            │  │  │
│  │  │  │                      │  │                   │                            │  │  │
│  │  │  │ • model_type         │  │ • endpoint        │                            │  │  │
│  │  │  │ • accuracy           │  │ • response_time   │                            │  │  │
│  │  │  │ • precision/recall   │  │ • status_code     │                            │  │  │
│  │  │  │ • f1_score           │  │                   │                            │  │  │
│  │  │  └──────────────────────┘  └────────────────────┘                            │  │  │
│  │  └───────────────────────────────────────────────────────────────────────────────┘  │  │
│  │                                            │                                        │  │
│  │  ┌─────────────────────────────────────────┴───────────────────────────────────────┐  │  │
│  │  │                       ANALYTICS TABLES                                         │  │  │
│  │  │                                                                                │  │  │
│  │  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐            │  │  │
│  │  │  │ attendance_      │  │ activity_logs    │  │ event_           │            │  │  │
│  │  │  │ history          │  │                  │  │ predictions      │            │  │  │
│  │  │  │                  │  │ • user_email     │  │                  │            │  │  │
│  │  │  │ • event_id       │  │ • page          │  │ • event_id      │            │  │  │
│  │  │  │ • attendance     │  │ • action        │  │ • predicted_    │            │  │  │
│  │  │  │ • date           │  │ • meta (JSON)   │  │   visitors      │            │  │  │
│  │  │  └──────────────────┘  └──────────────────┘  │ • crowd_status   │            │  │  │
│  │  │                                              └──────────────────┘            │  │  │
│  │  │                                                                                │  │  │
│  │  │  ┌──────────────────────────────────────────────────────────────────────────┐  │  │  │
│  │  │  │                    ITINERARY MANAGEMENT                                 │  │  │  │
│  │  │  │  ┌────────────────────────────┐ ┌────────────────────────────────┐     │  │  │  │
│  │  │  │  │ itinerary_destinations    │ │ itinerary_hotels               │     │  │  │  │
│  │  │  │  │ • id                     │ │  • id                          │     │  │  │  │
│  │  │  │  │ • name                   │ │  • name                        │     │  │  │  │
│  │  │  │  │ • latitude/longitude     │ │  • latitude/longitude          │     │  │  │  │
│  │  │  │  │ • category, activities   │ │  • rating, rate_per_night      │     │  │  │  │
│  │  │  │  │ • image                  │ │  • phone, address, features    │     │  │  │  │
│  │  │  │  └────────────────────────────┘ └────────────────────────────────┘     │  │  │  │
│  │  │  └──────────────────────────────────────────────────────────────────────────┘  │  │  │
│  │  └───────────────────────────────────────────────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────────────────────────────────┘  │
│                                             │                                               │
│                                    HTTP API (Port 3000) │                                  │
│                                             ▼                                               │
│  ┌─────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                              ML SERVER (Node.js)                                     │  │
│  │                                                                                      │  │
│  │  ┌───────────────────────────────────────────────────────────────────────────────┐   │  │
│  │  │                         EXPRESS.JS SERVER                                     │   │  │
│  │  │                                                                               │   │  │
│  │  │  ┌────────────────────────────────────────────────────────────────────────┐   │   │  │
│  │  │  │                        API ENDPOINTS                                   │   │   │  │
│  │  │  │                                                                         │   │   │  │
│  │  │  │  POST /predict          - Single prediction                          │   │   │  │
│  │  │  │  POST /predict-batch    - Batch predictions                          │   │   │  │
│  │  │  │  GET  /history          - Prediction history                        │   │   │  │
│  │  │  │  GET  /stats            - Statistics                                │   │   │  │
│  │  │  │  POST /optimize-route   - Route optimization                       │   │   │  │
│  │  │  │  GET  /health           - Health check                             │   │   │  │
│  │  │  │                                                                         │   │   │  │
│  │  │  └────────────────────────────────────────────────────────────────────────┘   │   │  │
│  │  │                                          │                                    │   │  │
│  │  │  ┌──────────────────────────────────────┴────────────────────────────────┐   │   │  │
│  │  │  │                         MODEL LAYER                                │   │   │  │
│  │  │  │                                                                      │   │   │  │
│  │  │  │  ┌──────────────────────────┐  ┌──────────────────────────┐       │   │   │  │
│  │  │  │  │  Classification Model   │  │   Regression Model       │       │   │   │  │
│  │  │  │  │  (Overcrowding)        │  │   (Waste Prediction)    │       │   │   │  │
│  │  │  │  │                         │  │                          │       │   │   │  │
│  │  │  │  │ • TensorFlow.js        │  │  • TensorFlow.js        │       │   │   │  │
│  │  │  │  │ • 64 -> 32 -> 1 units │  │  • 64 -> 32 -> 1 units │       │   │   │  │
│  │  │  │  │ • Sigmoid activation   │  │  • Linear activation   │       │   │   │  │
│  │  │  │  │ • Binary output        │  │  • Continuous output  │       │   │   │  │
│  │  │  │  └──────────────────────────┘  └──────────────────────────┘       │   │   │  │
│  │  │  │                                                                      │   │   │  │
│  │  │  │  ┌──────────────────────────────────────────────────────────────┐   │   │  │  │
│  │  │  │  │              TRAINING SCRIPTS                               │   │   │  │  │
│  │  │  │  │  train.js  |  train_route_model.js  |  ml_trainer.php      │   │   │  │  │
│  │  │  │  └──────────────────────────────────────────────────────────────┘   │   │  │  │
│  │  │  │                                                                      │   │   │  │
│  │  │  │  ┌──────────────────────────────────────────────────────────────┐   │   │  │  │
│  │  │  │  │              SAVED MODELS                                    │   │   │  │  │
│  │  │  │  │  best-overcrowding-model/  |  best-waste-model/            │   │   │  │  │
│  │  │  │  │  normalization.json         |  schema.sql                  │   │   │  │  │
│  │  │  │  └──────────────────────────────────────────────────────────────┘   │   │  │  │
│  │  │  └────────────────────────────────────────────────────────────────────┘   │   │  │
│  │  └────────────────────────────────────────────────────────────────────────────┘   │  │
│  │                                                                                      │  │
│  │  ┌───────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │                      DATABASE CONNECTION (MySQL)                             │  │  │
│  │  │                                                                               │  │  │
│  │  │  ┌──────────────────────────────────────────────────────────────────────┐   │  │  │
│  │  │  │                         ibalong_ai (ML Database)                      │   │  │  │
│  │  │  │                                                                       │   │  │  │
│  │  │  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐   │   │  │  │
│  │  │  │  │ predictions     │  │ festivals       │  │    metrics      │   │   │  │  │
│  │  │  │  │                  │  │                  │  │                  │   │   │  │  │
│  │  │  │  │ • attendance     │  │ • event_name    │  │ • accuracy      │   │   │  │  │
│  │  │  │  │ • overcrowded    │  │ • expected      │  │ • precision     │   │   │  │  │
│  │  │  │  │ • waste_pred    │  │   _attendance   │  │ • recall        │   │   │  │  │
│  │  │  │  │                 │  │ • venue_capacity│  │ • f1_score      │   │   │  │  │
│  │  │  │  └──────────────────┘  └──────────────────┘  └──────────────────┘   │   │  │  │
│  │  │  └──────────────────────────────────────────────────────────────────────┘   │  │  │
│  │  └───────────────────────────────────────────────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                              EXTERNAL SERVICES                                        │  │
│  │                                                                                      │  │
│  │  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐                   │  │
│  │  │  OpenStreetMap  │  │  OSRM Router    │  │  Nominatim      │                   │  │
│  │  │   (Tiles)       │  │  (Routing)      │  │  (Geocoding)    │                   │  │
│  │  └──────────────────┘  └──────────────────┘  └──────────────────┘                   │  │
│  │                                                                                      │  │
│  │  ┌──────────────────┐  ┌──────────────────┐                                       │  │
│  │  │  TensorFlow.js  │  │  Chart.js       │                                       │  │
│  │  │   (ML Backend)  │  │  (Visualization)│                                       │  │
│  │  └──────────────────┘  └──────────────────┘                                       │  │
│  │                                                                                      │  │
│  └─────────────────────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

```

## Package Relationships

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        PACKAGE DEPENDENCIES                                 │
└─────────────────────────────────────────────────────────────────────────────┘

FRONTEND
    │
    ├──► Calls Backend API (HTTP)
    │
    └──► Uses External Services:
          ├── Leaflet (Maps)
          ├── Chart.js (Visualization)
          └── html2pdf.js (PDF Generation)

BACKEND (PHP)
    │
    ├──► Connects to MySQL Database
    │
    ├──► Uses ML Server Bridge to call ML API
    │
    ├──► Handles File Uploads
    │
    └──► Manages Sessions/Authentication

ML SERVER (Node.js)
    │
    ├──► Uses TensorFlow.js for predictions
    │
    ├──► Connects to MySQL for model metrics
    │
    └──► Provides REST API endpoints

DATABASE
    │
    ├──► capstone_db - Main application data
    │
    └──► ibalong_ai - ML predictions data
```

## Component Summary

| Package | Technology | Purpose |
|---------|------------|---------|
| Frontend | HTML/CSS/JS | User interface |
| Backend API | PHP | Business logic & data handling |
| ML Server | Node.js/TensorFlow.js | AI predictions |
| Database | MySQL | Data storage |
| External Services | Various | Maps, routing, visualization |

## Data Flow

```
User Action
    │
    ▼
Frontend (index.html/admin_dashboard.html)
    │
    ▼ (AJAX/Fetch)
Backend API (api.php)
    │
    ├──► Database Query
    │         │
    │         ▼
    │    MySQL Database
    │         │
    │         ▼
    │    Return Data
    │
    └──► ML Prediction (optional)
              │
              ▼
         ML Server Bridge
              │
              ▼
         ML Server (Node.js)
              │
              ▼ (TensorFlow.js)
         ML Models
              │
              ▼
         Return Prediction
              │
              ▼
         Save to Database
              │
              ▼
         Response to Frontend
              │
              ▼
         Display Results
```

---

*Generated for Legazpi Explorer System - Capstone Project*
