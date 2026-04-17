# Legazpi Explorer — UML Diagrams

---

## 1. Use Case Diagram (Guest)

```mermaid
graph TB
    subgraph "Legazpi Explorer System"
        UC1["Browse Destinations"]
        UC2["View Interactive Map"]
        UC3["View Crowd Level Predictions"]
        UC4["Get Directions via GPS"]
        UC5["View Destination Alerts"]
        UC6["Browse Local Experiences"]
        UC7["Browse Festivals & Events"]
        UC8["Browse Novelty Shops"]
        UC9["View Shop Products"]
        UC10["Submit Shop/Product Rating"]
        UC11["Plan Itinerary"]
        UC12["Set Trip Preferences"]
        UC13["Select Destinations & Hotels"]
        UC14["Download Itinerary PDF"]
        UC15["Submit System Feedback"]
        UC16["View Weather Forecast"]
        UC18["View Visitor Forecasts"]
        UC19["View Waste Predictions"]
        UC20["Filter Destinations by Category"]
    end

    Guest(("👤 Guest"))

    Guest --> UC1
    Guest --> UC2
    Guest --> UC3
    Guest --> UC4
    Guest --> UC5
    Guest --> UC6
    Guest --> UC7
    Guest --> UC8
    Guest --> UC9
    Guest --> UC10
    Guest --> UC11
    Guest --> UC15
    Guest --> UC16

    UC11 --> UC12
    UC11 --> UC13
    UC11 --> UC14
    UC2 --> UC18
    UC2 --> UC19
    UC1 --> UC20
    UC2 --> UC4

    style Guest fill:#4CAF50,stroke:#333,color:#fff
    style UC1 fill:#e3f2fd,stroke:#1976D2
    style UC2 fill:#e3f2fd,stroke:#1976D2
    style UC3 fill:#e3f2fd,stroke:#1976D2
    style UC4 fill:#e3f2fd,stroke:#1976D2
    style UC5 fill:#e3f2fd,stroke:#1976D2
    style UC6 fill:#e3f2fd,stroke:#1976D2
    style UC7 fill:#e3f2fd,stroke:#1976D2
    style UC8 fill:#e3f2fd,stroke:#1976D2
    style UC9 fill:#e3f2fd,stroke:#1976D2
    style UC10 fill:#e3f2fd,stroke:#1976D2
    style UC11 fill:#e3f2fd,stroke:#1976D2
    style UC12 fill:#fff3e0,stroke:#FF9800
    style UC13 fill:#fff3e0,stroke:#FF9800
    style UC14 fill:#fff3e0,stroke:#FF9800
    style UC15 fill:#e3f2fd,stroke:#1976D2
    style UC16 fill:#e3f2fd,stroke:#1976D2
    style UC18 fill:#fff3e0,stroke:#FF9800
    style UC19 fill:#fff3e0,stroke:#FF9800
    style UC20 fill:#fff3e0,stroke:#FF9800
```

---

## 2. Use Case Diagram (Admin)

```mermaid
graph TB
    subgraph "Legazpi Explorer Admin Panel"
        UC1["Login / Logout"]
        UC2["View Dashboard Metrics"]
        UC3["Manage Destinations (CRUD)"]
        UC4["Manage Shops (CRUD)"]
        UC5["Manage Products (CRUD)"]
        UC6["Manage Product Categories"]
        UC7["Manage Hotels (CRUD)"]
        UC8["Manage Experiences (CRUD)"]
        UC9["Manage Festivals (CRUD)"]
        UC10["Manage Alerts (CRUD)"]
        UC11["View Feedback & Ratings"]
        UC12["Delete Reviews"]
        UC13["View Guest Activity Logs"]
        UC14["View Analytics Charts"]
        UC15["Upload Images / Videos"]
        UC16["View Rating Statistics"]
        UC17["Update Admin Profile"]
        UC18["View Guest Itineraries"]
        UC19["Set Destination Location via Map"]
    end

    Admin(("🔐 Admin"))

    Admin --> UC1
    Admin --> UC2
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    Admin --> UC7
    Admin --> UC8
    Admin --> UC9
    Admin --> UC10
    Admin --> UC11
    Admin --> UC12
    Admin --> UC13
    Admin --> UC17

    UC2 --> UC14
    UC2 --> UC16
    UC2 --> UC18
    UC3 --> UC15
    UC3 --> UC19
    UC4 --> UC5
    UC4 --> UC6
    UC4 --> UC15
    UC7 --> UC15
    UC8 --> UC15
    UC9 --> UC15
    UC11 --> UC12
    UC2 --> UC13

    style Admin fill:#E91E63,stroke:#333,color:#fff
    style UC1 fill:#fce4ec,stroke:#C62828
    style UC2 fill:#fce4ec,stroke:#C62828
    style UC3 fill:#fce4ec,stroke:#C62828
    style UC4 fill:#fce4ec,stroke:#C62828
    style UC5 fill:#fff3e0,stroke:#FF9800
    style UC6 fill:#fff3e0,stroke:#FF9800
    style UC7 fill:#fce4ec,stroke:#C62828
    style UC8 fill:#fce4ec,stroke:#C62828
    style UC9 fill:#fce4ec,stroke:#C62828
    style UC10 fill:#fce4ec,stroke:#C62828
    style UC11 fill:#fce4ec,stroke:#C62828
    style UC12 fill:#fff3e0,stroke:#FF9800
    style UC13 fill:#fff3e0,stroke:#FF9800
    style UC14 fill:#fff3e0,stroke:#FF9800
    style UC15 fill:#fff3e0,stroke:#FF9800
    style UC16 fill:#fff3e0,stroke:#FF9800
    style UC17 fill:#fce4ec,stroke:#C62828
    style UC18 fill:#fff3e0,stroke:#FF9800
    style UC19 fill:#fff3e0,stroke:#FF9800
```

---

# GUEST SEQUENCE DIAGRAMS

---

