# Guest Package Diagram - Legazpi Explorer

This document shows the package/component structure and dependencies for the **Guest/User Side** of the Legazpi Explorer tourism management system.

---

## Guest Package Overview

```mermaid
graph TD
    subgraph Frontend["🖥️ FRONTEND (index.html)"]
        Home["🏠 Home Page"]
        Events["🎭 Community Events"]
        Destinations["🏛️ Destinations"]
        Experiences["✨ Experiences"]
        Festivals["🎉 Festivals"]
        Shops["🛍️ Shops & Products"]
        Itinerary["📅 Itinerary Planner"]
        Feedback["💬 Feedback"]
        Navigation["🧭 Navigation"]
        Analytics["📊 Analytics"]
    end
    
    subgraph API["📡 PHP BACKEND API"]
        ListAPI["List APIs"]
        WriteAPI["Write APIs"]
        MLAPI["ML Predictions"]
        External["External Services"]
    end
    
    subgraph Database["🗄️ DATABASE"]
        MainDB[(Main Database)]
    end
    
    subgraph ExternalServices["🌐 EXTERNAL SERVICES"]
        OSM["OpenStreetMap"]
        OSRM["OSRM Router"]
        Weather["Open-Meteo"]
        Google["Google Maps"]
    end
    
    Home --> Events
    Home --> Destinations
    Home --> Experiences
    Home --> Festivals
    Home --> Shops
    Home --> Itinerary
    Home --> Feedback
    Home --> Navigation
    Navigation --> Analytics
    
    Events --> API
    Destinations --> API
    Experiences --> API
    Festivals --> API
    Shops --> API
    Itinerary --> API
    Feedback --> API
    Analytics --> API
    
    API --> Database
    API --> MLAPI
    Events --> External
    Itinerary --> External
    Shops --> External
    
    MLAPI --> External
    
    style Frontend fill:#667eea,color:#fff
    style API fill:#ff7a18,color:#fff
    style Database fill:#4caf50,color:#fff
    style ExternalServices fill:#9c27b0,color:#fff
```

---

## Frontend Package Structure

```mermaid
graph TB
    subgraph UI["🎨 USER INTERFACE LAYER"]
        Hero["Hero Section"]
        About["About Section"]
        Navbar["Navigation Bar"]
        Footer["Footer"]
    end
    
    subgraph Components["📱 UI COMPONENTS"]
        Cards["Card Components"]
        Modals["Modal Components"]
        Forms["Form Components"]
        Charts["Chart Components"]
    end
    
    subgraph Features["⚡ FEATURE MODULES"]
        EventsMod["Events Module"]
        DestMod["Destinations Module"]
        ExperMod["Experiences Module"]
        FestMod["Festivals Module"]
        ShopMod["Shops Module"]
        ItinMod["Itinerary Module"]
        FeedMod["Feedback Module"]
    end
    
    subgraph Core["⚙️ CORE SERVICES"]
        API["API Client"]
        Maps["Map Service"]
        Storage["Local Storage"]
        Analytics["Analytics Tracker"]
    end
    
    UI --> Components
    Components --> Features
    Features --> Core
    
    style UI fill:#667eea,color:#fff
    style Components fill:#00bcd4,color:#fff
    style Features fill:#ff9800,color:#fff
    style Core fill:#607d8b,color:#fff
```

---

## Feature 1: Community Events

