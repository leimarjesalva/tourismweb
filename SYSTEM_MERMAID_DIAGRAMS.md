# System Documentation with Detailed Mermaid Diagrams

---

## 1. Use Case Diagram

```mermaid
usecaseDiagram
actor Admin
actor Guest

Admin --> (View Dashboard & Analytics)
Admin --> (Manage Destinations)
Admin --> (Manage Shops)
Admin --> (Manage Hotels)
Admin --> (Moderate Ratings & Reviews)
Admin --> (Export Reports)
Admin --> (Receive System Alerts)

Guest --> (Browse Destination Map)
Guest --> (View Destination Details)
Guest --> (View Visitor Predictions)
Guest --> (View Waste Insights)
Guest --> (Browse Novelty Shops)
Guest --> (View Shop Details)
Guest --> (Rate & Review Shops)
Guest --> (Plan Custom Itinerary)
Guest --> (Save/Share Itinerary)
Guest --> (Rate & Review System)
```

---

## 2. Class Diagram (High-level, Detailed)

```mermaid
classDiagram
class Admin {
  +login()
  +viewDashboard()
  +manageDestinations()
  +manageShops()
  +manageHotels()
  +moderateReviews()
  +exportReports()
  +receiveAlerts()
}
class Guest {
  +register()
  +browseMap()
  +viewPredictions()
  +viewWasteInsights()
  +browseShops()
  +planItinerary()
  +rateReview()
  +saveItinerary()
}
class Destination {
  +name
  +description
  +category
  +location
  +images
  +openingHours
  +events
  +visitorStats
  +feedback[]
}
class Shop {
  +name
  +products[]
  +location
  +images
  +contactInfo
  +ratings[]
  +reviews[]
  +performanceStats
}
class Hotel {
  +name
  +amenities[]
  +location
  +roomTypes[]
  +rates
  +images
  +availability
  +ratings[]
  +reviews[]
  +bookingStats
}
class Review {
  +author
  +content
  +rating
  +date
  +type
  +status
}
class Itinerary {
  +owner
  +items[]
  +route
  +shareLink
  +createdAt
}
class Prediction {
  +destination
  +date
  +predictedVisitors
  +confidence
}
class WasteInsight {
  +event
  +location
  +wasteType
  +quantity
  +date
}

Admin "1" -- "many" Destination
Admin "1" -- "many" Shop
Admin "1" -- "many" Hotel
Admin "1" -- "many" Review
Guest "1" -- "many" Review
Guest "1" -- "many" Itinerary
Destination "1" -- "many" Review
Shop "1" -- "many" Review
Hotel "1" -- "many" Review
Destination "1" -- "many" Prediction
Destination "1" -- "many" WasteInsight
```

---

## 3. Sequence Diagram (Guest Planning and Saving Itinerary)


### Admin Side Sequence Diagrams

#### 1. Dashboard - Metrics
```mermaid
sequenceDiagram
Admin->>Frontend: Login & Open Dashboard
Frontend->>API: Request Metrics & Analytics
API->>MLPrediction: Fetch Analytics/Predictions
API->>Backend: Fetch Raw Metrics Data
MLPrediction-->>API: Return Analytics/Predictions
Backend-->>API: Return Raw Metrics Data
API-->>Frontend: Return Combined Metrics
Frontend-->>Admin: Display Dashboard Metrics
```

#### 2. Destination Management
```mermaid
sequenceDiagram
Admin->>Frontend: Open Destination Management
Frontend->>API: Fetch Destinations List
API->>Backend: Get Destinations
Backend-->>API: Return Destinations
API-->>Frontend: Return Destinations
Admin->>Frontend: Add/Edit/Delete Destination
Frontend->>API: Submit Changes
API->>Backend: Update Destinations
Backend-->>API: Confirm Update
API-->>Frontend: Confirm Update
Frontend-->>Admin: Show Confirmation
```

#### 3. Shop Management
```mermaid
sequenceDiagram
Admin->>Frontend: Open Shop Management
Frontend->>API: Fetch Shops & Products List
API->>Backend: Get Shops & Products
Backend-->>API: Return Shops & Products
API->>MLPrediction: Analyze Shop Products (e.g., sales trends, recommendations)
MLPrediction-->>API: Return Product Analytics
API-->>Frontend: Return Shops, Products & Analytics
Admin->>Frontend: Add/Edit/Delete Shop/Product
Frontend->>API: Submit Changes
API->>Backend: Update Shops/Products
Backend-->>API: Confirm Update
API-->>Frontend: Confirm Update
Frontend-->>Admin: Show Confirmation
```

#### 4. Hotel Management
```mermaid
sequenceDiagram
Admin->>Frontend: Open Hotel Management
Frontend->>API: Fetch Hotels List
API->>Backend: Get Hotels
Backend-->>API: Return Hotels
API-->>Frontend: Return Hotels
Admin->>Frontend: Add/Edit/Delete Hotel
Frontend->>API: Submit Changes
API->>Backend: Update Hotels
Backend-->>API: Confirm Update
API-->>Frontend: Confirm Update
Frontend-->>Admin: Show Confirmation
```

#### 5. Guest Ratings & Reviews Moderation
```mermaid
sequenceDiagram
Admin->>Frontend: Open Ratings & Reviews Moderation
Frontend->>API: Fetch Pending Reviews
API->>Backend: Get Pending Reviews
Backend-->>API: Return Reviews
API->>MLPrediction: Analyze Reviews (for moderation)
MLPrediction-->>API: Return Analysis
API-->>Frontend: Return Reviews & Analysis
Admin->>Frontend: Approve/Reject/Edit Review
Frontend->>API: Update Review Status
API->>Backend: Update Review
Backend-->>API: Confirm Update
API-->>Frontend: Confirm Update
Frontend-->>Admin: Show Confirmation
```

