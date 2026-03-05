# 🧪 Route Optimization Testing Guide

Quick steps to test the new route optimization system.

## Prerequisites

1. **ML Server Running**
   ```bash
   cd d:\xampp\htdocs\capstone\backend\ml
   npm start
   ```
   Should see: `🚀 ML Prediction Server running on port 3000`

2. **XAMPP Running**
   - Apache & MySQL must be active
   - Accessible at http://localhost

3. **Event in Database**
   - Must be located in "Legazpi City"
   - Should have crowd prediction (ml_predictions table)

## Test 1: API Endpoint

### Test Route Suggestion API
```bash
# In PowerShell/Bash:
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=8000"
```

### Expected Response
```json
{
  "success": true,
  "event_id": 1,
  "event": {
    "id": 1,
    "title": "...",
    "location": "Legazpi City"
  },
  "routes": [
    {
      "name": "Northern Bypass Route",
      "distance": 8.5,
      "duration": 12,
      "reason": "..."
    }
  ]
}
```

### Test with Different Crowd Levels
```bash
# Small crowd
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=1500"

# Medium crowd
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=5000"

# Large crowd  
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=12000"
```

## Test 2: Frontend Display

### Steps
1. Open http://localhost/capstone
2. Scroll to "Community Events & Updates" section
3. Find event in Legazpi City
4. Look for "Optimal Route Navigation" card
5. Should show:
   - Interactive map of Legazpi City
   - 3 colored routes (green, orange, red)
   - Route suggestions box below

### Expected Behavior
- **Map loads** with Legazpi City centered at 13.1597°N, 124.2044°E
- **Green route** (Northern Bypass) shown as primary recommendation
- **Routes update** if crowd level changes
- **Clicking route** shows details (distance, duration, reason)

### Mobile Testing
1. Open on mobile device or responsive mode
2. Check map displays correctly at smaller widths
3. Verify route list is scrollable

## Test 3: Mapbox Integration

### Setup Access Token (Required)
1. Get token from https://mapbox.com/account/tokens
2. Open `d:\xampp\htdocs\capstone\index.html`
3. Find line ~524: `mapboxgl.accessToken = '...'`
4. Replace with your actual token
5. Save file

### Test Map Display
- Routes should appear as colored lines
- Red circle = event location
- Hover over route = should show name
- Map should be dark themed

### Troubleshoot Map Issues
```javascript
// In browser console (F12):
console.log(mapboxgl); // Should show object
console.log(routeMap); // Should show map instance
```

## Test 4: ML Route Optimization

### Train New Model (Optional)
```bash
cd d:\xampp\htdocs\capstone\backend\ml
node train_route_model.js
```

Expected output:
```
🚗 ROUTE OPTIMIZATION ML MODEL TRAINER
...
Epoch 10: loss = 0.5234, accuracy = 0.8550
Epoch 100: loss = 0.2341, accuracy = 0.9430
✓ Model saved to best-route-model
```

### Test ML Server Endpoint
```bash
curl -X POST http://localhost:3000/optimize-route \
  -H "Content-Type: application/json" \
  -d '{"crowdLevel": 8000, "eventTime": 14, "eventDay": 3, "weather": 0.8}'
```

Response should include all 3 routes ranked by score.

## Test 5: Different Scenarios

### Scenario 1: Small Weekend Event (Good Weather)
```bash
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=1500"
```
✅ Expected: Main Street Route might be recommended for speed

### Scenario 2: Large Festival (Good Weather)
```bash
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=10000"
```
✅ Expected: Northern Bypass Route strongly recommended

### Scenario 3: Concert Evening Peak Hours
```bash
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=7000"
```
✅ Expected: Bypass route due to time of day

### Scenario 4: Non-Legazpi Event
```bash
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=2&crowd_level=5000"
```
⚠️ Expected: Error message "Route optimization only available for Legazpi City"

## Test 6: Database Integration

### Check Route Logs (Optional - requires schema update)
```sql
SELECT * FROM route_suggestions ORDER BY created_at DESC LIMIT 10;
```

### Verify Event Locations
```sql
SELECT id, title, location FROM events LIMIT 5;
```

Output should show at least one event with "Legazpi" in location.

## Test 7: Performance

### Measure Response Time
```bash
# Using PowerShell
$start = Get-Date
curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=5000" | Out-Null
$end = Get-Date
Write-Host "Response time: $(($end - $start).TotalMilliseconds)ms"
```

✅ Expected: **<500ms** total

### Check Map Load Time
1. Open DevTools (F12)
2. Go to Network tab
3. Load http://localhost/capstone
4. Look for `mapbox-gl.js` load time
5. Total route section load: **<3 seconds**

### Monitor ML Server
```bash
cd d:\xampp\htdocs\capstone\backend\ml
npm start
# Should show stable memory usage, <100MB
```

## Common Issues & Fixes

### Issue: "Route optimization only available for Legazpi City"
**Fix:** Update event location to include "Legazpi"
```sql
UPDATE events SET location = 'Legazpi City - Festival Grounds' WHERE id = 1;
```

### Issue: Map Not Displaying
**Possible causes:**
1. Missing Mapbox token
2. Invalid token
3. Token usage exceeded
4. Browser blocking API calls

**Fix:** 
```javascript
// Check in console:
if (!window.mapboxgl) console.error('Mapbox not loaded');
if (!mapboxgl.accessToken) console.error('No access token set');
```

### Issue: Routes Always Show Same Recommendation
**Fix:** Check ML server is running and providing varied predictions

### Issue: Slow Response Times
**Fix:** 
- Ensure MySQL is responsive
- Check ML server CPU usage
- Verify network latency

## Success Checklist

- [ ] API returns routes for Legazpi events
- [ ] API returns error for non-Legazpi events
- [ ] Frontend map loads on event page
- [ ] Routes display as colored lines
- [ ] Route suggestions show below map
- [ ] Routes update when crowd level changes
- [ ] Mobile view works responsively
- [ ] Mapbox token configured
- [ ] Response time <500ms
- [ ] All 3 routes appear in different scenarios

## Next Steps

If all tests pass:
1. ✅ System is ready for production
2. ✅ Deploy to event management system
3. ✅ Monitor real usage and feedback
4. ✅ Plan Phase 2 enhancements

For issues, check [ROUTE_OPTIMIZATION.md](ROUTE_OPTIMIZATION.md) for full documentation.

---

**Test Date:** February 13, 2025  
**Tested By:** Development Team  
**Status:** Ready for QA
