# Legazpi Explorer AI/ML Architecture

## Overview
The Legazpi Explorer capstone project implements a **dual AI architecture** combining:

1. **NLP Analytics Pipeline** - Analyzes tourist feedback (sentiment, aspects, keywords)
2. **Predictive ML Server** - Forecasts event overcrowding, waste generation, route optimization

## High-Level Architecture Diagram

```mermaid
graph TB
    subgraph \"Frontend (React/HTML/JS)\"
        A[Admin Dashboard]
        B[Feedback Forms]
        C[NLP Analytics UI]
    end
    
    subgraph \"Backend APIs (PHP)\"
        D[nlp_analytics_api.php]
        E[ml_api_client.php]
        F[MLServerBridge.php]
        G[ratings_api.php]
    end
    
    subgraph \"NLP Pipeline (Python)\"
        H[nlp_analyzer.py]
        I[NLTK + spaCy + scikit-learn]
        J[Sentiment Analysis<br/>Keyword Extraction<br/>Aspect Detection]
    end
    
    subgraph \"ML Prediction Server (Node.js/TF.js)\"
        K[server.js]
        L[best-overcrowding-model]
        M[best-waste-model]
        N[Normalization]
        O[Predict: /predict, /optimize-route]
    end
    
    subgraph \"Database (MySQL)\"
        P[feedback table]
        Q[predictions table]
        R[shops/products/events]
    end
    
    %% Data Flows
    B -->|submit_feedback| P
    P -->|fetch feedback| H
    H -->|process| I
    I -->|results| D
    D -->|JSON analytics| C
    
    A -->|request| E
    E -->|HTTP| K
    K -->|load models| L
    K -->|load models| M
    K -->|normalize| N
    K -->|predict| O
    O -->|save| Q
    O -->|return JSON| E
    
    R -->|features| K
    
    C -->|display| D
    A -->|display| F
```

## Component Details

### 1. NLP Analytics Pipeline
**Purpose**: Process tourist feedback for actionable insights.

**Tech Stack**:
- Python 3.7+ with NLTK, spaCy (en_core_web_sm), scikit-learn
- VADER sentiment lexicon
- PHP API wrapper (`nlp_analytics_api.php`)

**Capabilities**:
```
- Sentiment: Positive/Neutral/Negative scores
- Keywords: Top terms with frequency  
- Aspects: Service/Quality/Price detection
- Summarization: Feedback condensation
- Topic modeling: Theme discovery
```

**Endpoints**:
```
GET /nlp_analytics_api.php?action=analyze_shop_feedback&shop_id=1
GET /nlp_analytics_api.php?action=analyze_all_feedback  
GET /nlp_analytics_api.php?action=get_analytics_summary
POST /nlp_analytics_api.php?action=analyze_sentiment
```

### 2. ML Prediction Server
**Purpose**: Real-time predictions for tourism operations.

**Tech Stack**:
- Node.js + Express
- TensorFlow.js (CPU backend)
- MySQL2 for prediction logging

**Models**:
| Model | Type | Input Features | Output | Loss/Metric |
|-------|------|----------------|--------|-------------|
| Overcrowding | Classification | 7 features | Binary (0/1) | Binary Crossentropy / Accuracy |
| Waste Prediction | Regression | 7 features | kg waste | Mean Squared Error |

**Features** (7 total):
```
attendance, venue_capacity, weekend (0/1), 
is_free (0/1), duration_hours, food_stalls, weather (0/1)
```

**Endpoints**:
```
POST /predict (single)
POST /predict-batch (bulk)
POST /optimize-route (Legazpi-specific)
GET /history, /stats
```

### 3. Data Flow
```
1. Feedback → MySQL → NLP Pipeline → Analytics Dashboard
2. Events/Shops → ML Server → Predictions → Admin Alerts
3. Real-time: Frontend → PHP API → ML Server → Results
```

## Deployment Architecture

```
Capstone (XAMPP)
├── frontend/          # HTML/JS/CSS
├── backend/           # PHP APIs
│   ├── ml/           # Node.js ML Server (port 3000)
│   └── nlp_analyzer.py
├── MySQL (ibalong_ai)
└── uploads/
```

## Integration Points
- **Admin Dashboard**: Real-time NLP charts + ML predictions
- **Feedback System**: Auto-triggers NLP analysis
- **Event Planning**: ML-powered route optimization

## Scalability Considerations
- ML Server: Docker-ready (nixpacks.toml)
- NLP: Batch processing for large feedback volumes
- Caching: Prediction history + NLP summaries in DB
