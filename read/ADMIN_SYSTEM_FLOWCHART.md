    # Admin System Flowchart - Legazpi Explorer

    This document provides a comprehensive flowchart of the Legazpi Explorer Admin System, detailing the user flow, data processing, and system interactions.

    ---

    ## 1. System Overview

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                        LEGAZPI EXPLORER ADMIN SYSTEM                        │
    └─────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────────────┐
    │   Admin Login   │────▶│  Admin Dashboard │────▶│  Content Management   │
    │   (auth.php)    │     │   (overview)     │     │  (CRUD Operations)    │
    └─────────────────┘     └─────────────────┘     └─────────────────────────┘
                                    │
                                    ▼
                            ┌─────────────────┐
                            │  Analytics &    │
                            │  Predictions    │
                            │  (ML Models)    │
                            └─────────────────┘
    ```

    ---

    ## 2. Admin Login Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                         ADMIN LOGIN PROCESS                                  │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────┐
        │  Admin User  │
        │ Visits Login │
        └──────┬───────┘
            │
            ▼
        ┌─────────────────────────────────────────┐
        │  admin_login.html                        │
        │  ├── Username Input                      │
        │  ├── Password Input                      │
        │  └── Login Button                        │
        └──────────────────────┬────────────────────┘
                            │
            ┌───────────────┴───────────────┐
            │                               │
            ▼                               ▼
        ┌──────────────┐              ┌──────────────┐
        │ Valid        │              │ Invalid      │
        │ Credentials  │              │ Credentials  │
        └──────┬───────┘              └──────┬───────┘
            │                               │
            ▼                               ▼
        ┌─────────────────────────────────────────┐
        │  POST to backend/admin_session.php     │
        │  action=login                          │
        └──────────────────────┬────────────────────┘
                            │
            ┌───────────────┴───────────────┐
            │                               │
            ▼                               ▼
        ┌──────────────┐              ┌──────────────┐
        │ Login        │              │ Show Error   │
        │ Successful   │              │ Message      │
        └──────┬───────┘              └──────────────┘
            │
            ▼
        ┌─────────────────────────────────────────┐
        │  Store Session in PHP & localStorage   │
        │  └── adminSession = {                   │
        │         logged_in: true,                │
        │         username: "admin"               │
        │      }                                  │
        └──────────────────────┬────────────────────┘
                            │
                            ▼
        ┌─────────────────────────────────────────┐
        │  Redirect to admin_dashboard.html        │
        └─────────────────────────────────────────┘
    ```

    ---

    ## 3. Dashboard Overview Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                       DASHBOARD OVERVIEW FLOW                                │
    └─────────────────────────────────────────────────────────────────────────────┘

                        ┌───────────────────────┐
                        │  admin_dashboard.html  │
                        │  (Loaded Successfully) │
                        └───────────┬───────────┘
                                    │
                                    ▼
                        ┌───────────────────────┐
                        │  checkAdminSession()  │
                        │  (Verify Authentication)│
                        └───────────┬───────────┘
                                    │
                ┌─────────────────┼─────────────────┐
                │                 │                 │
                ▼                 ▼                 ▼
        ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
        │ Session OK   │  │ Session      │  │ Session      │
        │              │  │ Expired     │  │ Invalid      │
        └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
            │                 │                 │
            ▼                 ▼                 ▼
        ┌─────────────────────────────────────────────────────────────┐
        │                    loadAllData()                           │
        │  ┌─────────────────────────────────────────────────────┐   │
        │  │  Parallel API Calls:                                 │   │
        │  │  ├── loadAnalytics()     → analytics_summary        │   │
        │  │  ├── loadEvents()       → list_events              │   │
        │  │  ├── loadDestinations()→ list_destinations         │   │
        │  │  ├── loadExperiences() → list_experiences         │   │
        │  │  ├── loadFestivals()   → list_festivals           │   │
        │  │  ├── loadAlerts()      → get_event_alerts         │   │
        │  │  ├── loadGuestItineraries() → list_itineraries    │   │
        │  │  ├── loadGuestFeedback() → list_feedback          │   │
        │  │  └── loadPredictions() → ML predictions          │   │
        │  └─────────────────────────────────────────────────────┘   │
        └──────────────────────┬────────────────────────────────────┘
                            │
                            ▼
        ┌─────────────────────────────────────────────────────────────┐
        │              RENDER DASHBOARD METRICS                       │
        │  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌──────────┐│
        │  │Active Users│ │Total Events│ │Destinations│ │Feedback  ││
        │  │    156    │ │     24     │ │     18     │ │    45    ││
        │  └────────────┘ └────────────┘ └────────────┘ └──────────┘│
        │  ┌──────────────────────────────────────────────────────┐ │
        │  │ Guest Itineraries Preview                             │ │
        │  │ • 3-Day Mayon Adventure                               │ │
        │  │ • Weekend Getaway                                     │ │
        │  └──────────────────────────────────────────────────────┘ │
        │  ┌──────────────────────────────────────────────────────┐ │
        │  │ Guest Feedback Preview                                │ │
        │  │ • Great experience at Lignon Hill!                   │ │
        │  │ • Loved the local cuisine                             │ │
        │  └──────────────────────────────────────────────────────┘ │
        └─────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 4. Navigation Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                          NAVIGATION FLOW                                     │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌────────────────────────────────────────────────────────────────────┐
        │                     SIDEBAR NAVIGATION                              │
        │  ┌──────────────────────────────────────────────────────────────┐  │
        │  │ 📈 Dashboard (Overview)                                       │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 📅 Community Events                                         │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 🗺️ Destinations                                            │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 🛍️ Shops                                                   │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 🎭 Experiences                                              │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 🎉 Festivals                                                │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 🚨 Alerts                                                  │  │
        │  ├──────────────────────────────────────────────────────────────┤  │
        │  │ 🔮 ML Predictions                                          │  │
        │  └──────────────────────────────────────────────────────────────┘  │
        └────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
        ┌────────────────────────────────────────────────────────────────────┐
        │                      showTab(tabName)                              │
        │  ┌──────────────────────────────────────────────────────────────┐   │
        │  │ 1. Hide all .tab-content elements                          │   │
        │  │ 2. Remove .active class from all .nav-link               │   │
        │  │ 3. Show selected tab (add .active, display:block)          │   │
        │  │ 4. Add .active class to clicked nav-link                  │   │
        │  │ 5. Load tab-specific data                                  │   │
        │  │ 6. Persist selection in localStorage                       │   │
        │  └──────────────────────────────────────────────────────────────┘   │
        └────────────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 5. Event Management Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                       EVENT MANAGEMENT FLOW                                  │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                         EVENTS TAB                                   │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │  EVENT CREATION FORM                                          │ │
        │  │  ├── Step 1: Event Basics                                     │ │
        │  │  │     ├── Title (required)                                   │ │
        │  │  │     ├── Event Type (dropdown)                              │ │
        │  │  │     ├── Location                                           │ │
        │  │  │     └── Capacity                                           │ │
        │  │  ├── Step 2: Duration Type                                   │ │
        │  │  │     ├── Single Day Event                                   │ │
        │  │  │     └── Multi-Day Event                                   │ │
        │  │  ├── Step 3: Date/Time Selection                             │ │
        │  │  │     └── Calendar Date Picker                               │ │
        │  │  └── Step 4: Advanced Options (Optional)                     │ │
        │  │        ├── Event Image/Video Upload                           │ │
        │  │        └── Route Location Picker (Leaflet Map)                │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        └──────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │                    EVENT SUBMISSION PROCESS                         │
        │                                                                     │
        │  1. Form Validation                                                │
        │     ├── Check required fields                                       │
        │     ├── Validate date range                                         │
        │     └── Validate capacity (if provided)                            │
        │                                                                     │
        │  2. File Upload (if image selected)                                 │
        │     └── POST to backend/upload_event_image.php                     │
        │                                                                     │
        │  3. Generate Event Schedule                                         │
        │     ├── For Single Day: Create 1 schedule entry                   │
        │     └── For Multi-Day: Auto-generate entries for each day          │
        │                                                                     │
        │  4. ML Prediction Generation                                       │
        │     └── generateDayPrediction() - Client-side calculation          │
        │                                                                     │
        │  5. API Submission                                                  │
        │     └── POST to backend/api.php?action=create_event               │
        └──────────────────────────────┬─────────────────────────────────────┘
                                    │
            ┌───────────────────────┴───────────────────────┐
            │                                               │
            ▼                                               ▼
        ┌──────────────┐                              ┌──────────────┐
        │   Success    │                              │    Error     │
        └──────┬───────┘                              └──────┬───────┘
            │                                               │
            ▼                                               ▼
        ┌────────────────────────────────────────────┐  ┌──────────────────┐
        │ • Show success snackbar                    │  │ • Show error    │
        │ • Reset form                               │  │   message       │
        │ • Reload events list (loadEvents())        │  │ • Keep form     │
        │ • Auto-generate & save ML predictions     │  │   data          │
        └────────────────────────────────────────────┘  └──────────────────┘
    ```

    ---

    ## 6. Event Actions Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                        EVENT ACTIONS FLOW                                   │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                     EVENTS LIST VIEW                                 │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ [Event Card 1]                                                │ │
        │  │ ┌──────────────────────────────────────────────────────────┐   │ │
        │  │ │ 🏷️ Mayon Festival 2026    👥 Capacity: 10,000          │   │ │
        │  │ │ 📍 Legazpi City Center                                    │   │ │
        │  │ └──────────────────────────────────────────────────────────┘   │ │
        │  │     ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │ │
        │  │     │ ✏️ Edit     │ │ 🗺️ Preview  │ │ 🗑️ Delete   │          │ │
        │  │     │             │ │   Route     │ │             │          │ │
        │  │     └─────────────┘ └─────────────┘ └─────────────┘          │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        └──────────────────────────────────────────────────────────────────────┘
            │
            ├──┬────────────────────────────────────────────────────────
            │ │
            │ ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │                         EDIT EVENT                                   │
        │  1. Fetch event data: GET api.php?action=list_events               │
        │  2. Populate form fields                                            │
        │  3. Set editingEventId                                              │
        │  4. Change submit button to "Update Event"                          │
        │  5. On Submit: POST api.php?action=edit_event                      │
        └──────────────────────────────────────────────────────────────────────┘
            │
            ├──┬────────────────────────────────────────────────────────
            │ │
            │ ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │                      PREVIEW ROUTE                                    │
        │  1. Show modal with Leaflet map                                     │
        │  2. GET api.php?action=get_event_parade_details&event_id=X         │
        │  3. Draw route polylines on map                                     │
        │  4. Show start/end markers                                          │
        └──────────────────────────────────────────────────────────────────────┘
            │
            ├──┬────────────────────────────────────────────────────────
            │ │
            │ ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │                        DELETE EVENT                                   │
        │  1. Show confirmation dialog                                        │
        │  2. If confirmed: POST api.php?action=delete_event                │
        │  3. On success: Reload events list, show success message           │
        └──────────────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 7. Shop Management Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                       SHOP MANAGEMENT FLOW                                 │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                          SHOPS TAB                                   │
        │  ┌─────────────────────┐  ┌──────────────────────────────────────┐  │
        │  │   SHOP FORM        │  │        SHOP LIST                     │  │
        │  │  ┌─────────────┐   │  │  ┌────────────────────────────────┐│  │
        │  │  │ Shop Name   │   │  │  │ 🛍️ Shop A     [Edit][Delete]   ││  │
        │  │  │ Owner Name  │   │  │  │ 🛍️ Shop B     [Edit][Delete]   ││  │
        │  │  │ Address     │   │  │  │ 🛍️ Shop C     [Edit][Delete]   ││  │
        │  │  │ Contact     │   │  │  └────────────────────────────────┘│  │
        │  │  │ Description │   │  │                                      │  │
        │  │  │ Image Upload│   │  │  ┌────────────────────────────────┐│  │
        │  │  └─────────────┘   │  │  │    SHOP ANALYTICS             ││  │
        │  │  [+ Add Shop]      │  │  │    Total Shops: 15             ││  │
        │  └─────────────────────┘  │  │    Total Products: 87          ││  │
        │                           │  │    Top Shops by Visits         ││  │
        │  ┌─────────────────────┐  │  └────────────────────────────────┘│  │
        │  │ PRODUCTS SECTION   │  │                                      │  │
        │  │  ┌─────────────┐   │  │                                      │  │
        │  │  │ Categories  │   │  │                                      │  │
        │  │  │ [+ Add]    │   │  │                                      │  │
        │  │  ├─────────────┤   │  │                                      │  │
        │  │  │ Products    │   │  │                                      │  │
        │  │  │ [+ Add]    │   │  │                                      │  │
        │  │  └─────────────┘   │  │                                      │  │
        │  └─────────────────────┘  │                                      │  │
        └───────────────────────────┴──────────────────────────────────────┘
    ```

    ### Shop Product Management

    ```
        ┌─────────────────────────────────────────────────────────────────────┐
        │                  PRODUCT MANAGEMENT FLOW                             │
        └─────────────────────────────────────────────────────────────────────┘

        1. Click "Manage Products" on shop card
        │
        ├──▼ View Shop Products
        │   ├── Load categories: GET api.php?action=list_categories
        │   ├── Load products:  GET api.php?action=list_products
        │   └── Display in grid
        │
        ├──▼ Add Category
        │   └── POST api.php?action=create_category
        │
        ├──▼ Add Product
        │   ├── Form fields: Name, Category, Price, Stock, Description
        │   ├── Optional image upload
        │   └── POST api.php?action=create_product
        │
        ├──▼ Edit Product
        │   └── POST api.php?action=edit_product
        │
        └──▼ Delete Product
            └── POST api.php?action=delete_product
    ```

    ---

    ## 8. Destination Management Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                    DESTINATION MANAGEMENT FLOW                              │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                      DESTINATIONS TAB                                │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ CATEGORY MANAGEMENT                                             │ │
        │  │ ├── Nature                                                      │ │
        │  │ ├── Historical                                                 │ │
        │  │ ├── Cultural                                                   │ │
        │  │ └── Adventure                                                  │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ ADD NEW DESTINATION FORM                                       │ │
        │  │ ├── Name (required)                                           │ │
        │  │ ├── Category (dropdown)                                        │ │
        │  │ ├── Location                                                   │ │
        │  │ ├── Rating (1-5)                                              │ │
        │  │ ├── Description                                                │ │
        │  │ └── Image Upload                                               │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ DESTINATIONS LIST                                              │ │
        │  │ ├── 🏔️ Mayon Volcano        [Edit] [Delete]                  │ │
        │  │ ├── ⛪ Cagsawa Ruins         [Edit] [Delete]                  │ │
        │  │ └── 🏖️ Embarcadero         [Edit] [Delete]                  │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        └──────────────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 9. Alert Management Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                        ALERT MANAGEMENT FLOW                                 │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                         ALERTS TAB                                   │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ ADD NEW ALERT FORM                                            │ │
        │  │ ├── Event ID (dropdown)                                        │ │
        │  │ ├── Alert Type                                                │ │
        │  │ │    ├── ✅ DO                                                 │ │
        │  │ │    ├── ❌ DO NOT                                             │ │
        │  │ │    ├── 🎒 BRING                                              │ │
        │  │ │    └── 🚫 DO NOT BRING                                       │ │
        │  │ └── Message (textarea)                                         │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ ACTIVE ALERTS LIST                                            │ │
        │  │                                                                │ │
        │  │ ┌──────────────────────────────────────────────────────────┐  │ │
        │  │ │ 🚨 DO | Event: Mayon Festival                            │  │ │
        │  │ │    Bring sunscreen and comfortable shoes                │  │ │
        │  │ │    [Edit] [Delete]                                      │  │ │
        │  │ └──────────────────────────────────────────────────────────┘  │ │
        │  │                                                                │ │
        │  │ ┌──────────────────────────────────────────────────────────┐  │ │
        │  │ │ 🚫 DO NOT | Event: Mayon Festival                       │  │ │
        │  │ │    Do not bring outside food and drinks                │  │ │
        │  │ │    [Edit] [Delete]                                      │  │ │
        │  │ └──────────────────────────────────────────────────────────┘  │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        └──────────────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 10. ML Predictions Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                      ML PREDICTIONS FLOW                                     │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                         PREDICTIONS TAB                                │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ MODEL PERFORMANCE SUMMARY                                      │ │
        │  │ ├── Best Model: ARIMA(1,1,1)                                  │ │
        │  │ ├── Accuracy: 92.75%                                          │ │
        │  │ ├── Error Margin: ±3,550 visitors                             │ │
        │  │ └── Confidence: MEDIUM                                         │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ 2025 HISTORICAL DATA                                          │ │
        │  │ ├── Annual Total: 548,880 visitors                            │ │
        │  │ ├── Peak Season: November - December                          │ │
        │  │ └── Monthly breakdown chart                                   │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ 2026 Q1 FORECAST                                              │ │
        │  │ ├── January: 45,000 visitors                                  │ │
        │  │ ├── February: 42,000 visitors                                 │ │
        │  │ ├── March: 48,000 visitors                                    │ │
        │  │ └── Q1 Total: 135,000 visitors (+5.2% YoY)                  │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ MODEL COMPARISON TABLE                                         │ │
        │  │ ├── ARIMA(1,1,1)        92.75%  MAE: 3,550   RMSE: 4,200   │ │
        │  │ ├── SARIMA(1,1,1)(1,0,1) 91.20%  MAE: 3,800   RMSE: 4,500   │ │
        │  │ ├── Linear Regression    78.50%  MAE: 8,200   RMSE: 9,800    │ │
        │  │ └── Random Forest       85.40%  MAE: 5,600   RMSE: 6,900    │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  [🔄 Regenerate Model] - Triggers model retraining                   │
        └──────────────────────────────────────────────────────────────────────┘

        MODEL PREDICTION PROCESS:
        
        ┌──────────────────────────────────────────────────────────────────────┐
        │                    PREDICTION ALGORITHM                              │
        │                                                                       │
        │  Input Features:                                                     │
        │  ├── Event Type (celebration, festival, expo, etc.)               │
        │  ├── Event Capacity                                                 │
        │  ├── Duration (hours)                                              │
        │  ├── Historical Data (past events)                                 │
        │  └── Seasonal Factors (month, day of week)                         │
        │                                                                       │
        │  Processing:                                                        │
        │  ├── ARIMA Time Series Analysis                                     │
        │  ├── Seasonal Decomposition                                        │
        │  ├── Trend Extrapolation                                          │
        │  └── Confidence Interval Calculation                               │
        │                                                                       │
        │  Output:                                                            │
        │  ├── Expected Visitors (prediction)                                 │
        │  ├── Overcrowding Probability                                      │
        │  ├── Waste Generation Estimate (kg)                                │
        │  ├── Hourly Distribution                                           │
        │  └── Phase Distribution (Arrival/Peak/Leaving)                    │
        └──────────────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 11. Guest Data Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                        GUEST DATA FLOW                                      │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                    GUESTS (Itineraries & Feedback)                   │
        │                                                                       │
        │  These appear in the Dashboard Overview (Metrics Tab):               │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ GUEST ITINERARIES                                              │ │
        │  │                                                                 │ │
        │  │ ├── Load: GET api.php?action=list_itineraries                 │ │
        │  │ │                                                              │ │
        │  │ ├── Display:                                                   │ │
        │  │ │     • Trip Title                                             │ │
        │  │ │     • Number of Days                                         │ │
        │  │ │     • Selected Destinations                                  │ │
        │  │ │     • Created Date                                           │ │
        │  │ │                                                              │ │
        │  │ └── Actions:                                                   │ │
        │  │         • Delete Itinerary                                     │ │
        │  │                                                              │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        │                                                                       │
        │  ┌────────────────────────────────────────────────────────────────┐ │
        │  │ GUEST FEEDBACK                                                │ │
        │  │                                                                 │ │
        │  │ ├── Load: GET api.php?action=list_feedback                   │ │
        │  │ │                                                              │ │
        │  │ ├── Display:                                                   │ │
        │  │ │     • Name (or Anonymous)                                    │ │
        │  │ │     • Email                                                  │ │
        │  │ │     • Feedback Type (Suggestion/Bug/Praise/Other)          │ │
        │  │ │     • Rating (1-5 stars)                                    │ │
        │  │ │     • Message                                                │ │
        │  │ │     • Timestamp                                             │ │
        │  │ │                                                              │ │
        │  │ └── Actions:                                                   │ │
        │  │         • Delete Feedback                                      │ │
        │  │                                                              │ │
        │  └────────────────────────────────────────────────────────────────┘ │
        └──────────────────────────────────────────────────────────────────────┘
    ```

    ---

    ## 12. Admin Logout Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                         LOGOUT FLOW                                          │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                    LOGOUT BUTTON CLICKED                              │
        └──────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │              Confirm Logout Dialog                                   │
        │                                                                       │
        │          ┌───────────────────────────────┐                          │
        │          │  Are you sure you want       │                          │
        │          │       to logout?             │                          │
        │          │                               │                          │
        │          │    [Cancel]  [Logout]        │                          │
        │          └───────────────────────────────┘                          │
        └──────────────────────────────────────────────────────────────────────┘
                                    │
                ┌─────────────────┴─────────────────┐
                │                               │
                ▼                               ▼
        ┌──────────────────┐              ┌──────────────────┐
        │   Cancel        │              │   Confirm        │
        └────────┬────────┘              └────────┬────────┘
                │                               │
                ▼                               ▼
        ┌──────────────────┐              ┌──────────────────────────────────┐
        │  Close Dialog    │              │  1. POST logout to backend       │
        │  Return to      │              │     admin_session.php?action=logout
        │  Dashboard      │              │                                  │
        └──────────────────┘              │  2. Clear localStorage         │
                                        │     └── adminUsername          │
                                        │     └── adminProfileImage     │
                                        │                                  │
                                        │  3. Show "Logging out..."      │
                                        │                                  │
                                        │  4. Redirect to                │
                                        │     admin_login.html           │
                                        │                                  │
                                        └──────────────────────────────────┘
    ```

    ---

    ## 13. API Endpoints Summary

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                         API ENDPOINTS                                        │
    └─────────────────────────────────────────────────────────────────────────────┘

    AUTHENTICATION:
    ├── POST   admin_session.php?action=login
    ├── GET    admin_session.php?action=check_session
    └── GET    admin_session.php?action=logout

    EVENTS:
    ├── GET    api.php?action=list_events
    ├── POST   api.php?action=create_event
    ├── POST   api.php?action=edit_event
    ├── POST   api.php?action=delete_event
    ├── GET    api.php?action=get_event_alerts
    ├── POST   api.php?action=add_event_alert
    └── POST   api.php?action=edit_alert

    DESTINATIONS:
    ├── GET    api.php?action=list_destinations
    ├── POST   api.php?action=create_destination
    ├── POST   api.php?action=edit_destination
    └── POST   api.php?action=delete_destination

    SHOPS:
    ├── GET    api.php?action=list_shops
    ├── POST   api.php?action=create_shop
    ├── POST   api.php?action=edit_shop
    ├── POST   api.php?action=delete_shop
    ├── GET    api.php?action=list_products
    ├── POST   api.php?action=create_product
    ├── POST   api.php?action=edit_product
    ├── POST   api.php?action=delete_product
    ├── GET    api.php?action=list_categories
    ├── POST   api.php?action=create_category
    └── GET    api.php?action=shop_analytics

    EXPERIENCES:
    ├── GET    api.php?action=list_experiences
    ├── POST   api.php?action=create_experience
    ├── POST   api.php?action=edit_experience
    └── POST   api.php?action=delete_experience

    FESTIVALS:
    ├── GET    api.php?action=list_festivals
    ├── POST   api.php?action=create_festival
    ├── POST   api.php?action=edit_festival
    └── POST   api.php?action=delete_festival

    ITINERARIES:
    ├── GET    api.php?action=list_itineraries
    ├── POST   api.php?action=delete_itinerary
    └── GET    api.php?action=get_guest_itineraries

    FEEDBACK:
    ├── GET    api.php?action=list_feedback
    └── POST   api.php?action=delete_feedback

    ANALYTICS:
    ├── GET    api.php?action=analytics_summary
    ├── GET    api.php?action=most_viewed_places
    └── GET    api.php?action=most_clicked_shops

    ML PREDICTIONS:
    ├── GET    event_predictions_api.php?action=get_predictions
    └── GET    api.php?action=save_event_prediction

    FILE UPLOADS:
    ├── POST   upload_event_image.php
    ├── POST   upload_shop_image.php
    ├── POST   upload_product_image.php
    ├── POST   upload_destination_image.php
    ├── POST   upload_experience_image.php
    ├── POST   upload_festival_image.php
    ├── POST   upload_admin_profile.php
    └── POST   upload_feedback_image.php
    ```

    ---

    ## 14. Error Handling Flow

    ```
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                       ERROR HANDLING FLOW                                   │
    └─────────────────────────────────────────────────────────────────────────────┘

        ┌──────────────────────────────────────────────────────────────────────┐
        │                    API CALL FAILURE                                  │
        └──────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │                     checkResponse()                                  │
        │                                                                       │
        │  ┌─────────────────────────────────────────────────────────────────┐ │
        │  │  if (!response.ok)                                           │ │
        │  │     ├── HTTP 400: Bad Request → Show validation error        │ │
        │  │     ├── HTTP 401: Unauthorized → Redirect to login           │ │
        │  │     ├── HTTP 403: Forbidden → Show access denied             │ │
        │  │     ├── HTTP 404: Not Found → Show not found error          │ │
        │  │     ├── HTTP 500: Server Error → Show server error          │ │
        │  │     └── Network Error → Show connection error               │ │
        │  └─────────────────────────────────────────────────────────────────┘ │
        └──────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
        ┌──────────────────────────────────────────────────────────────────────┐
        │                    showSnackbar(message, type)                       │
        │                                                                       │
        │  Types:                                                              │
        │  ├── 'success' - Green background                                    │
        │  ├── 'error'   - Red background                                      │
        │  ├── 'warning' - Orange background                                   │
        │  └── 'info'    - Blue background                                    │
        │                                                                       │
        │  Display: 3 seconds auto-dismiss                                     │
        └──────────────────────────────────────────────────────────────────────┘
    ```

    ---

    *Document Version: 1.0*  
    *Generated: 2025*  
    *Project: Legazpi Explorer - Capstone Project*