```mermaid
graph LR
    subgraph EventsFE["🎭 EVENTS FRONTEND"]
        EM[Events Map]
        EC[Event Cards]
        EMod[Event Modal]
        ECharts[Charts Tab]
        ERoute[Route Navigation]
    end
    
    subgraph EventsAPI["📡 EVENTS API"]
        LE[list_events]
        GP[get_predictions]
        GEP[get_event_predictions]
        GEPD[get_event_parade_details]
    end
    
    subgraph ML["🤖 ML PREDICTIONS"]
        MLOver[Overcrowding Model]
        MLWaste[Waste Prediction]
        MLRoute[Route Optimization]
    end
    
    EM --> LE
    EC --> LE
    EMod --> GEP
    EMod --> GEPD
    ECharts --> GP
    ERoute --> MLRoute
    
    LE --> EventsAPI
    GP --> EventsAPI
    GEP --> EventsAPI
    GEPD --> EventsAPI
    
    EventsAPI --> MLOver
    EventsAPI --> MLWaste
    EventsAPI --> MLRoute
    
    style EventsFE fill:#ff7a18,color:#fff
    style EventsAPI fill:#667eea,color:#fff
    style ML fill:#4caf50,color:#fff
```

---

## Feature 2: Destinations

```mermaid
graph LR
    subgraph DestFE["🏛️ DESTINATIONS FRONTEND"]
        DG[3D Arc Gallery]
        DC[Destination Cards]
        DMod[Destination Modal]
        DFilter[Category Filter]
    end
    
    subgraph DestAPI["📡 DESTINATIONS API"]
        LDest[list_destinations]
        LDCat[list_destination_categories]
    end
    
    DG --> LDest
    DC --> LDest
    DMod --> LDest
    DFilter --> LDCat
    DFilter --> LDest
    
    LDest --> DestAPI
    LDCat --> DestAPI
    
    style DestFE fill:#e91e63,color:#fff
    style DestAPI fill:#667eea,color:#fff
```

---

## Feature 3: Experiences

```mermaid
graph LR
    subgraph ExperFE["✨ EXPERIENCES FRONTEND"]
        ExC[Experience Cards]
        ExMod[Experience Modal]
    end
    
    subgraph ExperAPI["📡 EXPERIENCES API"]
        LExp[list_experiences]
    end
    
    ExC --> LExp
    ExMod --> LExp
    
    LExp --> ExperAPI
    
    style ExperFE fill:#9c27b0,color:#fff
    style ExperAPI fill:#667eea,color:#fff
```

---

## Feature 4: Festivals

```mermaid
graph LR
    subgraph FestFE["🎉 FESTIVALS FRONTEND"]
        FC[Festival Cards]
        FMod[Festival Modal]
        FVideo[Video Player]
    end
    
    subgraph FestAPI["📡 FESTIVALS API"]
        LFest[list_festivals]
    end
    
    FC --> LFest
    FMod --> LFest
    FVideo --> LFest
    
    LFest --> FestAPI
    
    style FestFE fill:#ff5722,color:#fff
    style FestAPI fill:#667eea,color:#fff
```

---

## Feature 5: Shops & Products

```mermaid
graph LR
    subgraph ShopFE["🛍️ SHOPS FRONTEND"]
        SC[Shop Grid]
        SMod[Shop Modal]
        PC[Products Display]
        PFilter[Category Filter]
        PMod[Product Modal]
    end
    
    subgraph ShopAPI["📡 SHOPS API"]
        LShop[list_shops]
        LProd[list_products]
        LCat[list_categories]
    end
    
    SC --> LShop
    SMod --> LShop
    PC --> LProd
    PFilter --> LCat
    PFilter --> LProd
    PMod --> LProd
    
    LShop --> ShopAPI
    LProd --> ShopAPI
    LCat --> ShopAPI
    
    style ShopFE fill:#00bcd4,color:#fff
    style ShopAPI fill:#667eea,color:#fff
```

---

## Feature 6: Itinerary Planner