---

### Guest Side Sequence Diagrams

#### 1. Legazpi Destination Map
```mermaid
sequenceDiagram
Guest->>Frontend: Open Destination Map
Frontend->>API: Request Destinations Data
API->>Backend: Fetch Destinations
Backend-->>API: Return Destinations
API->>MLPrediction: Analyze Destinations (optional)
MLPrediction-->>API: Return Analysis
API-->>Frontend: Return Destinations & Analysis
Frontend-->>Guest: Display Map
```

#### 2. Visitor Predictions
```mermaid
sequenceDiagram
Guest->>Frontend: View Visitor Predictions
Frontend->>API: Request Prediction Data
API->>MLPrediction: Fetch Visitor Predictions
MLPrediction-->>API: Return Predictions
API-->>Frontend: Return Predictions
Frontend-->>Guest: Show Predictions
```

#### 3. Waste Breakdown
```mermaid
sequenceDiagram
Guest->>Frontend: View Waste Breakdown
Frontend->>API: Request Waste Data
API->>Backend: Fetch Waste Insights
Backend-->>API: Return Waste Insights
API->>MLPrediction: Analyze Waste Data (optional)
MLPrediction-->>API: Return Analysis
API-->>Frontend: Return Waste Insights & Analysis
Frontend-->>Guest: Show Waste Breakdown
```

#### 4. Novelty Shops
```mermaid
sequenceDiagram
Guest->>Frontend: Browse Novelty Shops
Frontend->>API: Request Shops Data
API->>Backend: Fetch Shops
Backend-->>API: Return Shops
API->>MLPrediction: Analyze Shops (optional)
MLPrediction-->>API: Return Analysis
API-->>Frontend: Return Shops & Analysis
Frontend-->>Guest: Display Shops
```

#### 5. Custom Itinerary
```mermaid
sequenceDiagram
Guest->>Frontend: Open Itinerary Planner
Frontend->>API: Request Destinations, Shops, Events, Predictions
API->>Backend: Fetch Destinations, Shops, Events
API->>MLPrediction: Fetch Visitor Predictions
Backend-->>API: Return Destinations, Shops, Events
MLPrediction-->>API: Return Predictions
API-->>Frontend: Return All Data
Guest->>Frontend: Add Items to Itinerary
Frontend->>API: Save Itinerary
API->>Backend: Store Itinerary
Backend-->>API: Confirm Save
API-->>Frontend: Confirm Save
Frontend-->>Guest: Show Confirmation
Guest->>Frontend: Share Itinerary
Frontend->>API: Generate Share Link
API->>Backend: Create Share Link
Backend-->>API: Return Link
API-->>Frontend: Return Link
Frontend-->>Guest: Display Share Link
```

#### 6. Rate & Review the System
```mermaid
sequenceDiagram
Guest->>Frontend: Submit Rating/Review
Frontend->>API: Send Rating/Review Data
API->>Backend: Store Rating/Review
Backend-->>API: Confirm Submission
API-->>Frontend: Confirm Submission
Frontend-->>Guest: Show Confirmation
```

---

## 4. Package Diagram

```mermaid
classDiagram
package "Frontend" {
  class "UI Components"
  class "Map Module"
  class "Itinerary Planner"
  class "Shop Directory"
  class "Feedback Module"
  class "Analytics Dashboard"
}
package "Backend" {
  class "REST API"
  class "Admin Tools"
  class "ML Prediction Engine"
  class "Moderation Service"
}
package "Data" {
  class "Destination Data"
  class "Shop Data"
  class "Hotel Data"
  class "Feedback Data"
  class "User Data"
  class "Itinerary Data"
  class "Prediction Data"
  class "Waste Data"
}
"Frontend" ..> "Backend"
"Backend" ..> "Data"
"Backend" ..> "ML Prediction Engine"
"Backend" ..> "Moderation Service"
```

---

## 5. Deployment Diagram

```mermaid
graph TD
  UserDevice[User Device (Browser/Mobile)]
  WebServer[Web Server (Apache/Nginx)]
  AppServer[Application Server (PHP)]
  MLServer[ML Prediction Server (Python)]
  DB[Database (MySQL)]
  Storage[Image/File Storage]
  Notification[Notification Service]

  UserDevice --> WebServer
  WebServer --> AppServer
  AppServer --> DB
  AppServer --> Storage
  AppServer --> MLServer
  AppServer --> Notification
```

---

## 6. System Architecture (Conceptual Design)

```mermaid
flowchart TD
  subgraph Frontend
    FE[Web/Mobile UI]
    Map[Interactive Map]
    Itinerary[Itinerary Planner]
    Feedback[Feedback & Reviews]
    Shops[Shop Directory]
    Analytics[Analytics Dashboard]
  end
  subgraph Backend
    API[REST API]
    Admin[Admin Dashboard]
    ML[AI/ML Prediction Service]
    Moderation[Moderation Service]
    DB[(Database)]
    Storage[(File/Image Storage)]
    Notification[Notification Service]
  end

  FE <--> API
  Map <--> API
  Itinerary <--> API
  Feedback <--> API
  Shops <--> API
  Analytics <--> API
  API <--> DB
  API <--> Storage
  API <--> ML
  API <--> Moderation
  API <--> Notification
  Admin <--> API
```

---
