# 🎉 Community Events & Updates - Complete Setup Guide

## Quick Fix Summary

**Issues Found & Fixed:**
- ✅ Duplicate function declaration (breaking JS)
- ✅ Undefined `eventId` variable (now uses `eventWithPred.id`)
- ✅ Missing database seed data
- ✅ No error handling for empty tables

---

## 🚀 Quick Start (3 Steps)

### Step 1: Seed Database with Sample Events
Open in browser:
```
http://localhost/capstone/setup_community_events.html
```

Click: **🌱 Seed Database with Test Events**

This will create 3 sample events in Legazpi City with ML predictions.

### Step 2: Verify Everything Works
On same page, click buttons in order:
1. **🔍 Test Events API** - Should show events
2. **🎯 Test Frontend Function** - Should show predictions and routes
3. **🌐 Open Community Events Page** - Visit main page

### Step 3: View Community Events Section
Open main page and scroll down:
```
http://localhost/capstone/index.html
```

Look for: **📊 Visitor & Waste Predictions** section

---

## 🔍 What Should Display

### Community Events Cards (Top)
```
[Event Image]
Festival Name | Legazpi City, Albay
Mar 15, 2026 · 02:00 PM
     
⚠️ MEDIUM RISK     (Risk badge)

📊 Visitors: 7,000
🗑️ Waste: 6,000 kg
```

### Prediction Charts (Below Cards)
```
📊 Visitor & Waste Predictions

[4 Charts in 2x2 Grid:]
⏱ Hour-by-Hour Visitor Prediction    | 🎟 Festival Phase Comparison
[Line Chart]                           | [Bar Chart]

🗑️ Predicted Garbage Collection      | 🗺️ Optimal Route Navigation
[Doughnut Chart]                       | [SVG Map + Route Suggestions]
  - Organic                            | 🟢 Northern Bypass
  - Recyclable                         | 🟠 Main Street
  - Hazardous                          | 🔴 Downtown
  - General
```

---

## 🛠 If Something Still Doesn't Work

### Issue: "No events appear"

**Solution:**
1. Verify XAMPP is running (Apache + MySQL)
2. Open setup page: http://localhost/capstone/setup_community_events.html
3. Click "🌱 Seed Database with Test Events"
4. Check console (F12) for errors

### Issue: "Charts show but routes don't"

**Solution:**
1. Open browser console: Press F12
2. Look for "📍 Fetching routes" message
3. Check if location contains "Legazpi"
4. Try refreshing page: Ctrl+Shift+R

### Issue: "Page loads white/blank"

**Solution:**
1. Press Ctrl+Shift+R (hard refresh)
2. Open console (F12)
3. Check for red error messages
4. Look for "Community Events & Updates" section
5. Try setup page first to verify database

### Issue: "API returns error"

**Solution:**
```javascript
// Test in browser console:
fetch('backend/api.php?action=list_events')
  .then(r => r.json())
  .then(data => console.log(data))
  .catch(e => console.error(e))
```

Should show events. If error, check:
- XAMPP MySQL is running
- Database `ibalong_ai` exists
- Table `events` exists (check phpMyAdmin)

---

### Issue: "Can't upload video / file too large"

**Solution:**
- A file larger than `upload_max_filesize` or `post_max_size` will fail silently. Open your `php.ini` (or use `phpinfo()` to locate it) and bump both values to at least `512M` (for example):
  ```ini
  post_max_size = 512M
  upload_max_filesize = 512M
  max_execution_time = 300
  max_input_time = 300
  ```
  Restart Apache after editing.
- The JavaScript front‑end already allows up to **500 MB** and shows a warning if a user tries to send more.
- The backend scripts (`backend/upload_event_image.php` and `backend/upload_festival_image.php`) now attempt to raise limits at runtime and log the effective server setting; you only need to raise the php.ini limits if uploads still fail.
- For debugging, inspect the server error log (look for `DEBUG: upload_max_filesize=` entries).


---

## 📝 Database Requirements

Your database must have:

### events table
```sql
CREATE TABLE events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255),
  location VARCHAR(255),
  capacity INT,
  datetime DATETIME,
  image VARCHAR(500)
);
```

### ml_predictions table
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

### Sample Data
```sql
INSERT INTO events VALUES
(1, 'Festival', 'Legazpi City', 10000, '2026-03-15 14:00', '...'),
(2, 'Gathering', 'Legazpi City Downtown', 5000, '2026-04-20 10:00', '...');

INSERT INTO ml_predictions VALUES
(1, 1, 7000, 6000, 0.35),
(2, 2, 3500, 3000, 0.25);
```

---

## ✅ Verification Checklist