## 3. Guest — Browse Destinations

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant API as api.php
    participant DB as MySQL (destinations)

    Guest->>UI: Opens Legazpi Explorer website
    UI->>JS: DOMContentLoaded fires
    JS->>API: GET api.php?action=list_destinations
    API->>DB: SELECT * FROM destinations ORDER BY id DESC
    DB-->>API: Destinations array
    API-->>JS: JSON {destinations: [...]}
    JS->>API: GET api.php?action=list_destination_categories
    API->>DB: SELECT * FROM destination_categories
    DB-->>API: Categories array
    API-->>JS: JSON {categories: [...]}
    JS->>UI: Render category filter buttons (All, Nature, Beach, Culture, Food, Adventure)
    JS->>UI: Render destination cards in arc carousel
    Guest->>UI: Clicks a destination card
    JS->>UI: Open modal with image, title, description
```

---

## 4. Guest — Filter Destinations by Category

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js

    Guest->>UI: Clicks category tab (e.g. "Nature")
    UI->>JS: filterDestinationsByCategory(categoryId)
    JS->>JS: Filter destinationsCache by category_id
    JS->>UI: Re-render destination cards matching selected category
    Guest->>UI: Clicks "All Destinations" tab
    UI->>JS: filterDestinationsByCategory(null)
    JS->>JS: Show all destinations from cache
    JS->>UI: Re-render full destination grid
```

---

## 5. Guest — View Interactive Map with Crowd Levels

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js (inline)
    participant API as api.php
    participant ML as event_predictions_api.php
    participant DB as MySQL (events)
    participant Map as Leaflet Map

    Guest->>UI: Scrolls to Destination Map section
    UI->>JS: loadCommunityEvents()
    JS->>API: GET api.php?action=list_events
    API->>DB: SELECT * FROM events
    DB-->>API: Events/destinations with lat, lng, capacity
    API-->>JS: JSON {events: [...]}

    JS->>ML: GET event_predictions_api.php?action=get_predictions
    ML-->>JS: JSON predictions (ARIMA model output)

    JS->>JS: Calculate crowd multiplier (day-of-week × hour-of-day)
    JS->>Map: Initialize Leaflet map centered on Legazpi City
    JS->>Map: Load GeoJSON boundary overlay
    JS->>Map: Place markers with color-coded crowd levels (Green/Yellow/Red)

    Guest->>Map: Clicks a destination marker
    Map->>JS: Marker click event with event data
    JS->>UI: Show tooltip with name, image, visitor count, crowd %, waste estimate
```

---

## 6. Guest — View Visitor Forecasts (Predictions)

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js (inline)
    participant ML as event_predictions_api.php
    participant Chart as Chart.js

    Guest->>UI: Clicks "Visitor Forecasts" tab on map section
    UI->>JS: switchMapTab('predictions')
    JS->>JS: Use cached ML predictions (mlPredictionsCache)
    JS->>JS: Scale predictions per destination capacity
    JS->>Chart: Render hourly visitor prediction bar chart
    JS->>Chart: Render weekly trend line chart
    JS->>UI: Display prediction cards per destination

    Guest->>UI: Clicks specific destination prediction card
    JS->>Chart: Update chart with selected destination data
    JS->>UI: Show predicted peak hours + crowd risk %
```

---

## 7. Guest — View Waste Predictions

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js (inline)
    participant Chart as Chart.js

    Guest->>UI: Scrolls to Waste Prediction section
    JS->>JS: Use cached event data + ML predictions
    JS->>JS: Calculate waste per destination (waste_per_visitor × predicted_visitors)
    JS->>JS: Break down into categories (Biodegradable, Recyclable, Residual, Hazardous)
    JS->>Chart: Render doughnut chart (globalWasteBreakdownChart)
    JS->>Chart: Render bar chart per destination (destinationWasteChart)
    JS->>UI: Display today/weekly toggle for waste view

    Guest->>UI: Toggles between "Today" and "This Week"
    JS->>JS: Recalculate waste totals for selected period
    JS->>Chart: Update waste charts with new data
```

---

## 8. Guest — Get Directions via GPS

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js (inline)
    participant Geo as Browser Geolocation
    participant OSRM as OSRM Router
    participant Map as Leaflet Map

    Guest->>UI: Clicks "Directions" button on destination info card
    UI->>JS: navigateToDestinationWithCurrentLocation()
    JS->>Geo: navigator.geolocation.getCurrentPosition()
    Geo-->>JS: User coordinates (lat, lng)
    JS->>OSRM: GET route (origin: user coords → dest: destination coords)
    OSRM-->>JS: Route geometry (polyline), distance, duration
    JS->>Map: Draw route polyline on map
    JS->>Map: Fit map bounds to show full route
    JS->>UI: Display distance (km) and estimated travel time
    JS->>UI: Show route details card with turn-by-turn info
```

---

## 9. Guest — View Destination Alerts

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js (inline)
    participant API as api.php
    participant DB as MySQL (event_alerts)

    Guest->>Map: Clicks destination marker on map
    Map->>JS: Marker click event
    JS->>API: GET api.php?action=get_event_alerts&event_id=X
    API->>DB: SELECT * FROM event_alerts WHERE event_id = X
    DB-->>API: Alerts array
    API-->>JS: JSON {alerts: [...]}

    JS->>JS: Group alerts by type (DO, DON'T, BRING, DON'T BRING)
    JS->>UI: Display alert badges on info card
    Guest->>UI: Clicks "View Alerts" 
    JS->>UI: Show alerts modal with categorized list
    JS->>UI: Display DO items in green, DON'T in red, BRING in blue, DON'T BRING in orange
```

---

## 10. Guest — Browse Local Experiences

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant API as api.php
    participant DB as MySQL (local_experiences)

    Guest->>UI: Scrolls to "Local Experiences & Activities" section
    Note over UI,JS: Already loaded on DOMContentLoaded
    JS->>API: GET api.php?action=list_experiences
    API->>DB: SELECT * FROM local_experiences ORDER BY id DESC
    DB-->>API: Experiences array
    API-->>JS: JSON {experiences: [...]}
    JS->>UI: Render experience cards (image, title, type, description)

    Guest->>UI: Clicks an experience card
    JS->>UI: openCardModal(title, image, description)
    JS->>UI: Display full-screen modal with experience details
    Guest->>UI: Closes modal
```

