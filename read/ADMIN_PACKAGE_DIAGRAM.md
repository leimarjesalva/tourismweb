# Admin Package Diagram - Legazpi Explorer

This document shows the package/component structure and dependencies for the **Admin Side** of the Legazpi Explorer system.

---

## Admin Package Overview

```mermaid
graph TD
    subgraph AdminUI["🖥️ ADMIN DASHBOARD (admin_dashboard.html)"]
        UI[Dashboard UI]
        Forms[Event/Shop/Itinerary Forms]
        Charts[Analytics Charts]
        Maps[Route Picker Maps]
    end
    
    subgraph Auth["🔐 AUTHENTICATION"]
        Login[admin_login.html]
        Session[admin_session.php]
    end
    
    subgraph API["📡 PHP BACKEND"]
        API[api.php]
        Events[Event Management]
        Shops[Shop Management]
        Itin[Itinerary Management]
        ML[ML Predictions]
    end
    
    subgraph Upload["📤 UPLOAD HANDLERS"]
        EventImg[upload_event_image.php]
        ShopImg[upload_shop_image.php]
        ProdImg[upload_product_image.php]
        FestImg[upload_festival_image.php]
        AdminImg[upload_admin_profile.php]
    end
    
    subgraph ML["🤖 ML INTEGRATION"]
        Bridge[MLServerBridge.php]
        Server[ML Server :3000]
        TensorFlow[TensorFlow.js]
    end
    
    subgraph Database["🗄️ DATABASE"]
        DB[(MySQL Database)]
        Tables[Tables: events, shops, products, itineraries, etc.]
    end
    
    UI --> API
    Forms --> API
    API --> DB
    API --> Bridge
    Bridge --> Server
    Server --> TensorFlow
    Upload --> DB
    Login --> Session
    Session --> API
```

---

## Admin Features Package

```mermaid
graph TB
    subgraph Events["🎭 EVENT MANAGEMENT"]
        E1[Create Event]
        E2[Edit Event]
        E3[Delete Event]
        E4[View Analytics]
    end
    
    subgraph Shops["🛍️ SHOP MANAGEMENT"]
        S1[Create Shop]
        S2[Add Products]
        S3[Categories]
        S4[Inventory]
    end
    
    subgraph Itin["📅 ITINERARY SYSTEM"]
        I1[Manage Destinations]
        I2[Manage Hotels]
        I3[Set Coordinates]
    end
    
    subgraph Users["👥 USER MANAGEMENT"]
        U1[View Users]
        U2[Feedback]
    end
    
    subgraph Profile["👤 ADMIN PROFILE"]
        P1[Update Profile]
        P2[Change Password]
    end
    
    Events --> API
    Shops --> API
    Itin --> API
    Users --> API
    Profile --> API
```

---

## API Endpoints (Admin)

```mermaid
graph LR
    subgraph Endpoints
        E[api.php]
    end
    
    E -->|action=create_event| EV[Event CRUD]
    E -->|action=update_event| EV
    E -->|action=delete_event| EV
    E -->|action=list_events| EV
    
    E -->|action=create_shop| SH[Shop CRUD]
    E -->|action=create_product| SH
    E -->|action=list_shops| SH
    E -->|action=list_products| SH
    
    E -->|action=create_itinerary_destination| IT[Itinerary]
    E -->|action=create_itinerary_hotel| IT
    E -->|action=list_itinerary_destinations| IT
    E -->|action=list_itinerary_hotels| IT
    
    E -->|action=analytics_summary| AN[Analytics]
    E -->|action=get_event_predictions| AN
    
    E -->|action=check_session| AU[Auth]
    E -->|action=login| AU
    E -->|action=logout| AU
    
    style E fill:#667eea,color:#fff
    style EV fill:#ff7a18,color:#fff
    style SH fill:#4caf50,color:#fff
    style IT fill:#2196f3,color:#fff
    style AN fill:#9c27b0,color:#fff
    style AU fill:#f44336,color:#fff
```

---

