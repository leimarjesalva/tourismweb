# Sequence Diagrams - Legazpi Explorer System

## Overview
This document shows the key sequence diagrams for the Legazpi Explorer tourism management system, illustrating how different components interact during various operations.

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                          SEQUENCE DIAGRAM 1: USER LOGIN (ADMIN)                                │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor User as "Admin User"
participant Frontend as "index.html\n(admin_login.html)"
participant PHP as "PHP Backend\n(admin_login.php)"
participant Session as "Session\nManagement"
participant Database as "MySQL\nDatabase"

User -> Frontend: Opens admin login page
activate Frontend
Frontend -> User: Displays login form
deactivate Frontend

User -> Frontend: Enters credentials\n(username, password)
activate Frontend
Frontend -> PHP: POST /api.php?action=login\n{username, password}
activate PHP

PHP -> Database: SELECT * FROM users\nWHERE email = ?
activate Database
Database -> PHP: Returns user record
deactivate Database

PHP -> PHP: Validates password\n(hash comparison)
alt Credentials Valid
    PHP -> Session: Create session\n$_SESSION['is_admin'] = true
    activate Session
    Session -> PHP: Session created
    deactivate Session
    PHP -> Frontend: {success: true,\nredirect: admin_dashboard.html}
    Frontend -> User: Redirect to\nadmin dashboard
else Credentials Invalid
    PHP -> Frontend: {success: false,\nerror: "Invalid credentials"}
    Frontend -> User: Display error\nmessage
end
deactivate PHP

deactivate Frontend

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                       SEQUENCE DIAGRAM 2: CREATE FESTIVAL EVENT                                │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor Admin as "Admin User"
participant Frontend as "admin_dashboard.html"
participant API as "PHP Backend\n(api.php)"
participant MLBridge as "MLServerBridge.php"
participant MLServer as "ML Server\n(Node.js:3000)"
participant Database as "MySQL\nDatabase"

Admin -> Frontend: Fills event form\n(title, date, capacity, etc.)
activate Frontend

Admin -> Frontend: Clicks "Create Event"
Frontend -> API: POST /api.php?action=create_event\n{title, description, datetime,\nlocation, capacity, event_type}

activate API

API -> Database: INSERT INTO events\n(title, description, datetime,\nlocation, capacity, event_type)
activate Database
Database -> API: Return event_id
deactivate Database

alt ML Server Available
    API -> MLBridge: callMlPrediction(eventData)
    activate MLBridge
    
    MLBridge -> MLServer: POST /predict\n{attendance, venue_capacity,\nweekend, is_free, duration,\nfood_stalls, weather}
    activate MLServer
    
    MLServer -> MLServer: Normalize input\n(load normalization.json)
    MLServer -> MLServer: Run Classification Model\n(TensorFlow.js)
    MLServer -> MLServer: Run Regression Model\n(TensorFlow.js)
    MLServer -> MLBridge: {is_overcrowded: true/false,\novercrowding_probability: 85%,\npredicted_waste_kg: 12500}
    deactivate MLServer
    
    MLBridge -> API: Return prediction\nresult
    deactivate MLBridge
    
    API -> Database: INSERT INTO ml_predictions\n(...prediction data...)
    activate Database
    Database -> API: Confirmation
    deactivate Database
    
else ML Server Unavailable (Fallback)
    API -> API: generateEventPrediction()\n(Heuristic-based)
    API -> Database: INSERT INTO ml_predictions\n(...fallback data...)
    activate Database
    Database -> API: Confirmation
    deactivate Database
end

API -> Frontend: {success: true,\nevent_id: 123, prediction: {...}}
deactivate API

Frontend -> Admin: Show success message\nwith prediction results
deactivate Frontend

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 3: VIEW COMMUNITY EVENTS (PUBLIC)                         │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor User as "Public User"
participant Frontend as "index.html\n(Community Events)"
participant API as "PHP Backend\n(api.php)"
participant Database as "MySQL\nDatabase"
participant MLAPI as "event_predictions_api.php"