---

## 11. Guest — Browse Festivals & Events

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant API as api.php
    participant DB as MySQL (festivals_events)

    Guest->>UI: Scrolls to "Festivals & Events" section
    Note over UI,JS: Already loaded on DOMContentLoaded
    JS->>API: GET api.php?action=list_festivals
    API->>DB: SELECT * FROM festivals_events ORDER BY start_date DESC
    DB-->>API: Festivals array
    API-->>JS: JSON {festivals: [...]}
    JS->>UI: Render festival cards (image/video, name, dates, location, description)

    Guest->>UI: Clicks a festival card
    JS->>UI: openFestivalModal(festival)
    JS->>UI: Show modal with full details + video player (if video exists)
    Guest->>UI: Closes modal
```

---

## 12. Guest — Browse Novelty Shops

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant API as api.php
    participant RAPI as ratings_api.php
    participant DB as MySQL (shops)

    Guest->>UI: Scrolls to "Shop" section
    JS->>API: GET api.php?action=list_shops
    API->>DB: SELECT * FROM shops
    DB-->>API: Shops array
    API-->>JS: JSON {shops: [...]}
    JS->>UI: Render shop cards (name, image, description, rating)

    Guest->>UI: Sorts shops (by rating, newest, alphabetical)
    JS->>JS: Sort shopsCache by selected criteria
    JS->>UI: Re-render shop cards in new order

    Guest->>UI: Clicks a shop card
    JS->>UI: openShopModal(shopId)
    JS->>RAPI: GET ratings_api.php?action=get_target_ratings&target_type=shop&target_id=X
    RAPI->>DB: SELECT * FROM feedback WHERE target_type='shop' AND target_id=X
    DB-->>RAPI: Shop reviews
    RAPI-->>JS: JSON {ratings: [...], average, count}
    JS->>UI: Display shop detail modal with reviews and rating
```

---

## 13. Guest — View Shop Products

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant API as api.php
    participant DB as MySQL (products)

    Guest->>UI: Opens shop modal (from Browse Shops)
    JS->>API: GET api.php?action=list_products&shop_id=X
    API->>DB: SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id=c.id WHERE p.shop_id=X
    DB-->>API: Products array with categories
    API-->>JS: JSON {products: [...]}
    JS->>UI: Render products grouped by category inside shop modal
    JS->>UI: Show product cards (name, price, image, rating)

    Guest->>UI: Sorts products (price high/low, rating, newest)
    JS->>JS: Sort productsCache by criteria
    JS->>UI: Re-render products in new order

    Guest->>UI: Clicks a product card
    JS->>UI: openProductModal(product)
    JS->>UI: Show full product details (name, price, stock, description, image)
```

---

## 14. Guest — Submit Shop/Product Rating

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant FS as feedback-system.js
    participant RAPI as ratings_api.php
    participant DB as MySQL (feedback)

    Guest->>UI: Clicks "Rate & Review" on shop or product
    UI->>FS: FeedbackSystem.open(target_type, target_id, target_name)
    FS->>UI: Display rating modal (1-5 stars, name, email, message, anonymous toggle)

    Guest->>UI: Selects star rating (e.g. 4 stars)
    Guest->>UI: Enters name, email, review message
    Guest->>UI: Optionally toggles "Anonymous"
    Guest->>UI: Clicks "Submit Review"
    UI->>FS: handleSubmit()
    FS->>FS: Validate fields (rating required, email format)
    FS->>RAPI: POST ratings_api.php?action=submit_rating
    Note right of FS: {target_type, target_id, target_name, rating, name, email, message, anonymous}
    RAPI->>DB: INSERT INTO feedback (target_type, target_id, target_name, rating, user_name, user_email, message, anonymous)
    DB-->>RAPI: Insert success
    RAPI-->>FS: {success: true}
    FS->>UI: Close modal
    FS->>UI: Show success snackbar "Review submitted!"
    FS->>UI: Refresh rating display on shop/product card
```

---

## 15. Guest — Plan Itinerary (Multi-Step)

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant API as api.php
    participant DB as MySQL
    participant OSRM as OSRM Router
    participant Weather as Open-Meteo API

    rect rgb(240, 248, 255)
    Note over Guest,DB: Step 1 — Trip Preferences
    Guest->>UI: Navigate to Itinerary section
    Guest->>UI: Set check-in/out dates, adults, kids, budget (₱1K-₱30K)
    Guest->>UI: Select travel style chips (Nature, Beach, Food, Culture, Adventure, Mixed)
    UI->>JS: Store preferences in tripPreferences object
    end

    rect rgb(245, 255, 245)
    Note over Guest,DB: Step 1 — Select Destinations
    JS->>API: GET api.php?action=list_destinations
    API->>DB: SELECT * FROM destinations
    DB-->>API: Destinations list
    API-->>JS: JSON response
    JS->>API: GET api.php?action=list_itinerary_hotels
    API->>DB: SELECT * FROM itinerary_hotels
    DB-->>API: Hotels list
    API-->>JS: JSON response
    JS->>UI: Render destination carousel with filters
    JS->>UI: Show interactive overview map with markers + crowd levels

    Guest->>UI: Filters destinations (Recommended / Category / Sort)
    JS->>JS: Filter and sort from destinationsCache
    JS->>UI: Update destination carousel

    Guest->>UI: Clicks destination on map
    JS->>UI: Show info card (image, category, crowd %, rating, entrance fee)
    Guest->>UI: Selects multiple destinations
    JS->>UI: Update budget tracker (entrance fees vs. travel budget)
    Guest->>UI: Clicks "Build Itinerary"
    end

    rect rgb(255, 248, 240)
    Note over Guest,DB: Step 2 — Itinerary Details
    JS->>JS: Allocate destinations to days based on trip duration
    JS->>UI: Display day-by-day itinerary tabs
    JS->>UI: Show hotel recommendations per day (sorted by proximity/price/rating)
    Guest->>UI: Selects hotel for each day
    JS->>UI: Update budget progress bar with hotel costs

    Guest->>UI: Clicks "Directions" to destination
    JS->>OSRM: Request route (hotel → destination)
    OSRM-->>JS: Route polyline + distance + duration
    JS->>UI: Show route on map with travel time

    JS->>Weather: GET Open-Meteo forecast for trip dates
    Weather-->>JS: Weather data (temp, wind, rain probability)
    JS->>UI: Display weather cards per day
    end

    rect rgb(255, 245, 250)
    Note over Guest,DB: Step 3 — Confirm & Save
    JS->>UI: Display trip summary (title, destinations, duration, travelers, style, budget, est. cost)
    Guest->>UI: Reviews summary
    Guest->>UI: Clicks "Save Itinerary"
    JS->>API: POST api.php?action=save_itinerary
    Note right of JS: {name, email, title, days, destinations, anonymous}
    API->>DB: INSERT INTO itineraries
    DB-->>API: Insert success
    API-->>JS: {success: true}
    JS->>UI: Show success notification
    end
