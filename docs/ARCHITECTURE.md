# 🏗️ ML Architecture & System Design

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                       LEGAZPI EXPLORER                           │
│                    (Frontend + Admin Panel)                      │
└────────────────┬──────────────────────────────┬─────────────────┘
                 │                              │
         [JavaScript]                  [PHP Admin Dashboard]
                 │                              │
                 └──────────────┬────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                  EXISTING PHP BACKEND                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  api.php | admin_login.php | upload_event_image.php      │  │
│  └───────────────┬──────────────────────────────────────────┘  │
│                  │                                              │
│  ┌───────────────▼──────────────────────────────────────────┐  │
│  │        MLServerBridge.php (NEW)                          │  │
│  │  ✓ Connects to ML Server                                 │  │
│  │  ✓ Sends event data for prediction                       │  │
│  │  ✓ Falls back if ML server unavailable                   │  │
│  └───────────────┬──────────────────────────────────────────┘  │
└──────────────────┼──────────────────────────────────────────────┘
                   │
           HTTP POST/GET
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────┐
│              ML SERVER (Node.js + TensorFlow.js)                │
│            (NEW - backend/ml/server.js)                         │
│                                                                  │
│  ┌────────────────────────────────────────────────────────┐   │
│  │  Express.js Server (Port 3000)                         │   │
│  │  ┌──────────────────────────────────────────────────┐  │   │
│  │  │  /predict       → Classification Model           │  │   │
│  │  │  /predict-batch → Batch predictions              │  │   │
│  │  │  /history       → Get all predictions            │  │   │
│  │  │  /stats         → Get statistics                 │  │   │
│  │  │  /health        → Server health check            │  │   │
│  │  └──────────────────────────────────────────────────┘  │   │
│  └────────────────────────────────────────────────────────┘   │
│                          │                                     │
│         ┌────────────────┼────────────────┐                   │
│         │                │                │                   │
│         ▼                ▼                ▼                   │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐         │
│  │ Overcrowding │ │ Waste Model  │ │ Normalizer   │         │
│  │  Model       │ │ (Regression) │ │ (norm.json)  │         │
│  │ (64 units)   │ │ (32 units)   │ │              │         │
│  └──────────────┘ └──────────────┘ └──────────────┘         │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
                   │
           MySQL Queries
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────┐
│                     MYSQL DATABASE                              │
│                   (ibalong_ai)                                  │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ predictions table                                       │  │
│  │ ├─ attendance, venue_capacity, weather, ...             │  │
│  │ ├─ overcrowded (0/1)                                    │  │
│  │ └─ waste_prediction (kg)                                │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ festival_events table                                   │  │
│  │ ├─ event_name, event_date, expected_attendance          │  │
│  │ └─ capacity, duration, status                           │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ festival_predictions table                              │  │
│  │ ├─ festival_event_id (FK)                               │  │
│  │ ├─ predicted_attendance, predicted_waste                │  │
│  │ └─ overcrowding_risk, confidence_score                  │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow: Request → Prediction → Response

```
1. USER INPUT (Frontend/Admin)
   ↓
   └─→ Event details:
       - Expected attendance
       - Venue capacity
       - Weekend? Free?
       - Duration
       - Food stalls
       - Weather

2. SEND TO ML SERVER
   ↓
   └─→ POST /predict
       Content-Type: application/json

3. ML SERVER PROCESSING
   ↓
   ├─→ Normalize input data (using normalization.json)
   ├─→ Create tensor from normalized data
   │
   ├─→ RUN CLASSIFICATION MODEL
   │   └─→ Predict: Is this overcrowded?
   │       Output: probability (0.0 - 1.0)
   │
   └─→ RUN REGRESSION MODEL
       └─→ Predict: How much waste?
           Output: waste in kg

4. STORE RESULT
   ↓
   └─→ Save prediction to MySQL
       INSERT INTO predictions (...)

5. RETURN RESPONSE
   ↓
   └─→ JSON Response:
       {
         "is_overcrowded": true/false,
         "probability": "85.2%",
         "predicted_waste_kg": 12500
       }

6. DISPLAY TO USER
   ↓
   └─→ Show prediction
       - Alert if overcrowded
       - Show waste management needs
```

## Training Process

```
1. GENERATE SYNTHETIC DATA (1000 records)
   ├─→ Random attendance (5000-35000)
   ├─→ Random capacity (10000-40000)
   ├─→ Random categorical features
   └─→ Calculate labels (overcrowded, waste)

2. NORMALIZE DATA
   ├─→ Calculate mean & std for each feature
   ├─→ Apply z-score normalization
   └─→ Save normalization params

3. SPLIT DATA
   ├─→ 80% training (800 samples)
   └─→ 20% testing (200 samples)

4. TRAIN CLASSIFICATION MODEL
   ├─→ Test Config 1: 32 units, LR 0.001
   ├─→ Test Config 2: 64 units, LR 0.001
   ├─→ Test Config 3: 32 units, LR 0.0005
   ├─→ Pick best config
   └─→ Save model → best-overcrowding-model/

5. TRAIN REGRESSION MODEL
   ├─→ Build 3-layer network
   ├─→ Train for 50 epochs
   └─→ Save model → best-waste-model/

6. SAVE ARTIFACTS
   ├─→ model.json (architecture)
   ├─→ weights.bin (weights)
   └─→ normalization.json (for predictions)
```

