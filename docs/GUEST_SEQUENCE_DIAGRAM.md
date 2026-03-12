# Guest Side Sequence Diagram - Legazpi Explorer

This document contains the Mermaid diagram for ALL guest/user side features of the Legazpi Explorer tourism management system.

---

## Complete Guest Features Overview

```mermaid
flowchart TB
    subgraph Home["🏠 HOME PAGE"]
        H1[Hero Section]
        H2[About Section]
    end
    
    subgraph Events["🎭 COMMUNITY EVENTS"]
        E1[Events Map]
        E2[Event Cards]
        E3[ML Predictions]
        E4[Event Modal]
        E5[Charts Tab]
        E6[Route Navigation]
    end
    
    subgraph Dest["🏛️ DESTINATIONS"]
        D1[3D Arc Gallery]
        D2[Destination Cards]
        D3[Destination Modal]
    end
    
    subgraph Exper["✨ EXPERIENCES"]
        X1[Experience Cards]
        X2[Experience Modal]
    end
    
    subgraph Fest["🎉 FESTIVALS"]
        F1[Festival Cards]
        F2[Festival Modal<br/>with Video]
    end
    
    subgraph Shop["🛍️ SHOPS"]
        S1[Shop Grid]
        S2[Shop Modal]
        S3[Products Display]
    end
    
    subgraph Itin["📅 ITINERARY PLANNER"]
        I1[Destination Selection]
        I2[Days Selection]
        I3[Trip Details Form]
        I4[Generate Itinerary]
        I5[Hotel Recommendations]
        I6[Transportation Options]
        I7[Weather Forecast]
        I8[Save as PDF]
    end
    
    subgraph Feed["💬 FEEDBACK"]
        FB1[Feedback Form]
        FB2[Form Submission]
    end
    
    subgraph Nav["🧭 NAVIGATION"]
        N1[Navbar]
        N2[Smooth Scroll]
    end
    
    subgraph Track["📊 ANALYTICS"]
        T1[Event Tracking]
        T2[Session Logging]
    end
    
    Home --> Events
    Home --> Dest
    Home --> Exper
    Home --> Fest
    Home --> Shop
    Home --> Itin
    Home --> Feed
    Home --> Nav
    
    Events --> E3
    Events --> E4
    E4 --> E5
    E4 --> E6
    
    Dest --> D1
    D1 --> D2
    D2 --> D3
    
    Itin --> I1
    I1 --> I2
    I2 --> I3
    I3 --> I4
    I4 --> I5
    I4 --> I6
    I4 --> I7
    I5 --> I8
    
    Feed --> FB1
    FB1 --> FB2
    
    Nav --> T1
    Itin --> T1
    Events --> T1
    Dest --> T1
```

---

## Feature 1: Community Events with ML Predictions

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    participant MLAPI
    
    User->>Frontend: Opens homepage
    Frontend->>API: GET list_events
    API->>Database: SELECT events
    Database-->>API: events[]
    API-->>Frontend: events data
    Frontend->>MLAPI: GET get_predictions
    MLAPI-->>Frontend: predictions_2026
    Frontend->>Frontend: Render event cards
    Note over Frontend: Cards show:<br/>- Expected Visitors<br/>- Predicted Waste<br/>- Risk Level Badge
    
    User->>Frontend: Clicks event card
    Frontend->>API: GET get_event_predictions&id=X
    API->>Database: SELECT predictions
    Database-->>API: prediction data
    API-->>Frontend: prediction{}
    Frontend->>Frontend: Open modal with charts
    
    par Charts
        Frontend->>Frontend: Hourly visitors line chart
        Frontend->>Frontend: Phase comparison chart
        Frontend->>Frontend: Waste breakdown chart
    end
    
    User->>Frontend: Clicks Route tab
    Frontend->>API: GET get_event_parade_details&id=X
    API-->>Frontend: routes[]
    Frontend->>Frontend: Draw routes on map