```

---

## 16. Guest — Download Itinerary PDF

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant PDF as html2pdf.js

    Guest->>UI: Clicks "Download PDF" on itinerary summary
    UI->>JS: saveSummaryPDF()
    JS->>JS: Build inline-styled HTML from itinerary data
    JS->>JS: Create hidden div element, append to DOM
    JS->>PDF: html2pdf().from(element).set(options).save()
    PDF->>PDF: html2canvas renders element to canvas
    PDF->>PDF: jsPDF converts canvas to PDF pages
    PDF-->>Guest: Browser downloads "Legazpi_Explorer_Itinerary.pdf"
    JS->>JS: Remove hidden div from DOM (cleanup)
```

---

## 17. Guest — Submit System Feedback

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant FS as feedback-system.js
    participant RAPI as ratings_api.php
    participant DB as MySQL (feedback)

    Guest->>UI: Scrolls to "Rate & Review" system section
    Guest->>UI: Clicks star rating (1-5)
    Guest->>UI: Enters name, email, message
    Guest->>UI: Optionally toggles "Anonymous"
    Guest->>UI: Clicks "Submit Feedback"
    UI->>FS: handleSubmit()
    FS->>RAPI: POST ratings_api.php?action=submit_rating
    Note right of FS: {target_type: "system", rating, name, email, message, anonymous}
    RAPI->>DB: INSERT INTO feedback (target_type='system', rating, user_name, user_email, message)
    DB-->>RAPI: Insert success
    RAPI-->>FS: {success: true}
    FS->>UI: Show success snackbar
    FS->>UI: Add new review to feedback carousel
    FS->>UI: Carousel auto-scrolls showing latest reviews
```

---

## 18. Guest — View Weather Forecast

```mermaid
sequenceDiagram
    actor Guest
    participant UI as index.html
    participant JS as script.js
    participant Weather as Open-Meteo API

    Guest->>UI: Opens itinerary planner and sets travel dates
    UI->>JS: fetchItineraryWeather(destination)
    JS->>Weather: GET https://api.open-meteo.com/v1/forecast
    Note right of JS: ?latitude=13.14&longitude=123.73&current_weather=true&daily=temperature_2m_max,min,precipitation_probability&timezone=Asia/Manila
    Weather-->>JS: JSON {current_weather: {temp, windspeed}, daily: {dates, temps, precip}}
    JS->>JS: Parse weather data for trip date range
    JS->>UI: Display weather cards per itinerary day
    JS->>UI: Show temperature, wind speed, rain probability
    JS->>UI: Apply weather icons (sunny, cloudy, rainy)
```

---

# ADMIN SEQUENCE DIAGRAMS

---

## 19. Admin — Login / Logout

```mermaid
sequenceDiagram
    actor Admin
    participant Login as admin_login.html
    participant API as admin_login.php
    participant Session as admin_session.php
    participant DB as MySQL (users)
    participant Dashboard as admin_dashboard.html

    Admin->>Login: Enters username and password
    Admin->>Login: Clicks "Login"
    Login->>API: POST admin_login.php {username, password}
    API->>DB: SELECT * FROM users WHERE username = ? AND role = 'admin'
    DB-->>API: Admin user record
    API->>API: Verify password hash
    API->>Session: Start session, store admin_id
    API-->>Login: {success: true}
    Login->>Dashboard: Redirect to admin_dashboard.html

    Note over Admin,Dashboard: Later...
    Admin->>Dashboard: Clicks "Logout"
    Dashboard->>Session: Destroy session
    Session-->>Dashboard: Session cleared
    Dashboard->>Login: Redirect to admin_login.html
```

---

## 20. Admin — View Dashboard Metrics & Analytics

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant DB as MySQL
    participant Chart as Chart.js

    Admin->>UI: Opens Dashboard (Overview tab)
    UI->>API: GET api.php?action=analytics_summary

    par Parallel Database Queries
        API->>DB: SELECT COUNT(*) FROM destinations
        API->>DB: SELECT COUNT(*) FROM local_experiences
        API->>DB: SELECT COUNT(*) FROM shops
        API->>DB: SELECT COUNT(*) FROM feedback
        API->>DB: SELECT AVG(rating) FROM feedback WHERE target_type='destination'
        API->>DB: SELECT AVG(rating) FROM feedback WHERE target_type='hotel'
        API->>DB: SELECT AVG(rating) FROM feedback WHERE target_type='shop'
        API->>DB: SELECT AVG(rating) FROM feedback WHERE target_type='product'
        API->>DB: SELECT AVG(rating) FROM feedback WHERE target_type='system'
    end

    DB-->>API: Aggregated data
    API-->>UI: JSON {total_destinations, total_experiences, total_shops, total_feedback, avg_destination_rating, avg_hotel_rating, avg_shop_rating, avg_product_rating, avg_system_rating, ...}
    UI->>UI: Update 4 stat cards (Destinations, Experiences, Shops, Feedback)
    UI->>UI: Update 5 rating cards with star displays

    UI->>API: GET api.php?action=get_guest_activity_logs
    API->>DB: SELECT * FROM activity_logs ORDER BY created_at DESC
    DB-->>API: Logs data
    API-->>UI: Render Guest Activity Logs panel

    UI->>API: GET api.php?action=list_feedback
    API->>DB: SELECT * FROM feedback ORDER BY created_at DESC
    DB-->>API: Feedback list
    API-->>UI: Render Guest Feedback preview panel

    UI->>Chart: Draw guest logs timeline (line chart)
    UI->>Chart: Draw peak hours chart (line chart)
    UI->>Chart: Draw city breakdown chart (doughnut)
```