```mermaid
graph TB
    subgraph ItinFE["📅 ITINERARY FRONTEND"]
        IDest[Destination Selection]
        IDays[Days Selection]
        IForm[Trip Details Form]
        IGen[Generate Itinerary]
        IHotel[Hotel Recommendations]
        ITrans[Transportation]
        IWeather[Weather Forecast]
        IPDF[Save as PDF]
    end
    
    subgraph ItinAPI["📡 ITINERARY API"]
        SI[save_itinerary]
        LI[list_itineraries]
        GI[get_itinerary]
        EI[edit_itinerary]
        DI[delete_itinerary]
        LH[list_itinerary_hotels]
        LD[list_itinerary_destinations]
    end
    
    subgraph External["🌐 EXTERNAL"]
        WeatherAPI[Open-Meteo]
        GoogleMaps[Google Maps]
        OSRM[OSRM Routing]
    end
    
    IDest --> LD
    IDays --> IForm
    IForm --> IGen
    IGen --> LH
    IGen --> LD
    IGen --> WeatherAPI
    IGen --> GoogleMaps
    IGen --> OSRM
    IHotel --> IPDF
    IPDF --> SI
    
    SI --> ItinAPI
    LI --> ItinAPI
    GI --> ItinAPI
    EI --> ItinAPI
    DI --> ItinAPI
    LH --> ItinAPI
    LD --> ItinAPI
    
    style ItinFE fill:#2196f3,color:#fff
    style ItinAPI fill:#667eea,color:#fff
    style External fill:#9c27b0,color:#fff
```

---

## Feature 7: Feedback Submission

```mermaid
graph LR
    subgraph FeedFE["💬 FEEDBACK FRONTEND"]
        FF[Feedback Form]
        FSub[Form Submission]
        FAnon[Anonymous Option]
    end
    
    subgraph FeedAPI["📡 FEEDBACK API"]
        AF[add_feedback]
        LF[list_feedback]
    end
    
    FF --> AF
    FSub --> AF
    FAnon --> AF
    
    AF --> FeedAPI
    LF --> FeedAPI
    
    style FeedFE fill:#4caf50,color:#fff
    style FeedAPI fill:#667eea,color:#fff
```

---

## Guest API Endpoints

```mermaid
graph LR
    subgraph PublicAPI["📡 PUBLIC API (api.php)"]
        READ[READ-ONLY Endpoints]
        WRITE[WRITE Endpoints]
        TRACK[Analytics Endpoints]
    end
    
    READ -->|list_events| E1[Events]
    READ -->|list_destinations| E2[Destinations]
    READ -->|list_experiences| E3[Experiences]
    READ -->|list_festivals| E4[Festivals]
    READ -->|list_shops| E5[Shops]
    READ -->|list_products| E6[Products]
    READ -->|list_feedback| E7[Feedback]
    READ -->|get_predictions| E8[ML Predictions]
    READ -->|get_event_parade_details| E9[Event Routes]
    
    WRITE -->|add_feedback| W1[Submit Feedback]
    WRITE -->|save_itinerary| W2[Save Itinerary]
    WRITE -->|list_itineraries| W3[List Itineraries]
    WRITE -->|get_itinerary| W4[Get Itinerary]
    WRITE -->|edit_itinerary| W5[Edit Itinerary]
    WRITE -->|delete_itinerary| W6[Delete Itinerary]
    
    TRACK -->|log_activity| T1[Log Activity]
    TRACK -->|log_event| T2[Log Event]
    TRACK -->|track_anonymous| T3[Track Session]
    TRACK -->|track_place| T4[Track Places]
    TRACK -->|track_shop| T5[Track Shops]
    TRACK -->|get_event_alerts| T6[Get Alerts]
    
    style READ fill:#4caf50,color:#fff
    style WRITE fill:#ff9800,color:#fff
    style TRACK fill:#9c27b0,color:#fff
    style PublicAPI fill:#667eea,color:#fff
```

---

## Database Tables (Guest Access)