User -> Frontend: Opens homepage
activate Frontend

Frontend -> API: GET /api.php?action=list_events
activate API

API -> Database: SELECT * FROM events\nORDER BY created_at DESC
activate Database
Database -> API: Returns events array
deactivate Database

API -> Frontend: {events: [...]}
deactivate API

Frontend -> MLAPI: GET /event_predictions_api.php\n?action=get_predictions
activate MLAPI
MLAPI -> MLAPI: Generate ARIMA-based\npredictions
MLAPI -> Frontend: {predictions_2026: {...}}
deactivate MLAPI

Frontend -> Frontend: Render event cards\nwith ML predictions

loop For each event
    Frontend -> Frontend: Display:\n- Event title & image\n- Date & location\n- Predicted visitors\n- Waste estimate\n- Risk level indicator
end

User -> Frontend: Clicks event card
Frontend -> Frontend: openEventModal_GLOBAL(eventId)

alt Fetch Event Details
    Frontend -> API: GET /api.php?action=list_events
    API -> Frontend: Return events
end

alt Fetch Prediction
    Frontend -> API: GET /api.php?action=get_event_predictions\n&event_id=123
    API -> Database: SELECT * FROM ml_predictions\nWHERE event_id = 123
    activate Database
    Database -> API: Return predictions
    deactivate Database
    API -> Frontend: Return prediction data
end

Frontend -> Frontend: Display event modal\nwith charts & details
deactivate Frontend

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 4: ITINERARY CREATION                                    │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor User as "User (Public)"
participant Frontend as "index.html\n(Itinerary Section)"
participant API as "PHP Backend\n(api.php)"
participant Database as "MySQL\nDatabase"

User -> Frontend: Selects destinations\nfrom dropdown
activate Frontend

User -> Frontend: Selects number of days

User -> Frontend: Clicks "Generate Itinerary"
Frontend -> Frontend: Validates form input

Frontend -> API: GET /api.php?action=list_itinerary_destinations
activate API
API -> Database: SELECT * FROM itinerary_destinations
activate Database
Database -> API: Returns destinations
deactivate Database
API -> Frontend: {destinations: [...]}
deactivate API

Frontend -> API: GET /api.php?action=list_itinerary_hotels
activate API
API -> Database: SELECT * FROM itinerary_hotels
activate Database
Database -> API: Returns hotels
deactivate Database
API -> Frontend: {hotels: [...]}
deactivate API

Frontend -> Frontend: Display:\n- Hotel recommendations\n- Day-by-day activities\n- Transportation options\n- Weather forecast

User -> Frontend: Selects hotel(s)\nvia checkboxes

User -> Frontend: Clicks "Save as PDF"
Frontend -> Frontend: Generate HTML content

Frontend -> Frontend: Create PDF using\nhtml2pdf.js library
Frontend -> Frontend: Trigger download

Frontend -> API: POST /api.php?action=save_itinerary\n{title, days, destinations,\nemail, name}
activate API

API -> Database: INSERT INTO itineraries\n(user_email, user_name, title,\ndays, destinations)
activate Database
Database -> API: Return itinerary_id
deactivate Database

API -> API: Log event for analytics\n(action: itinerary_created)

API -> Frontend: {success: true,\nitinerary_id: 456}
deactivate API

Frontend -> User: Display success\nconfirmation
deactivate Frontend

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 5: SUBMIT FEEDBACK                                         │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor User as "User"
participant Frontend as "index.html\n(Feedback Section)"
participant FeedbackAPI as "submit_feedback.php"
participant Database as "MySQL\nDatabase"

User -> Frontend: Fills feedback form\n(name, email, type, message)
activate Frontend

User -> Frontend: Clicks "Submit"

Frontend -> FeedbackAPI: POST /submit_feedback.php\n{name, email, feedback_type,\nmessage, timestamp}

activate FeedbackAPI