- [ ] XAMPP Apache running (green)
- [ ] XAMPP MySQL running (green)
- [ ] Database `ibalong_ai` exists
- [ ] Tables: `events`, `ml_predictions` exist
- [ ] Sample events seeded (run setup page)
- [ ] API endpoint works: http://localhost/capstone/backend/api.php?action=list_events
- [ ] Shows JSON with `success: true`
- [ ] Events have location containing "Legazpi"
- [ ] Events have prediction objects
- [ ] Main page loads: http://localhost/capstone
- [ ] Scroll down shows "Community Events & Updates"
- [ ] Charts appear (line, bar, doughnut, routes)
- [ ] Route map shows (SVG visualization)

---

## 📊 Testing Workflow

**Fastest way to verify everything:**

```bash
# 1. Open setup page in browser
http://localhost/capstone/setup_community_events.html

# 2. Click: Seed Database
# (Wait for ✅ message)

# 3. Click: Test Events API
# (Should show Legazpi events)

# 4. Click: Test Frontend Function
# (Should show all charts working)

# 5. Click: Open Community Events Page
# (View full page with all visualizations)
```

**Expected output:**
```
✅ Seeded database with 3 events
✅ API returned 3 events
✅ 3 Legazpi City events found
✅ 3 events have ML predictions
✅ Found event with prediction: Legazpi City Festival 2026
✅ Predicted attendance: 7,000 visitors
✅ Waste prediction: 6,000 kg
✅ Crowding probability: 35%
✅ Event in Legazpi City - Routes will display
✅ Route API returned 3 routes
  🟢 Northern Bypass: 8.5km, 12min
  🟠 Main Street: 6.2km, 18min  
  🔴 Downtown: 5.8km, 35min
✅ Frontend should work correctly!
```

---

## 🎯 Component Breakdown

### Community Events Section Includes:

**1. Event Cards** (Top grid)
- Event image
- Title & location
- Date & time
- Risk level badge
- Prediction stats

**2. Hour-by-Hour Chart** (Line)
- 24-hour visitor distribution
- Peak hours highlighted
- Tooltip on hover

**3. Festival Phase Chart** (Bar)
- Before/During/After visitor counts
- Capacity indicators
- Phase breakdown

**4. Garbage Collection Chart** (Doughnut)  
- Organic waste (45%)
- Recyclable (30%)
- Hazardous (15%)
- General (10%)

**5. Route Navigation** (SVG/Mapbox)
- 3 color-coded routes
- Distance & time for each
- Reason for recommendation
- Opens interactive map (if Mapbox enabled)

---

## 🔗 Related Files

**Setup & Testing:**
- [setup_community_events.html](setup_community_events.html) - Setup wizard & diagnostics
- [test_routes.html](test_routes.html) - Route visualization tester
- [test_routes.bat](test_routes.bat) - Command-line API tester

**Backend:**
- [backend/api.php](backend/api.php) - API endpoints
- [backend/seed_db.php](backend/seed_db.php) - Database seeder
- [backend/seed_events.sql](backend/seed_events.sql) - SQL seed data

**Frontend:**
- [index.html](index.html) - Main page (renderCommunityCharts function)
- [style.css](style.css) - Styling

**Documentation:**
- [ROUTE_OPTIMIZATION.md](ROUTE_OPTIMIZATION.md) - Routes system details
- [ROUTE_TROUBLESHOOTING.md](ROUTE_TROUBLESHOOTING.md) - Route fixes

---

## 💡 Pro Tips

### View Console Logs
1. Press F12 in browser
2. Go to "Console" tab
3. Look for these messages:
   ```
   📍 Fetching routes for: Legazpi City
   Route API Response: {success: true, routes: Array(3)}
   Routes received, initializing map...
   ```

### Test API from URL Bar
```
http://localhost/capstone/backend/api.php?action=list_events
http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=8000
```

### Force Reload (clear cache)
- **Windows:** Ctrl + Shift + R
- **Mac:** Cmd + Shift + R
- **Firefox:** Ctrl + Shift + Delete

### Check Database
1. Open http://localhost/phpmyadmin
2. Select `ibalong_ai`
3. Click `events` table
4. Should have 3+ rows

---

## 📞 Quick Fixes

| Problem | Fix |
|---------|-----|
| Page loads but no events | Run setup page, seed database |
| Charts don't show | Refresh page (F5), check console |
| Routes show message instead of map | Normal - SVG placeholder until Mapbox token configured |
| API returns error | Check XAMPP MySQL is running |
| Nothing appears at all | Check browser console for JavaScript error |
| Page blank/white | XAMPP not running - start Apache + MySQL |

---

## 🎉 Success Indicators

✅ Events appear in cards
✅ Charts render below events
✅ All 4 chart types visible
✅ Route suggestions box shows 3 routes
✅ No red errors in console
✅ Page responsive on mobile

**You're all set!** 🚀

---

**Last Updated:** February 13, 2026  
**Fixed:** Duplicate functions, undefined variables, missing database seed  
**Status:** Fully functional with live fallback visualization