## Model Architecture

```
CLASSIFICATION MODEL (Overcrowding)
===================================

Input: [attendance, capacity, weekend, is_free, duration, food_stalls, weather]
       (7 features)
         ▼
    [Dense Layer 1]
    - Units: 64
    - Activation: ReLU
    - Learns patterns
         ▼
    [Dense Layer 2]
    - Units: 32
    - Activation: ReLU
    - Learns complex patterns
         ▼
    [Output Layer]
    - Units: 1
    - Activation: Sigmoid
    - Output: 0.0-1.0 probability
         ▼
    Output: Is overcrowded? (0.5+ = Yes)


REGRESSION MODEL (Waste Prediction)
====================================

Input: [attendance, capacity, weekend, is_free, duration, food_stalls, weather]
       (7 features)
         ▼
    [Dense Layer 1]
    - Units: 64
    - Activation: ReLU
         ▼
    [Dense Layer 2]
    - Units: 32
    - Activation: ReLU
         ▼
    [Output Layer]
    - Units: 1
    - Activation: Linear (none)
    - Output: continuous value (kg)
         ▼
    Output: Predicted waste in kg
```

## Integration with Existing System

```
FRONTEND (index.html)
├─→ Festival Analytics Dashboard
│   └─→ Can call PHP API for predictions
│       └─→ PHP API calls MLServerBridge
│           └─→ MLServerBridge calls ML Server
│               └─→ Returns prediction

ADMIN DASHBOARD (admin_dashboard.html)
├─→ Admin Events Management
│   └─→ When creating festival event
│       └─→ Get crowding prediction
│       └─→ Get waste estimate
│       └─→ Display in admin UI

PHP BACKEND (api.php, admin_login.php, etc.)
├─→ New endpoint: /api/predict_festival
│   ├─→ Receives event data
│   ├─→ Calls MLServerBridge
│   ├─→ Returns prediction
│   └─→ Stores in predictions table

DATABASE
├─→ ibalong_ai (NEW ML database)
│   └─→ Separate from main capstone DB
│   └─→ Can query from PHP backend
└─→ Original capstone DB
    └─→ Unchanged, still fully functional
```

## Deployment Architecture

```
DEVELOPMENT (Current Setup)
===========================
Windows 10 PC
  ├─→ XAMPP
  │   ├─→ Apache (PHP)
  │   └─→ MySQL
  └─→ Node.js
      └─→ ML Server (Port 3000)

Browser Access:
  ├─→ http://localhost (HTML/PHP)
  └─→ http://localhost:3000 (ML API)


PRODUCTION (Recommended)
===========================
Linux Server
  ├─→ Nginx (Reverse Proxy)
  │   ├─→ :80 → Apache (:8080)
  │   ├─→ /api → PHP Backend
  │   └─→ /ml → Node.js (:3000)
  │
  ├─→ Apache/PHP (Port 8080)
  │   └─→ Main application
  │
  ├─→ Node.js (Port 3000)
  │   └─→ ML Server (behind proxy)
  │
  ├─→ MySQL 8
  │   ├─→ capstone DB
  │   └─→ ibalong_ai DB
  │
  ├─→ PM2
  │   └─→ Process manager for ML server
  │
  └─→ SSL Certificate
      └─→ HTTPS enabled
```

## Key Features by Component

```
TRAINING SCRIPT (train.js)
├─→ Generates 1000 synthetic records
├─→ Tests 3 model configurations
├─→ Saves best performing model
├─→ Tracks metrics (accuracy, loss)
└─→ Creates normalization parameters

SERVER (server.js)
├─→ HTTP API (5 endpoints)
├─→ Input validation
├─→ Tensor management
├─→ Database integration
├─→ Error handling
├─→ Performance logging
└─→ CORS support

DATABASE (schema.sql)
├─→ Predictions history
├─→ Festival event metadata
├─→ Per-festival predictions
├─→ Model performance metrics
├─→ API activity logs
└─→ Optimized indexes

PHP BRIDGE
├─→ Connects PHP ↔ Node.js
├─→ Fallback predictions
├─→ Error handling
├─→ Result caching
└─→ Automatic database storage
```

---

This architecture ensures:
✅ Separation of concerns (ML separate from main app)
✅ Easy maintenance & updates
✅ Scalability (can move ML to separate server)
✅ Reliability (fallback if ML server down)
✅ Performance (optimized data flow)
✅ Integration (works with existing system)