FeedbackAPI -> Database: INSERT INTO feedback\n(user_name, user_email, feedback_type,\nmessage, rating)
activate Database

Database -> FeedbackAPI: Return feedback_id
deactivate Database

alt Image Uploaded
    User -> Frontend: Selects image file
    Frontend -> FeedbackAPI: POST /upload_feedback_image.php\n(Multipart form)
    FeedbackAPI -> FeedbackAPI: Validate image\n(max 5MB, jpg/png)
    FeedbackAPI -> FeedbackAPI: Generate unique filename\n(md5 + timestamp)
    FeedbackAPI -> FeedbackAPI: Move to uploads/feedback/
    FeedbackAPI -> Database: UPDATE feedback\nSET image = ? WHERE id = ?
    activate Database
    Database -> FeedbackAPI: Confirmation
    deactivate Database
end

FeedbackAPI -> Frontend: {success: true,\nmessage: "Feedback submitted!"}
deactivate FeedbackAPI

Frontend -> User: Display success message\n"Thank you for your feedback!"
deactivate Frontend

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 6: ML PREDICTION FLOW                                      │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor System as "System\n(Automated)"
participant API as "PHP Backend\n(api.php)"
participant MLBridge as "MLServerBridge.php"
participant MLServer as "ML Server\n(Node.js)"
participant TensorFlow as "TensorFlow.js"
participant Database as "MySQL\n(ibalong_ai)"

System -> API: Trigger prediction\n(action: predict_events)
activate API

API -> Database: SELECT upcoming events\nwithout recent predictions
activate Database
Database -> API: Return events array
deactivate Database

loop For each event
    API -> API: predictVisitors()\n(Heuristic-based)\n- Event type detection\n- Keyword boosting\n- Historical data lookup
    
    alt Use External ML Model
        API -> MLBridge: POST /predict\n{attendance, venue_capacity,\nweekend, is_free, duration,\nfood_stalls, weather}
        activate MLBridge
        
        MLBridge -> MLServer: HTTP POST /predict\n(JSON payload)
        activate MLServer
        
        MLServer -> TensorFlow: Load models\n(best-overcrowding-model,\nbest-waste-model)
        activate TensorFlow
        TensorFlow -> MLServer: Models loaded
        deactivate TensorFlow
        
        MLServer -> TensorFlow: normalizeInput(input)\n(load normalization.json)
        activate TensorFlow
        TensorFlow -> MLServer: Normalized features
        deactivate TensorFlow
        
        MLServer -> TensorFlow: classModel.predict()\n(Overcrowding Classification)
        activate TensorFlow
        TensorFlow -> MLServer: overcrowding_prob: 0.85
        deactivate TensorFlow
        
        MLServer -> TensorFlow: regModel.predict()\n(Waste Regression)
        activate TensorFlow
        TensorFlow -> MLServer: waste_prediction: 12500 kg
        deactivate TensorFlow
        
        MLServer -> MLBridge: {is_overcrowded: true,\novercrowding_probability: "85%",\npredicted_waste_kg: 12500}
        deactivate MLServer
        
        MLBridge -> API: Return ML prediction
        deactivate MLBridge
        
    else Use Fallback (No ML Server)
        API -> API: generateEventPrediction()\n(Heuristic algorithms)
        API -> API: Calculate based on:\n- Capacity ratio\n- Event type factors\n- Weather impact
    end
    
    API -> Database: INSERT INTO event_predictions\n(event_id, event_name,\npredicted_visitors, predicted_garbage_kg,\ncrowd_status, prediction_date)
    activate Database
    Database -> API: Confirmation
    deactivate Database
end

API -> API: Return summary\n{predictions_generated: 5}

deactivate API

System: Scheduled to run daily\n(at midnight via cron)

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 7: ROUTE OPTIMIZATION                                      │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor User as "User"
participant Frontend as "index.html\n(Event Detail Modal)"
participant RouteAPI as "PHP Backend\n(api.php)"
participant OSRM as "External OSRM\n(router.project-osrm.org)"
participant Map as "Leaflet Map\n(Frontend)"