---

## 21. Admin — Manage Destinations (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant Upload as upload_destination_image.php
    participant DB as MySQL (destinations)
    participant Map as Leaflet Map

    Note over Admin,Map: CREATE
    Admin->>UI: Click "Destination Management" tab
    UI->>API: GET api.php?action=list_destinations
    API->>DB: SELECT * FROM destinations ORDER BY id DESC
    DB-->>API: Destinations list
    API-->>UI: Render destinations in grid

    Admin->>UI: Fill form: title, type, location, visitors, entrance fee, description
    Admin->>UI: Set open days (start → end), operating hours (start/end time)
    Admin->>Map: Click map to pin location (lat, lng auto-filled)
    Admin->>UI: Select image file
    UI->>Upload: POST FormData (image)
    Upload-->>UI: {success: true, path: "uploads/destination_xxx.jpg"}
    Admin->>UI: Click "Add Destination"
    UI->>API: POST api.php?action=create_event
    Note right of UI: {title, type, location, lat, lng, expected_visitors, entrance_fee, description, image, open_days_start, open_days_end, hours_start, hours_end, ...}
    API->>DB: INSERT INTO destinations (...)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh destinations list

    Note over Admin,Map: EDIT
    Admin->>UI: Click "Edit" on destination card
    UI->>UI: Populate form with existing data (title, type, location, hours, etc.)
    UI->>Map: Show existing pin on map
    Admin->>UI: Modify fields
    Admin->>UI: Click "Update Destination"
    UI->>API: POST api.php?action=edit_event {id, ...updated fields}
    API->>DB: UPDATE destinations SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}
    UI->>UI: Refresh list

    Note over Admin,Map: DELETE
    Admin->>UI: Click "Delete" on destination card
    UI->>UI: Confirm deletion dialog
    Admin->>UI: Confirms
    UI->>API: POST api.php?action=delete_event {id}
    API->>DB: DELETE FROM destinations WHERE id = X
    DB-->>API: Delete success
    API-->>UI: {success: true}
    UI->>UI: Remove card from list
```

---

## 22. Admin — Manage Shops (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant Upload as upload_shop_image.php
    participant DB as MySQL (shops)
    participant Map as Leaflet Map

    Note over Admin,Map: LIST
    Admin->>UI: Click "Shops" tab
    UI->>API: GET api.php?action=list_shops
    API->>DB: SELECT * FROM shops ORDER BY id DESC
    DB-->>API: Shops array
    API-->>UI: Render shop cards with ratings overview

    Note over Admin,Map: CREATE
    Admin->>UI: Fill shop form: name, owner, address, contact, description
    Admin->>Map: Pin shop location on map (lat, lng)
    Admin->>UI: Select image file
    UI->>Upload: POST FormData (image)
    Upload-->>UI: {success: true, path: "uploads/shop_xxx.jpg"}
    Admin->>UI: Click "Add Shop"
    UI->>API: POST api.php?action=create_shop
    Note right of UI: {name, owner, address, contact, description, image, lat, lng}
    API->>DB: INSERT INTO shops (...)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh shops list

    Note over Admin,Map: EDIT
    Admin->>UI: Click "Edit" on shop
    UI->>UI: Populate form with shop data
    Admin->>UI: Modify fields, click "Update"
    UI->>API: POST api.php?action=edit_shop {id, ...}
    API->>DB: UPDATE shops SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Note over Admin,Map: DELETE
    Admin->>UI: Click "Delete" on shop
    UI->>API: POST api.php?action=delete_shop {id}
    API->>DB: DELETE FROM shops WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove shop from list
```

---

## 23. Admin — Manage Products (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant Upload as upload_product_image.php
    participant DB as MySQL (products)

    Note over Admin,DB: LIST
    Admin->>UI: Click "View Products" on a shop
    UI->>API: GET api.php?action=list_products&shop_id=X
    API->>DB: SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id=c.id WHERE p.shop_id=X
    DB-->>API: Products array
    API-->>UI: Render product cards per shop

    Note over Admin,DB: CREATE
    Admin->>UI: Fill product form: name, category, price, stock, description
    Admin->>UI: Select product image
    UI->>Upload: POST FormData (image)
    Upload-->>UI: {success: true, path: "uploads/product_xxx.jpg"}
    Admin->>UI: Click "Add Product"
    UI->>API: POST api.php?action=create_product
    Note right of UI: {shop_id, name, category_id, price, stock, description, image}
    API->>DB: INSERT INTO products (...)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh product list

    Note over Admin,DB: EDIT
    Admin->>UI: Click "Edit" on product
    UI->>UI: Populate form
    Admin->>UI: Modify fields, click "Update"
    UI->>API: POST api.php?action=edit_product {id, ...}
    API->>DB: UPDATE products SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Note over Admin,DB: DELETE
    Admin->>UI: Click "Delete" on product
    UI->>API: POST api.php?action=delete_product {id}
    API->>DB: DELETE FROM products WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove from list
