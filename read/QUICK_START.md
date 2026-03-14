# 🚀 Quick Start Guide - ML Server Setup

## 5-Minute Setup

### Step 1: Open Terminal in ML folder

**On Windows:**
- Open **GitBash** (recommended) or Command Prompt
- Navigate to: `d:\xampp\htdocs\capstone\backend\ml`

```bash
cd d:/xampp/htdocs/capstone/backend/ml
```

**On Mac/Linux:**
```bash
cd backend/ml
```

### Step 2: Install Dependencies
```bash
npm install
```

Expected: ~2-3 minutes (installs TensorFlow and other packages)

**⚠️ Windows Users:** If you get TensorFlow build errors, see [WINDOWS_SETUP.md](WINDOWS_SETUP.md) for the fix!

### Step 3: Setup Database
```bash
# Option A: Using terminal
mysql -u root < schema.sql

# Option B: Using MySQL Workbench
# Open schema.sql file and execute all commands
```

### Step 4: Train Models
```bash
npm run train
```

Expected: ~3-5 minutes. Output should show model accuracy ~85-90%

### Step 5: Start Server
```bash
npm start
```

**Success!** Server is running on `http://localhost:3000`

---

## Testing the Server

### Test 1: Health Check
```bash
# In new terminal or browser
curl http://localhost:3000/health
```

**Expected Response:**
```json
{"status":"ML Server is running ✅"}
```

### Test 2: Make a Prediction
```bash
curl -X POST http://localhost:3000/predict \
  -H "Content-Type: application/json" \
  -d '{
    "attendance": 20000,
    "venue_capacity": 50000,
    "weekend": 1,
    "is_free": 1,
    "duration_hours": 8,
    "food_stalls": 45,
    "weather": 0
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "prediction": {
    "is_overcrowded": false,
    "overcrowding_probability": "40.0%",
    "predicted_waste_kg": 8500
  }
}
```

---

## Using from PHP

```php
<?php
// Include the bridge
require_once 'MLServerBridge.php';

// Initialize bridge
$bridge = new MLServerBridge($db);

// Check if server is running
if ($bridge->isServerRunning()) {
    // Get prediction
    $result = $bridge->predictFestivalCrowding([
        'attendance' => 30000,
        'venue_capacity' => 50000,
        'weekend' => 1,
        'is_free' => 0,
        'duration_hours' => 8,
        'food_stalls' => 50,
        'weather' => 0
    ]);
    
    if ($result['success']) {
        echo "Overcrowded: " . ($result['prediction']['is_overcrowded'] ? 'Yes' : 'No');
        echo "Waste: " . $result['prediction']['predicted_waste_kg'] . " kg";
    }
} else {
    echo "ML Server is not running";
}
?>
```

---

## Using from JavaScript

```javascript
async function getPrediction() {
    const response = await fetch('http://localhost:3000/predict', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            attendance: 25000,
            venue_capacity: 50000,
            weekend: 1,
            is_free: 1,
            duration_hours: 8,
            food_stalls: 40,
            weather: 0
        })
    });
    
    const prediction = await response.json();
    console.log(prediction);
}

getPrediction();
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `npm: command not found` | Install Node.js from nodejs.org |
| `unable to connect to MySQL` | Start MySQL in XAMPP Control Panel |
| `Module not found` | Run `npm install` again |
| `Port 3000 in use` | `PORT=3001 npm start` |
| `Models not found` | Run `npm run train` first |

---

## Directory Structure After Setup

```
backend/ml/
├── train.js
├── server.js  
├── package.json
├── README.md
├── .env.example
├── schema.sql
├── normalization.json          ← Created by training
├── best-overcrowding-model/    ← Created by training
└── best-waste-model/           ← Created by training
```

---

## Next Steps

1. **Test API endpoints** (see "Testing the Server" above)
2. **Integrate with admin dashboard** using `MLServerBridge.php`
3. **Monitor performance** via `/stats` endpoint
4. **Retrain models** as you get real data with `npm run train`

---

## Production Deployment

Before going live:

1. Set `NODE_ENV=production` in `.env`
2. Add API authentication
3. Use reverse proxy (Nginx)
4. Enable HTTPS
5. Set up PM2 for process management:
   ```bash
   npm install -g pm2
   pm2 start server.js --name "ml-server"
   ```

---

**Questions?** Check `README.md` for detailed documentation.