```

---

## Feature 2: Destinations Gallery

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    
    User->>Frontend: Scrolls to Destinations
    Frontend->>API: GET list_destinations
    API->>Database: SELECT destinations
    Database-->>API: destinations[]
    API-->>Frontend: destinations data
    Frontend->>Frontend: Render 3D arc gallery
    
    par Gallery Display
        Frontend->>Frontend: Auto-rotate carousel
        Frontend->>Frontend: Update left panel description
    end
    
    User->>Frontend: Clicks destination
    Frontend->>Frontend: Open cardModal
    Note over Frontend: Display:<br/>- Full image<br/>- Name<br/>- Description
```

---

## Feature 3: Experiences

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    
    User->>Frontend: Scrolls to Experiences section
    Frontend->>API: GET list_experiences
    API->>Database: SELECT experiences
    Database-->>API: experiences[]
    API-->>Frontend: experiences data
    Frontend->>Frontend: Render experience cards grid
    
    User->>Frontend: Clicks experience card
    Frontend->>Frontend: Open cardModal
    Note over Frontend: Display:<br/>- Image<br/>- Title<br/>- Type<br/>- Description
```

---

## Feature 4: Festivals

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    
    User->>Frontend: Scrolls to Festivals section
    Frontend->>API: GET list_festivals
    API->>Database: SELECT festivals
    Database-->>API: festivals[]
    API-->>Frontend: festivals data
    Frontend->>Frontend: Render festival cards
    
    par Video Handling
        Frontend->>Frontend: Check for video
        alt Has Video
            Frontend->>Frontend: Auto-play muted loop
        else Image Only
            Frontend->>Frontend: Display static image
        end
    end
    
    User->>Frontend: Clicks festival card
    Frontend->>Frontend: Open festivalModal
    Note over Frontend: Split view:<br/>- Video/Image left<br/>- Details right
```

---

## Feature 5: Shops & Products

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    
    User->>Frontend: Scrolls to Shop section
    Frontend->>API: GET list_shops
    API->>Database: SELECT shops
    Database-->>API: shops[]
    API-->>Frontend: shops data
    Frontend->>API: GET list_products
    API->>Database: SELECT products
    Database-->>API: products{}
    API-->>Frontend: products data
    Frontend->>Frontend: Render shop cards grid
    
    User->>Frontend: Clicks shop card
    Frontend->>Frontend: Open shopDetailModal
    Frontend->>Frontend: Render products list
    
    par Category Filter
        Frontend->>Frontend: Filter by category
        Frontend->>Frontend: Update products display
    end
    
    User->>Frontend: Clicks product
    Frontend->>Frontend: Open productModal
    Note over Frontend: Display:<br/>- Product details<br/>- Price<br/>- Category
```

---

## Feature 6: Itinerary Planning

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    
    User->>Frontend: Scrolls to Itinerary section
    Frontend->>Frontend: Show destination carousel
    
    User->>Frontend: Selects destination(s)
    Frontend->>Frontend: Open destination modal
    
    User->>Frontend: Selects number of days
    User->>Frontend: Clicks Create Itinerary
    
    Frontend->>Frontend: Show trip form
    User->>Frontend: Fills form<br/>(title, budget, style)
    User->>Frontend: Clicks Generate
    
    par Generate Results
        Frontend->>API: GET list_itinerary_hotels
        API->>Database: SELECT hotels
        Database-->>API: hotels[]
        API-->>Frontend: hotels data
        
        Frontend->>Frontend: Fetch weather
        Frontend->>Frontend: Calculate transport fares
    end
    
    Frontend->>Frontend: Display results<br/>(hotels, activities, transport, weather)
    
    User->>Frontend: Selects accommodation
    User->>Frontend: Clicks Save as PDF
    
    Frontend->>Frontend: Generate PDF<br/>(html2pdf.js)
    Frontend-->>User: Download PDF
    
    Frontend->>API: POST save_itinerary
    API->>Database: INSERT itinerary
    Database-->>API: success
    API-->>Frontend: confirmation
    Frontend-->>User: Success message
```