```

---

## 24. Admin — Manage Product Categories

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant DB as MySQL (product_categories)

    Admin->>UI: Click "Manage Categories" on a shop
    UI->>API: GET api.php?action=list_categories&shop_id=X
    API->>DB: SELECT * FROM product_categories WHERE shop_id = X
    DB-->>API: Categories array
    API-->>UI: Render categories list

    Admin->>UI: Enter new category name
    Admin->>UI: Click "Add Category"
    UI->>API: POST api.php?action=create_category {shop_id, name}
    API->>DB: INSERT INTO product_categories (shop_id, name)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh categories

    Admin->>UI: Click "Edit" on category
    Admin->>UI: Modify name, click "Save"
    UI->>API: POST api.php?action=edit_category {id, name}
    API->>DB: UPDATE product_categories SET name = ? WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Admin->>UI: Click "Delete" category
    UI->>API: POST api.php?action=delete_category {id}
    API->>DB: DELETE FROM product_categories WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove from list
```

---

## 25. Admin — Manage Hotels (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant Upload as upload_hotel_image.php
    participant DB as MySQL (itinerary_hotels)
    participant Map as Leaflet Map

    Note over Admin,Map: LIST
    Admin->>UI: Click "Hotels" tab
    UI->>API: GET api.php?action=list_itinerary_hotels
    API->>DB: SELECT * FROM itinerary_hotels ORDER BY id DESC
    DB-->>API: Hotels array
    API-->>UI: Render hotel cards

    Note over Admin,Map: CREATE
    Admin->>UI: Fill hotel form: name, category (Budget/Standard/Boutique/Luxury/Resort), rate/night, phone, address, description, features
    Admin->>Map: Pin hotel location (lat, lng)
    Admin->>UI: Select image
    UI->>Upload: POST FormData (image)
    Upload-->>UI: {success: true, path: "uploads/hotel_xxx.jpg"}
    Admin->>UI: Click "Add Hotel"
    UI->>API: POST api.php?action=add_itinerary_hotel
    Note right of UI: {name, category, rate, phone, address, description, features, image, lat, lng}
    API->>DB: INSERT INTO itinerary_hotels (...)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh hotels list

    Note over Admin,Map: EDIT
    Admin->>UI: Click "Edit" on hotel
    UI->>UI: Populate form with hotel data
    Admin->>UI: Modify fields, click "Update"
    UI->>API: POST api.php?action=edit_itinerary_hotel {id, ...}
    API->>DB: UPDATE itinerary_hotels SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Note over Admin,Map: DELETE
    Admin->>UI: Click "Delete" on hotel
    UI->>API: POST api.php?action=delete_itinerary_hotel {id}
    API->>DB: DELETE FROM itinerary_hotels WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove from list
```

---

## 26. Admin — Manage Experiences (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant Upload as upload_experience_image.php
    participant DB as MySQL (local_experiences)

    Note over Admin,DB: LIST
    Admin->>UI: Click "Experiences" tab
    UI->>API: GET api.php?action=list_experiences
    API->>DB: SELECT * FROM local_experiences ORDER BY id DESC
    DB-->>API: Experiences array
    API-->>UI: Render experience cards (title, type, price, duration)

    Note over Admin,DB: CREATE
    Admin->>UI: Fill form: title, type/category, price, duration, description
    Admin->>UI: Select image
    UI->>Upload: POST FormData (image)
    Upload-->>UI: {success: true, path: "uploads/experience_xxx.jpg"}
    Admin->>UI: Click "Add Experience"
    UI->>API: POST api.php?action=create_experience
    Note right of UI: {title, type, price, duration, description, image}
    API->>DB: INSERT INTO local_experiences (title, type, price, duration, description, image)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh experiences list

    Note over Admin,DB: EDIT
    Admin->>UI: Click "Edit" on experience
    UI->>UI: Populate form (title, type, price, duration, description, image)
    Admin->>UI: Modify fields, click "Update"
    UI->>API: POST api.php?action=edit_experience {id, title, type, price, duration, description, image}
    API->>DB: UPDATE local_experiences SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Note over Admin,DB: DELETE
    Admin->>UI: Click "Delete" on experience
    UI->>API: POST api.php?action=delete_experience {id}
    API->>DB: DELETE FROM local_experiences WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove from list
```

---

## 27. Admin — Manage Festivals (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant Upload as upload_festival_image.php
    participant DB as MySQL (festivals_events)

    Note over Admin,DB: LIST
    Admin->>UI: Click "Festivals" tab
    UI->>API: GET api.php?action=list_festivals
    API->>DB: SELECT * FROM festivals_events ORDER BY start_date DESC
    DB-->>API: Festivals array
    API-->>UI: Render festival cards (name, dates, location, type)

    Note over Admin,DB: CREATE
    Admin->>UI: Fill form: name, location, start date, end date, type, expected attendance, description
    Admin->>UI: Select image or video file
    UI->>Upload: POST FormData (image/video)
    Upload-->>UI: {success: true, path: "uploads/festival_xxx.jpg"}
    Admin->>UI: Click "Add Festival"
    UI->>API: POST api.php?action=create_festival
    Note right of UI: {name, location, start_date, end_date, type, expected_attendance, description, image}
    API->>DB: INSERT INTO festivals_events (...)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh festivals list

    Note over Admin,DB: EDIT
    Admin->>UI: Click "Edit" on festival
    UI->>UI: Populate form
    Admin->>UI: Modify fields, click "Update"
    UI->>API: POST api.php?action=edit_festival {id, ...}
    API->>DB: UPDATE festivals_events SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Note over Admin,DB: DELETE
    Admin->>UI: Click "Delete" on festival
    UI->>API: POST api.php?action=delete_festival {id}
    API->>DB: DELETE FROM festivals_events WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove from list