User -> Frontend: Opens event detail\nmodal
activate Frontend

User -> Frontend: Clicks "Route Navigation" tab

Frontend -> RouteAPI: GET /api.php?action=suggest_optimal_route\n&event_id=123&crowd_level=15000
activate RouteAPI

RouteAPI -> RouteAPI: Get event coordinates\n(start_lat, start_lng, end_lat, end_lng)

alt Coordinates Available
    RouteAPI -> OSRM: GET /route/v1/driving/{coords}\n?overview=full&geometries=geojson
    activate OSRM
    OSRM -> RouteAPI: Return route geometry\n(distance, duration, coordinates)
    deactivate OSRM
    
    RouteAPI -> RouteAPI: Process route data\n- Calculate traffic levels\n- Apply crowd factors\n- Sort by score
    
else Coordinates Not Available (Fallback)
    RouteAPI -> RouteAPI: predictOptimalRoutes()\n(Rule-based algorithm)\n- Define route variants\n- Score based on crowd level\n- Return hardcoded coordinates
end

RouteAPI -> Frontend: {success: true,\nroutes: [{name, distance, duration,\ntraffic_level, coordinates}, ...]}
deactivate RouteAPI

Frontend -> Map: Initialize Leaflet map\n(centered on Legazpi)
activate Map

