# 🤖 Legazpi Festival ML Server - Setup & Usage Guide

## Overview

This is a TensorFlow-based machine learning server that predicts festival overcrowding and waste management metrics. It uses synthetic data generation for training and provides REST API endpoints for predictions.

### Features
- 🎯 **Overcrowding Classification**: Predicts if a festival will be overcrowded
- 🗑️ **Waste Prediction**: Estimates waste generation in kilograms
- 📊 **Batch Predictions**: Handle multiple predictions at once
- 📈 **Historical Analytics**: Track predictions over time
- 🧠 **High Accuracy**: Trained on synthetic festival data

---

## Prerequisites

- **Node.js** >= 14.0.0 (Download from https://nodejs.org/)
- **npm** (comes with Node.js)
- **MySQL/MariaDB** (XAMPP includes this)
- **Git Bash** (Windows users - recommended for npm install)

> **Windows Users:** See [WINDOWS_SETUP.md](WINDOWS_SETUP.md) if you get TensorFlow build errors during `npm install`!

---

## Installation Steps

### 1. Setup Database

```bash
# Open MySQL command line in XAMPP
# Or use MySQL Workbench

# Run the schema setup
mysql -u root -p < schema.sql

# If prompted for password, press Enter (default is empty in XAMPP)
```

Or manually create the database:
```sql
CREATE DATABASE ibalong_ai;
USE ibalong_ai;

-- Run all statements from schema.sql
```

### 2. Install Dependencies

```bash
cd backend/ml

# Install Node.js dependencies
npm install

# This will install:
# - @tensorflow/tfjs
# - @tensorflow/tfjs-node-cpu (pre-built, Windows-friendly)
# - Express.js
# - MySQL2
# - CORS
```

**Windows Users Note:** The package.json uses `@tensorflow/tfjs-node-cpu` which is pre-compiled for Windows. If you still encounter issues, see [WINDOWS_SETUP.md](WINDOWS_SETUP.md).

### 3. Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit .env with your database credentials (if needed)
# Default configuration works with XAMPP
```

### 4. Train the Models

Train the ML models with synthetic data:

```bash
npm run train
```

**Expected Output:**
```
🤖 Starting ML Model Training...

📊 Generating synthetic festival dataset...
📐 Normalizing data...
🎯 Training classification model (overcrowding detection)...

  Config 1/3: 32 units, LR 0.001
  ✓ Accuracy: 0.856 (85.6%)

  Config 2/3: 64 units, LR 0.001
  ✓ Accuracy: 0.892 (89.2%)

  Config 3/3: 32 units, LR 0.0005
  ✓ Accuracy: 0.878 (87.8%)

🏆 Best Classification Model:
  - Units: 64
  - Learning Rate: 0.001
  - Accuracy: 89.2%

✅ Classification model saved to backend/ml/best-overcrowding-model
🎯 Training regression model (waste prediction)...

  ✓ Mean Squared Error: 45.23

✅ Regression model saved to backend/ml/best-waste-model

🎉 All models trained and saved successfully!

✨ Training complete! Ready to run the ML server.
```

**Files Created:**
- `best-overcrowding-model/` - Overcrowding classification model
- `best-waste-model/` - Waste prediction regression model
- `normalization.json` - Data normalization parameters

### 5. Start the Server

```bash
npm start
```

**Server Output:**
```
🚀 ML Prediction Server running on port 3000
📊 Endpoints available:
   - POST   /predict        (single prediction)
   - POST   /predict-batch  (multiple predictions)
   - GET    /history        (prediction history)
   - GET    /stats          (statistics)
   - GET    /health         (health check)
```

The server is now running on `http://localhost:3000`

---

## API Endpoints

### 1. Single Prediction

**Endpoint:** `POST /predict`

**Request Body:**
```json
{
  "attendance": 20000,
  "venue_capacity": 50000,
  "weekend": 1,
  "is_free": 1,
  "duration_hours": 8,
  "food_stalls": 45,
  "weather": 0
}
```

**Parameters:**
- `attendance` (number): Expected number of visitors
- `venue_capacity` (number): Total venue capacity
- `weekend` (0/1): Is event on weekend?
- `is_free` (0/1): Is event free admission?
- `duration_hours` (number): Event duration in hours
- `food_stalls` (number): Number of food stalls/vendors
- `weather` (0/1): Bad weather expected?

**Response:**
```json
{
  "success": true,
  "prediction": {
    "is_overcrowded": false,
    "overcrowding_probability": "38.5%",
    "predicted_waste_kg": 8456,
    "input_data": {
      "attendance": 20000,
      "venue_capacity": 50000,
      "weekend": true,
      "is_free": true,
      "duration_hours": "8.0",
      "food_stalls": 45,
      "bad_weather": false
    }
  }
}
```

### 2. Batch Predictions

**Endpoint:** `POST /predict-batch`

**Request Body:**
```json
{
  "predictions": [
    {
      "attendance": 20000,
      "venue_capacity": 50000,
      "weekend": 1,
      "is_free": 1,
      "duration_hours": 8,
      "food_stalls": 45,
      "weather": 0
    },
    {
      "attendance": 35000,
      "venue_capacity": 40000,
      "weekend": 1,
      "is_free": 0,
      "duration_hours": 6,
      "food_stalls": 60,
      "weather": 1
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "total_predictions": 2,
  "results": [
    {
      "is_overcrowded": false,
      "overcrowding_probability": "38.5%",
      "predicted_waste_kg": 8456
    },
    {
      "is_overcrowded": true,
      "overcrowding_probability": "92.3%",
      "predicted_waste_kg": 15782
    }
  ]
}
```

### 3. Prediction History

**Endpoint:** `GET /history`

**Response:**
```json
{
  "success": true,
  "count": 25,
  "predictions": [
    {
      "id": 1,
      "attendance": 20000,
      "venue_capacity": 50000,
      "overcrowded": 0,
      "waste_prediction": 8456.23,
      "created_at": "2026-02-13T10:30:45.000Z"
    }
  ]
}
```

### 4. Statistics

**Endpoint:** `GET /stats`

**Response:**
```json
{
  "success": true,
  "statistics": {
    "total_predictions": 42,
    "overcrowded_count": 8,
    "avg_waste_kg": 9234.5,
    "avg_attendance": 22500,
    "max_attendance": 38000,
    "min_attendance": 8000,
    "overall_crowding_score": 0.45
  }
}
```

### 5. Health Check

**Endpoint:** `GET /health`

**Response:**
```json
{
  "status": "ML Server is running ✅"
}
```

---

## Integration with Frontend

### Making Predictions from JavaScript

```javascript
// Example: Make prediction from frontend
async function predictCrowding(eventData) {
  try {
    const response = await fetch('http://localhost:3000/predict', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        attendance: eventData.expectedAttendance,
        venue_capacity: eventData.venueCapacity,
        weekend: eventData.isWeekend ? 1 : 0,
        is_free: eventData.isFree ? 1 : 0,
        duration_hours: eventData.duration,
        food_stalls: eventData.foodStalls,
        weather: eventData.badWeather ? 1 : 0
      })
    });

    const result = await response.json();
    
    if (result.success) {
      console.log('Prediction:', result.prediction);
      displayPredictionResults(result.prediction);
    }
  } catch (error) {
    console.error('Prediction error:', error);
  }
}

// Display results in UI
function displayPredictionResults(prediction) {
  const status = prediction.is_overcrowded ? '⚠️ OVERCROWDED' : '✅ NORMAL';
  const wasteInfo = `Estimated waste: ${prediction.predicted_waste_kg} kg`;
  
  console.log(`${status} - ${wasteInfo}`);
  console.log(`Confidence: ${prediction.overcrowding_probability}`);
}
```

---

## Model Performance

### Overcrowding Classification Model
- **Architecture:** 3-layer neural network
- **Best Configuration:** 64 units, Adam optimizer (LR: 0.001)
- **Expected Accuracy:** 88-92%
- **Metrics:** Binary cross-entropy loss

### Waste Prediction Regression Model
- **Architecture:** 3-layer neural network
- **Optimizer:** Adam (LR: 0.001)
- **Loss Function:** Mean Squared Error
- **Expected Performance:** Good waste estimation within ±15% of actual

---

## Retraining Models

To retrain models with new data:

```bash
# Stop the server (Ctrl+C)

# Retrain
npm run train

# Start server again
npm start
```

---

## Development Mode

For development with automatic restart on file changes:

```bash
npm run dev
```

This uses `nodemon` to watch for changes and automatically restart the server.

---

## Troubleshooting

### Model Files Not Found
```
❌ Model not found at backend/ml/best-overcrowding-model/model.json
```
**Solution:** Run `npm run train` first

### Database Connection Error
```
❌ Failed to connect to database
```
**Solution:** 
1. Check MySQL is running in XAMPP
2. Verify DB credentials in `.env`
3. Ensure `ibalong_ai` database exists

### TensorFlow Build Error
```
npm error node-pre-gyp ERR! not ok
npm error * Building TensorFlow Node.js bindings
```
**Solution:**
1. Windows users: See [WINDOWS_SETUP.md](WINDOWS_SETUP.md) for detailed fix
2. Try: `npm install --legacy-peer-deps`
3. Clean reinstall:
```bash
# Remove old installation
rm -rf node_modules package-lock.json

# Reinstall with CPU version
npm install
```

### Port Already in Use
```
Error: listen EADDRINUSE: address already in use :::3000
```
**Solution:**
```bash
# Use different port
PORT=3001 npm start
```

---

## Project Structure

```
backend/ml/
├── train.js                    # Model training script
├── server.js                   # REST API server
├── package.json                # Dependencies
├── .env.example                # Environment template
├── schema.sql                  # Database schema
├── README.md                   # This file
├── normalization.json          # Data normalization params
├── best-overcrowding-model/    # Trained classification model
│   ├── model.json
│   └── weights.bin
└── best-waste-model/           # Trained regression model
    ├── model.json
    └── weights.bin
```

---

## Security Notes

1. **API Keys:** Add authentication in production (modify `server.js`)
2. **Environment Variables:** Use `.env` file for sensitive data
3. **Database User:** Create dedicated DB user with limited privileges
4. **CORS:** Currently allows all origins (restrict in production)

Example CORS restriction:
```javascript
app.use(cors({
  origin: 'http://localhost:80',
  credentials: true
}));
```

---

## Performance Tips

1. **Batch Predictions:** Use `/predict-batch` for multiple predictions (faster)
2. **Model Caching:** Models are loaded once at startup
3. **Database Indexes:** Schema includes indexes for fast queries
4. **Connection Pooling:** MySQL connection pool configured for efficiency

---

## Support & Documentation

- **TensorFlow.js Docs:** https://js.tensorflow.org/
- **Express.js Docs:** https://expressjs.com/
- **MySQL Documentation:** https://dev.mysql.com/doc/

---

## License

MIT License - Free for educational and commercial use

---

**Last Updated:** February 2026
**Version:** 1.0.0
