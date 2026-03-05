# 🗺️ Route Navigation - Troubleshooting Guide

## Issue: Route Navigation Not Showing

The Route Navigation feature requires several components to work together. This guide helps you diagnose and fix the issue.

## ✅ Quick Checklist

- [ ] Database has events (check via phpMyAdmin)
- [ ] At least one event has "Legazpi" in the location field
- [ ] At least one event has ML predictions
- [ ] No JavaScript errors in browser console (F12)
- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running

## 🔍 Step-by-Step Diagnosis

### Step 1: Verify Database Data

**Via phpMyAdmin:**
1. Open http://localhost/phpmyadmin
2. Select `ibalong_ai` database
3. Click `events` table
4. Check:
   - ✓ Row exists with location containing "Legazpi"
   - ✓ Has values in `title`, `location`, `datetime`

**Via MySQL Command Line:**
```bash
mysql -u root ibalong_ai
SELECT id, title, location FROM events LIMIT 5;
```

**Expected output:**
```
| id | title | location |
|----|-------|----------|
| 1  | Festival | Legazpi City, Albay |
```

### Step 2: Check API Response

**Test the API directly:**

**Using Browser (paste in address bar):**
```
http://localhost/capstone/backend/api.php?action=list_events
```

Should return JSON with object like:
```json
{
  "success": true,
  "events": [
    {
      "id": 1,
      "title": "Event Name",
      "location": "Legazpi City",
      "prediction": {
        "attendance": 5000,
        "waste_prediction": 3000,
        "overcrowding_probability": 0.4
      }
    }
  ]
}
```

**Test route API:**
```
http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=8000
```

Should return:
```json
{
  "success": true,
  "routes": [
    {"name": "Northern Bypass Route", "distance": 8.5, ...},
    ...
  ]
}
```

### Step 3: Check Browser Console (F12)

Open DevTools and check for messages:

**Good signs:**
```
📍 Fetching routes for: Legazpi City
Route API Response: {success: true, routes: [...]}
Routes received, initializing map...
```

**Bad signs:**
```
❌ Error loading routes: ...
Route fetch error: ...
Uncaught ReferenceError: eventId is not defined
```

### Step 4: Verify HTML Elements

In browser console, run:
```javascript
// Should return element
document.getElementById('routeMapContainer')

// Should return element  
document.getElementById('routeSuggestions')

// Should return array
document.querySelectorAll('[id*="route"]')
```

If these return `null`, the HTML elements are missing.

## 🔧 Common Issues & Solutions

### Issue: "Route optimization only available for Legazpi City events"

**Cause:** Event location doesn't contain "Legazpi"  
**Solution:**
1. Edit event location to include "Legazpi"
2. Or create new test event:
   - Title: "Test Event"
   - Location: **"Legazpi City, Albay"** (must contain word "Legazpi")

### Issue: No events appear in Community Events section

**Cause:** Database empty or API not returning data  
**Solution:**
```bash
# Create test event via SQL
mysql -u root ibalong_ai -e "
INSERT INTO events (title, location, capacity, datetime) 
VALUES ('Community Festival', 'Legazpi City, Albay', 10000, '2026-03-15 14:00:00');
"

# Check it was created
mysql -u root ibalong_ai -e "SELECT * FROM events LIMIT 1;"
```

### Issue: Routes show but no predictions (blank charts)

**Cause:** ML predictions not generated for events  
**Solution:**
1. Ensure ML server is running on port 3000
2. Generate predictions via API:
```bash
curl -X POST http://localhost:3000/predict \
  -H "Content-Type: application/json" \
  -d '{
    "attendance": 5000,
    "venue_capacity": 10000,
    "weekend": 1,
    "is_free": 1,
    "duration_hours": 5,
    "food_stalls": 50,
    "weather": 0.8
  }'
```

### Issue: "eventId is not defined" error

**Status:** ✅ FIXED (Updated index.html)  
If you still see this error:
1. Hard refresh page: `Ctrl+Shift+R` (or `Cmd+Shift+R`)
2. Clear browser cache
3. Check you have latest index.html

### Issue: Route map shows placeholder SVG instead of Mapbox

**Cause:** Mapbox token not configured  
**Solution (Optional - Placeholder works fine):**
1. Get Mapbox token at https://mapbox.com
2. Edit `index.html` line ~524
3. Replace: `mapboxgl.accessToken = '...'`
4. Reload page

Note: System works fine with placeholder visualization for now!

### Issue: CORS errors or 404 errors

**Cause:** API path incorrect or XAMPP not running  
**Solution:**
1. Start XAMPP Apache & MySQL
2. Check path is correct: http://localhost/capstone/
3. Verify backend folder exists at: `d:\xampp\htdocs\capstone\backend\`

## 🧪 Test Page

Open this page for automated testing:
```
http://localhost/capstone/test_routes.html
```

Features:
- Check events database
- Test route API
- Visualize sample routes
- View system status

## 📝 Required Database Structure

### Events Table
```sql
CREATE TABLE events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255),
  location VARCHAR(255),
  datetime DATETIME,
  capacity INT,
  COALESCE(lat, 13.1597) DECIMAL(10,8),
  COALESCE(lng, 124.2044) DECIMAL(10,8)
);
```

### ML Predictions
```sql
CREATE TABLE ml_predictions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  event_id INT,
  attendance INT,
  waste_prediction FLOAT,
  overcrowding_probability FLOAT,
  FOREIGN KEY (event_id) REFERENCES events(id)
);
```

## 📊 Data Flow

```
1. Page loads
   ↓
2. loadCommunityEvents() fetches events
   ↓
3. renderCommunityCharts() processes first event with prediction
   ↓
4. Checks if location contains "Legazpi"
   ↓
5. Fetches routes from API
   ↓
6. initializeRouteMap() displays visualization
   ↓
7. displayRouteSuggestions() shows recommendation list
```

If any step fails, routes won't display.

## 💡 Pro Tips

### Check All Console Messages
1. Open DevTools (F12)
2. Go to Console tab
3. Expected messages:
   ```
   📍 Fetching routes for: Legazpi City
   Route API Response: Object {success: true, routes: Array(3)}
   Routes received, initializing map...
   ```

### Test with curl
```bash
# Windows
curl -s "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=8000"

# Then check response
```

### Verify Event IDs
```bash
# Get list of all events
curl -s "http://localhost/capstone/backend/api.php?action=list_events" | grep -E '"id"|"location"'
```

## 🎯 Success Indicators

✅ Routes section shows:
- Interactive map visualization (even if placeholder)
- 3 route suggestions below with:
  - 🟢 BEST Route
  - 🟠 ALTERNATE Route  
  - 🔴 AVOID Route
- Distance, duration, reasoning for each

✅ Hover tooltips work (if Mapbox enabled)

✅ Routes update when crowd level changes

## 📞 Still Having Issues?

1. **Document the error:** What exactly do you see?
2. **Check console:** What does F12 console show?
3. **Verify database:** Are events actually in database?
4. **Test API directly:** Use curl or browser to test endpoint
5. **Check latest code:** Did you save the file? Page cached?

---

**Created:** February 13, 2025  
**Fixed in:** index.html (eventId → eventWithPred.id)  
**Status:** Working with Live Fallback Visualization