```mermaid
erDiagram
    USERS ||--o{ ITINERARIES : "creates"
    USERS {
        int id PK
        string email
        string name
        string password
        bool is_admin
        timestamp created_at
    }
    
    EVENTS {
        int id PK
        string title
        text description
        string image
        datetime datetime
        string location
        int capacity
        string author
        bool anonymous
        float start_lat
        float start_lng
        float end_lat
        float end_lng
    }
    
    DESTINATIONS ||--o{ ITINERARY_DESTINATIONS : "manages"
    DESTINATIONS {
        int id PK
        string name
        text description
        string location
        string image
        int category_id
    }
    
    LOCAL_EXPERIENCES {
        int id PK
        string title
        text description
        string type
        decimal price
        string duration
        string image
    }
    
    FESTIVALS_EVENTS {
        int id PK
        string name
        text description
        date date_start
        date date_end
        string location
        string image
    }
    
    SHOPS ||--o{ PRODUCTS : "has"
    SHOPS {
        int id PK
        string name
        text description
        string address
        string contact
        string owner_name
        string image
    }
    
    PRODUCTS {
        int id PK
        int shop_id FK
        int category_id FK
        string name
        text description
        decimal price
        string image
    }
    
    ITINERARIES {
        int id PK
        string user_email
        string user_name
        bool anonymous
        string title
        int days
        text destinations
        timestamp created_at
    }
    
    FEEDBACK {
        int id PK
        string user_email
        string user_name
        bool anonymous
        text message
        int rating
        string image
        timestamp created_at
    }
    
    ITINERARY_DESTINATIONS {
        int id PK
        string name
        string category
        float latitude
        float longitude
        text description
        text activities
        string image
    }
    
    ITINERARY_HOTELS {
        int id PK
        string name
        string category
        float latitude
        float longitude
        float rating
        int rate_per_night
        string phone
        string address
        text description
        text features
        string image
    }
```

---

## Guest Data Flow

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    participant ML
    participant External
    
    User->>Frontend: Opens homepage
    Frontend->>API: GET list_events
    API->>Database: SELECT events
    Database-->>API: events data
    API-->>Frontend: events JSON
    Frontend->>ML: GET predictions
    ML-->>Frontend: prediction data
    
    par Parallel Loading
        Frontend->>API: GET list_destinations
        Frontend->>API: GET list_experiences
        Frontend->>API: GET list_festivals
        Frontend->>API: GET list_shops
    end
    
    Note over Frontend: User browses content<br/>and views details
    
    User->>Frontend: Plans itinerary
    Frontend->>API: GET list_itinerary_hotels
    Frontend->>External: GET weather forecast
    Frontend->>External: GET route suggestions
    
    User->>Frontend: Submits feedback
    Frontend->>API: POST add_feedback
    API->>Database: INSERT feedback
    
    User->>Frontend: Saves itinerary
    Frontend->>API: POST save_itinerary
    API->>Database: INSERT itinerary
    
    User->>Frontend: Downloads PDF
    Frontend->>Frontend: Generate PDF (html2pdf)
    Frontend-->>User: Download PDF
    
    Note over Frontend: Analytics tracked silently
    
    par Background Analytics
        Frontend->>API: POST log_event
        Frontend->>API: POST track_place
        Frontend->>API: POST track_shop
    end
    
    style User fill:#4caf50,color:#fff
    style Frontend fill:#667eea,color:#fff
    style API fill:#ff7a18,color:#fff
    style Database fill:#4caf50,color:#fff
    style ML fill:#9c27b0,color:#fff
    style External fill:#00bcd4,color:#fff