Map -> Map: Add OpenStreetMap tiles\n(https://tile.openstreetmap.org/)

Map -> Map: Draw route polylines\n(color-coded by traffic level)
alt Route 1 (Official)
    Map -> Map: Green line\n(Traffic: Low)
end
alt Route 2 (Alternate)
    Map -> Map: Orange line\n(Traffic: Moderate)
end
alt Route 3 (Bypass)
    Map -> Map: Red line\n(Traffic: Heavy)
end

Map -> Map: Add markers\n(start point, end point,\nlandmarks)

Map -> Frontend: Map rendered with\nall routes
deactivate Map

Frontend -> User: Display route options\nwith details and "Open in Maps" links
deactivate Frontend

User -> User: Selects preferred route

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 8: ADMIN ANALYTICS                                         │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor Admin as "Admin User"
participant Frontend as "admin_dashboard.html"
participant API as "PHP Backend\n(api.php)"
participant Database as "MySQL\nDatabase"

Admin -> Frontend: Opens analytics tab\non admin dashboard
activate Frontend

Frontend -> API: GET /api.php?action=analytics_summary
activate API

par
    API -> Database: SELECT COUNT(*) FROM users
    activate Database
    Database -> API: total_users: 150
    deactivate Database
    
    API -> Database: SELECT COUNT(*) FROM events
    activate Database
    Database -> API: total_events: 45
    deactivate Database
    
    API -> Database: SELECT COUNT(*) FROM itineraries
    activate Database
    Database -> API: total_itineraries: 89
    deactivate Database
    
    API -> Database: SELECT COUNT(*) FROM feedback
    activate Database
    Database -> API: total_feedback: 234
    deactivate Database
    
    API -> Database: SELECT DATE(ts), COUNT(*)\nFROM page_views\nWHERE ts >= NOW() - 30 days\nGROUP BY DATE(ts)
    activate Database
    Database -> API: pv_last_30: [{date, count}, ...]
    deactivate Database
    
    API -> Database: SELECT url, COUNT(*) as c\nFROM page_views\nGROUP BY url\nORDER BY c DESC LIMIT 15
    activate Database
    Database -> API: top_pages: [{url, count}, ...]
    deactivate Database
    
    API -> Database: SELECT HOUR(ts), COUNT(*)\nFROM page_views\nGROUP BY HOUR(ts)
    activate Database
    Database -> API: peak_hours: [{hour, hits}, ...]
    deactivate Database
    
    API -> Database: SELECT city, COUNT(*)\nFROM anonymous_sessions\nGROUP BY city
    activate Database
    Database -> API: city_stats: [{city, visitors}, ...]
    deactivate Database
end

API -> Frontend: {total_users, total_events,\ntotal_itineraries, total_feedback,\npv_last_30, top_pages, peak_hours,\ncity_stats}
deactivate API

Frontend -> Frontend: Render analytics\ndashboards\n- Summary cards\n- Line chart (page views)\n- Bar chart (top pages)\n- Pie chart (peak hours)\n- Table (city stats)

deactivate Frontend

Admin -> Frontend: Clicks "Export Report"
Frontend -> Frontend: Generate CSV/PDF\nreport
Frontend -> Admin: Download file\ninitiated

```

```

┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                    SEQUENCE DIAGRAM 9: SHOP & PRODUCT MANAGEMENT                                │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘

actor Admin as "Admin User"
participant Frontend as "admin_dashboard.html\n(Shop Management)"
participant API as "PHP Backend\n(api.php)"
participant Upload as "upload_shop_image.php"
participant Database as "MySQL\nDatabase"

Admin -> Frontend: Navigates to Shop tab
activate Frontend

Frontend -> API: GET /api.php?action=list_shops
activate API

API -> Database: SELECT * FROM shops\nORDER BY created_at DESC
activate Database
Database -> API: Returns shops array
deactivate Database

API -> Frontend: {shops: [...]}
deactivate API

Frontend -> Frontend: Display shops grid

Admin -> Frontend: Clicks "Add New Shop"
Frontend -> Admin: Show shop form

Admin -> Frontend: Fills shop details\n& uploads image

Admin -> Frontend: Clicks "Save Shop"
Frontend -> Upload: POST /upload_shop_image.php\n(image file)
activate Upload

Upload -> Upload: Validate image\n(type, size)
Upload -> Upload: Generate filename\n(uniqid + extension)
Upload -> Upload: Move to uploads/shops/
Upload -> Frontend: {success: true,\nfilename: "shop_abc123.jpg"}
deactivate Upload

Frontend -> API: POST /api.php?action=create_shop\n{name, description, address,\ncontact, owner_name, image}
activate API

API -> Database: INSERT INTO shops\n(name, description, address,\ncontact, owner_name, image)
activate Database
Database -> API: Return shop_id
deactivate Database

API -> Frontend: {success: true,\nid: 123}
deactivate API

Frontend -> Admin: Show success message\n& refresh shop list

Admin -> Frontend: Selects shop\nto add products

Admin -> Frontend: Creates product\ncategory first

Frontend -> API: POST /api.php?action=create_category\n{shop_id, name, description}
activate API
API -> Database: INSERT INTO product_categories
API -> Frontend: {success: true, id: 5}
deactivate API

Admin -> Frontend: Adds products to category

Frontend -> API: POST /api.php?action=create_product\n{shop_id, category_id, name,\ndescription, price, image}
activate API
API -> Database: INSERT INTO products
API -> Frontend: {success: true}
deactivate API

Frontend -> Admin: Display updated\nproduct list

deactivate Frontend

```

---

## Sequence Diagram Summary

| Diagram | Description | Key Components |
|---------|-------------|------------------|
| 1 | Admin Login | User → Frontend → PHP → Database → Session |
| 2 | Create Event | Admin → Frontend → API → ML Server → Database |
| 3 | View Events (Public) | User → Frontend → API → Database → ML API |
| 4 | Create Itinerary | User → Frontend → API → Database → PDF Generation |
| 5 | Submit Feedback | User → Frontend → Feedback API → Database |
| 6 | ML Prediction Flow | System → API → ML Bridge → TensorFlow → Database |
| 7 | Route Optimization | User → Frontend → Route API → OSRM → Map |
| 8 | Admin Analytics | Admin → Frontend → API → Database (Multiple Queries) |
| 9 | Shop Management | Admin → Frontend → API → Upload Handler → Database |

---

*Generated for Legazpi Explorer System - Capstone Project*