```

---

## 28. Admin — Manage Alerts (CRUD)

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant DB as MySQL (event_alerts)

    Note over Admin,DB: LIST
    Admin->>UI: Click "Alerts" tab
    UI->>API: GET api.php?action=get_event_alerts
    API->>DB: SELECT a.*, e.title as event_name FROM event_alerts a LEFT JOIN events e ON a.event_id=e.id
    DB-->>API: Alerts array with event names
    API-->>UI: Render active alerts list

    Note over Admin,DB: CREATE
    Admin->>UI: Select destination from dropdown
    Admin->>UI: Select alert type (DO / DON'T / BRING / DON'T BRING)
    Admin->>UI: Optionally toggle "Global alert"
    Admin->>UI: Enter alert message
    Admin->>UI: Click "Add Alert"
    UI->>API: POST api.php?action=add_event_alert
    Note right of UI: {event_id, alert_type, content, is_global}
    API->>DB: INSERT INTO event_alerts (event_id, alert_type, content, is_global)
    DB-->>API: Insert success
    API-->>UI: {success: true}
    UI->>UI: Refresh alerts list

    Note over Admin,DB: EDIT
    Admin->>UI: Click "Edit" on alert
    UI->>UI: Populate form with alert data
    Admin->>UI: Modify fields, click "Update"
    UI->>API: POST api.php?action=edit_alert {id, event_id, alert_type, content, is_global}
    API->>DB: UPDATE event_alerts SET ... WHERE id = X
    DB-->>API: Update success
    API-->>UI: {success: true}

    Note over Admin,DB: DELETE
    Admin->>UI: Click "Delete" on alert
    UI->>API: POST api.php?action=delete_alert {id}
    API->>DB: DELETE FROM event_alerts WHERE id = X
    DB-->>API: Delete success
    API-->>UI: Remove from list
```

---

## 29. Admin — View & Manage Feedback/Ratings

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant RAPI as ratings_api.php
    participant DB as MySQL (feedback)

    Admin->>UI: Click "Feedback & Ratings" tab
    UI->>API: GET api.php?action=list_feedback
    API->>DB: SELECT * FROM feedback ORDER BY created_at DESC
    DB-->>API: All feedback/reviews
    API-->>UI: JSON {feedback: [...]}

    UI->>UI: Calculate rating statistics (total reviews, averages per type, positive %)
    UI->>UI: Render statistics cards
    UI->>UI: Render feedback list (user, rating stars, message, type, date)

    Admin->>UI: Filter by type (shop / product / destination / hotel / system)
    UI->>UI: Filter displayed feedback by selected target_type
    Admin->>UI: Filter by rating (1-5 stars)
    UI->>UI: Filter displayed feedback by selected rating

    Admin->>UI: Click "Delete" on a review
    UI->>RAPI: DELETE ratings_api.php?action=delete_rating {id}
    RAPI->>DB: DELETE FROM feedback WHERE id = X
    DB-->>RAPI: Delete success
    RAPI-->>UI: {success: true}
    UI->>UI: Remove review from list
    UI->>UI: Recalculate statistics
```

---

## 30. Admin — View Guest Activity Logs

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant API as api.php
    participant DB as MySQL (activity_logs)

    Admin->>UI: Opens Dashboard → Guest Logs panel
    UI->>API: GET api.php?action=get_guest_activity_logs
    API->>DB: SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100
    DB-->>API: Activity logs array
    API-->>UI: JSON {logs: [...]}
    UI->>UI: Render logs list (user, action, page, timestamp)

    Admin->>UI: Clicks "Refresh" button
    UI->>API: GET api.php?action=get_guest_activity_logs
    API->>DB: SELECT * FROM activity_logs ORDER BY created_at DESC
    DB-->>API: Latest logs
    API-->>UI: Update logs panel with fresh data
    UI->>UI: Update log count badge
```

---

## 31. Admin — Update Profile Image

```mermaid
sequenceDiagram
    actor Admin
    participant UI as admin_dashboard.html
    participant Upload as upload_admin_profile.php
    participant Storage as localStorage

    Admin->>UI: Clicks admin profile image in header
    UI->>UI: Open file picker dialog
    Admin->>UI: Selects new profile image file
    UI->>Upload: POST FormData (image file)
    Upload->>Upload: Validate file type and size
    Upload->>Upload: Save to backend/uploads/ directory
    Upload-->>UI: {success: true, path: "uploads/admin_profile_xxx.jpg"}
    UI->>Storage: Store image path in localStorage ('adminProfileImage')
    UI->>UI: Update profile image in header with new image
```

---

# PACKAGE DIAGRAM

---

## 32. Package Diagram