```

---

## File Structure (Guest Side)

```
frontend/
├── index.html                    # Main guest-facing page
├── script.js                     # Guest JavaScript logic
├── style.css                     # Styling
├── .htaccess                     # URL routing
│
├── legazpi_boundary.geojson     # City boundary data
│
├── assets/                      # Static assets
│   ├── logo.png
│   ├── hero.png
│   └── ...
│
├── img/                         # Images
│   └── uploads/                 # Mirrored uploads
│
backend/
├── api.php                      # Main API (guest + admin)
├── submit_feedback.php          # Feedback handler
├── event_predictions_api.php    # Event predictions
├── predictions_api.php          # Legacy predictions
├── db.php                       # Database connection
│
├── upload_feedback_image.php   # Feedback image upload
│
backend/ml/
├── server.js                    # ML prediction server
├── train.js                    # Model training
├── package.json                 # Node dependencies
│
docs/
├── GUEST_PACKAGE_DIAGRAM.md    # This document
├── GUEST_SEQUENCE_DIAGRAM.md   # Guest sequence diagrams
├── ADMIN_PACKAGE_DIAGRAM.md    # Admin package diagram
└── PACKAGE_DIAGRAM.md          # Complete system diagram
```

---

## Guest Session & Analytics Flow

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant Analytics
    
    User->>Frontend: First visit
    Frontend->>Frontend: Generate session ID
    Frontend->>Frontend: Set cookie (anon_sid)
    
    par Tracking
        Frontend->>Analytics: POST log_event (page_view)
        Analytics->>Analytics: Store in anonymous_sessions
    end
    
    Note over Frontend: User browses pages
    
    par Continuous Tracking
        Frontend->>Analytics: Click events
        Frontend->>Analytics: Place views
        Frontend->>Analytics: Shop interactions
    end
    
    par Heartbeat
        Frontend->>Analytics: Every 30 seconds
        Analytics->>Analytics: Update session duration
    end
    
    Note over Frontend: User leaves
    
    par Exit Tracking
        Frontend->>Analytics: Page unload event
        Analytics->>Analytics: Log final duration
    end
    
    style User fill:#4caf50,color:#fff
    style Frontend fill:#667eea,color:#fff
    style Analytics fill:#9c27b0,color:#fff
```

---

## Guest Feature Comparison

| Feature | Description | Data Source | Authentication |
|---------|-------------|-------------|----------------|
| Community Events | View events with ML predictions | API (list_events, get_predictions) | None |
| Destinations | Browse tourist spots | API (list_destinations) | None |
| Experiences | Browse local activities | API (list_experiences) | None |
| Festivals | View festival events | API (list_festivals) | None |
| Shops & Products | Browse shops and products | API (list_shops, list_products) | None |
| Itinerary Planner | Plan trips with hotels & weather | API + External APIs | Optional |
| Feedback | Submit feedback | API (add_feedback) | Optional |
| Analytics | Anonymous session tracking | API (log_event) | None |

---

## External Services Used by Guests

```mermaid
graph LR
    subgraph Maps["🗺️ MAP SERVICES"]
        OSM[OpenStreetMap]
        Google[Google Maps]
        OSRM[OSRM Router]
    end
    
    subgraph Weather["🌤️ WEATHER"]
        OpenMeteo[Open-Meteo API]
    end
    
    subgraph ML["🤖 ML SERVICES"]
        TensorFlow[TensorFlow.js]
        LocalML[Local ML Server]
    end
    
    subgraph Verification["🔐 VERIFICATION"]
        GoogleAuth[Google Sign-In]
    end
    
    Itinerary --> Maps
    Events --> Maps
    Events --> ML
    Events --> OSRM
    Itinerary --> Weather
    Feedback --> Verification
    
    style Maps fill:#2196f3,color:#fff
    style Weather fill:#00bcd4,color:#fff
    style ML fill:#9c27b0,color:#fff
    style Verification fill:#ff5722,color:#fff
```

---

## Error Handling for Guests

```mermaid
flowchart TD
    A[API Call] --> B{Success?}
    
    B -->|Yes| C[Process Response]
    B -->|No| D{Error Type}
    
    D -->|Network Error| E[Show Fallback UI]
    D -->|404 Not Found| F[Show 'Content Not Available']
    D -->|500 Server Error| G[Show 'Try Again Later']
    D -->|JSON Parse Error| H[Show 'Failed to Load']
    
    C --> I[Render Data]
    E --> J[Continue with Cached Data]
    F --> J
    G --> J
    H --> J
    
    style A fill:#667eea,color:#fff
    style I fill:#4caf50,color:#fff
    style J fill:#ff9800,color:#fff
```

---

*Document Version: 1.0*
*Generated: 2026*
*Project: Legazpi Explorer - Capstone Project*