---

## Feature 7: Feedback Submission

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant FeedbackAPI
    participant Database
    
    User->>Frontend: Scrolls to Feedback section
    Frontend-->>User: Display feedback form
    
    User->>Frontend: Fills form<br/>(name, email, type, message)
    User->>Frontend: Clicks Submit
    
    Frontend->>FeedbackAPI: POST submit_feedback
    activate FeedbackAPI
    
    alt With Image
        Frontend->>Frontend: Upload image
        Frontend->>FeedbackAPI: POST upload_feedback_image
    end
    
    FeedbackAPI->>Database: INSERT feedback
    activate Database
    Database-->>FeedbackAPI: feedback_id
    deactivate Database
    
    FeedbackAPI-->>Frontend: {success: true}
    deactivate FeedbackAPI
    
    Frontend-->>User: "Thank you!" message
```

---

## Feature 8: Navigation & Analytics

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant API
    participant Database
    
    User->>Frontend: Clicks nav link
    Frontend->>Frontend: Smooth scroll to section
    
    par Tracking
        Frontend->>API: POST log_event
        API->>Database: INSERT analytics
        Database-->>API: confirmation
        API-->>Frontend: logged
    end
    
    Note over Frontend: Events tracked:<br/>- page_view<br/>- destination_viewed<br/>- itinerary_generated<br/>- accommodation_selected<br/>- feedback_submitted
    
    par Session
        Frontend->>Frontend: Initialize session ID
        Frontend->>Frontend: Track heartbeat every 30s
        Frontend->>Frontend: Log page_unload on exit
    end
```

---

## Feature 9: Geolocation & Weather

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant WeatherAPI as "Open-Meteo"
    
    User->>Frontend: Page loads
    Frontend->>Frontend: Check geolocation support
    
    alt Geolocation Available
        Frontend->>User: Request location permission
        alt Permission Granted
            Frontend->>Frontend: Get user coordinates
            Frontend->>Frontend: Save to userLocation
        else Denied
            Frontend->>Frontend: Use Legazpi default
        end
    else Not Supported
        Frontend->>Frontend: Use Legazpi default
    end
    
    par Weather Fetch
        Frontend->>WeatherAPI: GET forecast
        WeatherAPI-->>Frontend: weather data
        Frontend->>Frontend: Display forecast in itinerary
    end
    
    Note over Frontend: Used for:<br/>- Distance calculations<br/>- Transport fare estimates<br/>- Hotel recommendations
```

---

## API Endpoints Used by Guests

```mermaid
graph LR
    subgraph Frontend
        F[index.html]
    end
    
    subgraph Backend
        A[api.php]
        Fb[submit_feedback.php]
        M[event_predictions_api.php]
    end
    
    subgraph Database
        D[(MySQL)]
    end
    
    F -->|GET /list_events| A
    F -->|GET /list_destinations| A
    F -->|GET /get_predictions| M
    F -->|POST /save_itinerary| A
    F -->|POST /submit_feedback| Fb
    
    A -->|SELECT| D
    M --> D
    Fb --> D
    
    style F fill:#ff7a18,color:#fff
    style A fill:#667eea,color:#fff
    style M fill:#667eea,color:#fff
    style D fill:#4caf50,color:#fff
```

---

## Error Handling Flow

```mermaid
flowchart TD
    A[API Call] --> B{Success?}
    
    B -->|Yes| C[Process Response]
    B -->|No| D{Error Type}
    
    D -->|Network Error| E[Show Fallback UI]
    D -->|404 Not Found| F[Show 'Content Not Found']
    D -->|500 Server Error| G[Show 'Try Again Later']
    D -->|JSON Parse Error| H[Show 'Failed to Load']
    
    C --> I[Render Data]
    E --> J[Continue with Available Data]
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