## Database Tables (Admin)

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
    
    EVENTS ||--o{ ML_PREDICTIONS : "has"
    EVENTS {
        int id PK
        string title
        text description
        datetime datetime
        string location
        int capacity
        string event_type
        bool ticketed
        float start_lat
        float start_lng
        float end_lat
        float end_lng
        string image
    }
    
    ML_PREDICTIONS {
        int id PK
        int event_id FK
        int predicted_visitors
        int predicted_waste_kg
        string crowd_status
        timestamp prediction_date
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
    
    ITINERARY_DESTINATIONS {
        int id PK
        string name
        string description
        float lat
        float lng
    }
    
    ITINERARY_HOTELS {
        int id PK
        string name
        string address
        decimal price
        float rating
    }
    
    FEEDBACK {
        int id PK
        string user_name
        string user_email
        string feedback_type
        text message
        int rating
        string image
    }
```

---

## Upload Flow

```mermaid
sequenceDiagram
    participant Admin
    participant Form
    participant Upload
    participant Database
    
    Admin->>Form: Select image file
    Form->>Form: Validate file<br/>(type, size)
    
    alt Event Image
        Form->>Upload: POST upload_event_image.php
    else Shop Image
        Form->>Upload: POST upload_shop_image.php
    else Product Image
        Form->>Upload: POST upload_product_image.php
    else Festival Image
        Form->>Upload: POST upload_festival_image.php
    else Admin Profile
        Form->>Upload: POST upload_admin_profile.php
    end
    
    Upload->>Upload: Generate unique filename<br/>(md5 + timestamp)
    Upload->>Upload: Move to uploads/ folder
    
    Upload->>Database: UPDATE table<br/>SET image = ? WHERE id = ?
    Database-->>Upload: Confirmation
    
    Upload-->>Form: {success: true, filename: "..."}
    Form-->>Admin: Show success message
```

---

## ML Prediction Flow (Admin)

```mermaid
sequenceDiagram
    participant Admin
    participant Form
    participant API
    participant Bridge
    participant MLServer
    participant TensorFlow
    participant Database
    
    Admin->>Form: Fills event details<br/>(capacity, type, date)
    Admin->>Form: Clicks "Create Event"
    
    Form->>API: POST create_event
    API->>Database: INSERT event
    Database-->>API: event_id
    
    alt ML Server Available
        API->>Bridge: POST /predict
        Bridge->>MLServer: POST /predict
        
        MLServer->>TensorFlow: Load models
        TensorFlow-->>MLServer: Models loaded
        
        MLServer->>TensorFlow: Classify overcrowding
        TensorFlow-->>MLServer: probability: 0.85
        
        MLServer->>TensorFlow: Predict waste
        TensorFlow-->>MLServer: waste_kg: 12500
        
        MLServer-->>Bridge: {is_overcrowded, probability, waste_kg}
        Bridge-->>API: Prediction result
        
        API->>Database: INSERT ml_predictions
    else Fallback
        API->>API: Generate heuristic prediction
        API->>Database: INSERT fallback prediction
    end
    
    Database-->>API: Confirmation
    API-->>Form: {success: true, prediction: {...}}
    Form-->>Admin: Show event with prediction
```

---

## Admin Session Flow

```mermaid
sequenceDiagram
    participant Admin
    participant Login
    participant Session
    participant Database
    
    Admin->>Login: Enter credentials
    Login->>Session: POST login
    
    Session->>Database: SELECT user<br/>WHERE email = ?
    Database-->>Session: user record
    
    alt Valid Credentials
        Session->>Session: Create session<br/>$_SESSION['is_admin'] = true
        Session-->>Login: {success: true}
        Login-->>Admin: Redirect to dashboard
    else Invalid
        Session-->>Login: {success: false, error: "..."}
        Login-->>Admin: Show error
    end
    
    Note over Admin,Session: Session stored in PHP & localStorage
```

---

## File Structure

```
backend/
├── api.php                          # Main API (admin + guest)
├── admin_login.php                   # Login page
├── admin_session.php                # Session management
├── db.php                          # Database connection
│
├── upload_event_image.php           # Event image upload
├── upload_shop_image.php            # Shop image upload
├── upload_product_image.php         # Product image upload
├── upload_festival_image.php      # Festival image upload
├── upload_admin_profile.php        # Admin profile upload
├── upload_destination_image.php  # Destination image upload
├── upload_experience_image.php    # Experience image upload
├── upload_feedback_image.php      # Feedback image upload
├── upload_itinerary_image.php     # Itinerary image upload
│
├── MLServerBridge.php              # ML Server bridge
├── ml_api_client.php             # ML API client
├── event_predictions_api.php      # Event predictions
├── predictions_api.php           # Legacy predictions
│
├── submit_feedback.php             # Feedback submission
├── verify_google.php            # Google verification
│
frontend/
├── admin_dashboard.html          # Admin dashboard
├── admin_login.html             # Admin login page
├── script.js                   # Shared JavaScript
└── style.css                  # Styles
```

---

*Document Version: 1.0*
*Generated: 2026*
*Project: Legazpi Explorer - Capstone Project*