```mermaid
graph TB
    subgraph "🖥️ Presentation Layer"
        direction TB
        P1["index.html<br/><i>Guest Interface</i>"]
        P2["admin_dashboard.html<br/><i>Admin Interface</i>"]
        P3["style.css<br/><i>Styles & Animations</i>"]
    end

    subgraph "📦 Frontend Logic Layer"
        direction TB
        F1["script.js<br/><i>Core Application Logic</i><br/>Destinations, Shops, Hotels,<br/>Itinerary, Weather, Tracking"]
        F2["feedback-system.js<br/><i>Rating & Review Module</i>"]
        F3["shop-ratings.js<br/><i>Shop Rating Manager</i>"]
        F4["contextual-feedback.js<br/><i>Contextual Feedback</i>"]
        F5["shop-feedback-sync.js<br/><i>Feedback Sync</i>"]
        F6["nlp-analytics.js<br/><i>NLP Analytics UI</i>"]
    end

    subgraph "📚 External Libraries"
        direction TB
        L1["Leaflet.js<br/><i>Interactive Maps</i>"]
        L2["Chart.js<br/><i>Analytics Charts</i>"]
        L3["html2pdf.js<br/><i>PDF Generation</i>"]
        L4["OSRM<br/><i>Road Routing</i>"]
        L5["Open-Meteo API<br/><i>Weather Data</i>"]
    end

    subgraph "⚙️ Backend API Layer"
        direction TB
        B1["api.php<br/><i>Main REST API Router</i><br/>80+ Action Handlers"]
        B2["ratings_api.php<br/><i>Ratings & Reviews API</i>"]
        B3["admin_login.php<br/><i>Authentication</i>"]
        B4["admin_session.php<br/><i>Session Management</i>"]
    end

    subgraph "📤 Upload Handlers"
        direction TB
        U1["upload_destination_image.php"]
        U2["upload_shop_image.php"]
        U3["upload_product_image.php"]
        U4["upload_hotel_image.php"]
        U5["upload_experience_image.php"]
        U6["upload_festival_image.php"]
    end

    subgraph "🤖 ML / AI Layer"
        direction TB
        M1["ml_predict.php<br/><i>ML Prediction Runner</i>"]
        M2["MLServerBridge.php<br/><i>ML Server Bridge</i>"]
        M3["ml_api_client.php<br/><i>ML API Client</i>"]
        M4["event_predictions_api.php<br/><i>ARIMA Predictions</i>"]
        M5["nlp_analyzer.py<br/><i>NLP Sentiment Analysis</i>"]
        M6["ml/ server.js<br/><i>Node.js ML Server</i>"]
    end

    subgraph "🗄️ Data Layer"
        direction TB
        D1["db.php<br/><i>MySQL Connection</i>"]
        D2[("MySQL Database<br/><i>capstone_db</i><br/>23 Tables")]
    end

    subgraph "📁 Database Tables"
        direction TB
        T1["destinations / events"]
        T2["shops / products / product_categories"]
        T3["itinerary_hotels / itinerary_destinations"]
        T4["local_experiences / festivals_events"]
        T5["feedback (ratings & reviews)"]
        T6["itineraries / users"]
        T7["event_alerts / event_predictions"]
        T8["activity_logs / anonymous_sessions"]
        T9["place_analytics / shop_interactions"]
    end

    P1 --> F1
    P1 --> F2
    P1 --> F3
    P1 --> F4
    P1 --> F5
    P2 --> F1

    F1 --> L1
    F1 --> L2
    F1 --> L3
    F1 --> L4
    F1 --> L5

    F1 --> B1
    F2 --> B2
    F3 --> B2
    P2 --> B1
    P2 --> B2
    P2 --> B3

    B1 --> D1
    B2 --> D1
    B3 --> D1
    B4 --> D1
    B1 --> M1
    B1 --> M4

    M1 --> M2
    M2 --> M6
    M1 --> M3

    D1 --> D2
    D2 --> T1
    D2 --> T2
    D2 --> T3
    D2 --> T4
    D2 --> T5
    D2 --> T6
    D2 --> T7
    D2 --> T8
    D2 --> T9

    B1 --> U1
    B1 --> U2
    B1 --> U3
    B1 --> U4
    B1 --> U5
    B1 --> U6

    style P1 fill:#e3f2fd,stroke:#1565C0,color:#000
    style P2 fill:#fce4ec,stroke:#C62828,color:#000
    style P3 fill:#e8eaf6,stroke:#283593,color:#000
    style F1 fill:#fff3e0,stroke:#E65100,color:#000
    style F2 fill:#fff3e0,stroke:#E65100,color:#000
    style F3 fill:#fff3e0,stroke:#E65100,color:#000
    style B1 fill:#e8f5e9,stroke:#2E7D32,color:#000
    style B2 fill:#e8f5e9,stroke:#2E7D32,color:#000
    style D1 fill:#f3e5f5,stroke:#6A1B9A,color:#000
    style D2 fill:#f3e5f5,stroke:#6A1B9A,color:#000
    style M1 fill:#fff8e1,stroke:#F57F17,color:#000
    style M6 fill:#fff8e1,stroke:#F57F17,color:#000
```

---

## Diagram Summary

| # | Diagram | Actor | Description |
|---|---------|-------|-------------|
| 1 | Use Case (Guest) | Guest | 19 use cases: browsing, map, directions, itinerary, ratings, weather |
| 2 | Use Case (Admin) | Admin | 19 use cases: CRUD for 6 entities, analytics, feedback, alerts |
| 3 | Browse Destinations | Guest | Load destinations from DB, render carousel with category filters |
| 4 | Filter Destinations | Guest | Client-side category filtering from cached data |
| 5 | Interactive Map | Guest | Leaflet map with crowd-level markers + ML predictions |
| 6 | Visitor Forecasts | Guest | ARIMA model predictions rendered in Chart.js |
| 7 | Waste Predictions | Guest | Waste breakdown doughnut + per-destination bar charts |
| 8 | Get Directions | Guest | GPS geolocation → OSRM routing → polyline on map |
| 9 | Destination Alerts | Guest | Fetch DO/DON'T/BRING alerts per destination |
| 10 | Browse Experiences | Guest | Load experiences grid from local_experiences table |
| 11 | Browse Festivals | Guest | Load festivals with image/video from festivals_events table |
| 12 | Browse Shops | Guest | Load shops with sorting, open detail modal with reviews |
| 13 | View Products | Guest | Load products by shop, grouped by category |
| 14 | Submit Rating | Guest | Universal feedback modal → ratings_api.php → feedback table |
| 15 | Plan Itinerary | Guest | 3-step: preferences → select destinations/hotels → confirm & save |
| 16 | Download PDF | Guest | html2pdf.js generates PDF from itinerary summary |
| 17 | System Feedback | Guest | Rate the system (1-5 stars) → feedback carousel |
| 18 | Weather Forecast | Guest | Open-Meteo API → weather cards per itinerary day |
| 19 | Admin Login/Logout | Admin | Session-based authentication |
| 20 | Dashboard Metrics | Admin | Analytics summary with parallel DB queries + Chart.js |
| 21 | Manage Destinations | Admin | Full CRUD with map pin + image upload |
| 22 | Manage Shops | Admin | Full CRUD with map location + image upload |
| 23 | Manage Products | Admin | Full CRUD per shop with image upload |
| 24 | Manage Categories | Admin | CRUD product categories per shop |
| 25 | Manage Hotels | Admin | Full CRUD with map pin + image upload |
| 26 | Manage Experiences | Admin | Full CRUD with price, duration, image upload |
| 27 | Manage Festivals | Admin | Full CRUD with dates, attendance, image/video upload |
| 28 | Manage Alerts | Admin | CRUD destination alerts (DO/DON'T/BRING/DON'T BRING) |
| 29 | Manage Feedback | Admin | View, filter, delete ratings/reviews |
| 30 | Guest Activity Logs | Admin | View tracked guest interactions |
| 31 | Update Profile | Admin | Upload profile image (localStorage + backend) |
| 32 | Package Diagram | System | 7-layer architecture with all components |
