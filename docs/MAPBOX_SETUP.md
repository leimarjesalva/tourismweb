# 🗺️ Mapbox Setup Guide

Complete guide to configure Mapbox Navigation for the Route Optimization system.

## What is Mapbox?

Mapbox is a location-based services platform that provides:
- Interactive map rendering (Mapbox GL JS)
- Route visualization
- Real-time traffic data (optional premium feature)
- Geocoding & directions
- Custom styles and markers

## Step 1: Create Mapbox Account

1. Go to [mapbox.com](https://mapbox.com)
2. Click "Sign Up" 
3. Choose:
   - ✅ **Free tier** ($0/month, 50,000 map views)
   - or **Premium** for more features
4. Verify email
5. Create account

## Step 2: Get Access Token

1. After login, go to [Account Settings](https://account.mapbox.com/tokens)
2. Create new token:
   - Name: `Capstone-Route-Optimization`
   - Permissions: Add `maps:styles:read`, `maps:tiles:read`
   - Domain: `localhost` (for development)
3. Click "Create"
4. Copy the token (you'll need it)

**Token format:**
```
pk.eyJ1IjoieW91cnVzZXJuYW1lIiwiYSI6ImNhxxxxxxxxxxxxxxxx...
```

## Step 3: Configure Frontend

### Find the Configuration Line
File: `d:\xampp\htdocs\capstone\index.html`

Search for (around line 524):
```javascript
mapboxgl.accessToken = 'pk.eyJ1IjoiZXZlbnRtYXAiLCJhIjoiY20ydzA1N3JpMjA2dzJpcW5td2ttNnkwOSJ9.example';
```

### Replace with Your Token
```javascript
mapboxgl.accessToken = 'pk.eyJ1IjoieW91cnVzZXJuYW1lIiwiYSI6ImNhxxxxxxxxxxxxxxxx...';
```

**Save the file after editing.**

## Step 4: Verify Setup

### Test in Browser
1. Open http://localhost/capstone
2. Create/view Legazpi City event
3. Scroll to "Optimal Route Navigation" section
4. Should see:
   - Dark map of Legazpi City
   - Three colored route lines
   - Interactive markers

### Console Check (F12)
```javascript
// Type in console:
mapboxgl.accessToken  // Should show your token
routeMap              // Should show map object
routeMap.getStyle()   // Should show map style
```

## Step 5: Test Interactive Features

### Click on Routes
- Hover over route lines - should highlight
- Click route name - should show details

### Zoom/Pan
- Scroll to zoom
- Drag to pan across Legazpi City
- Double-click to center

### View Markers
- Red circle = event location
- Should show event name in popup

## Customization Options

### Change Map Style
In `index.html`, line ~512:
```javascript
// Change this line:
style: 'mapbox://styles/mapbox/dark-v11',

// To any of:
// 'mapbox://styles/mapbox/light-v11'
// 'mapbox://styles/mapbox/streets-v12'
// 'mapbox://styles/mapbox/outdoors-v12'
// 'mapbox://styles/mapbox/satellite-v9'
```

### Customize Route Colors
In `index.html`, line ~490:
```javascript
const color = idx === 0 ? '#00ff00' : (idx === 1 ? '#ffaa00' : '#ff6666');
// Change hex codes above to your colors
```

### Change Initial Zoom
Line ~514:
```javascript
zoom: 14  // 1-20, higher = more zoomed in
```

### Add Custom Markers
```javascript
new mapboxgl.Marker({ color: '#667eea' })
  .setLngLat([124.2044, 13.1597])  // longitude, latitude
  .setPopup(new mapboxgl.Popup().setHTML('<h3>My Location</h3>'))
  .addTo(routeMap);
```

## Usage Limits & Pricing

### Free Tier
- **50,000 map loads/month** = ~1,600/day ✅
- **Vector tiles:** Unlimited
- **Geocoding:** 600 queries/month
- **Traffic:** Not included

### Premium Features
- **Directions API:** $0.50 per 1000 requests
- **Real-time traffic:** Included in premium
- **Optimization API:** $2.50 per 1000 requests
- **Matrix API:** (Routing matrix)

### Current Usage
For Legazpi City event routes:
- Estimated daily loads: **100-500** (depends on events)
- Monthly cost: **FREE** (well under 50k limit)

## Troubleshooting

### "Mapbox GL not defined"
**Cause:** Script not loaded properly
**Fix:**
```javascript
// Ensure this in index.html head:
<link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
```

### "Token is not allowed"
**Cause:** 
1. Invalid token format
2. Token not active
3. Domain restrictions

**Fix:**
1. Go back to [Mapbox tokens page](https://account.mapbox.com/tokens)
2. Click token name
3. Check "URL Restrictions" = `localhost` for development
4. Verify token is enabled (toggle switch ON)

### Map Not Rendering
**Causes & Fixes:**
1. **Container not found:**
   ```javascript
   if (!document.getElementById('routeMapContainer')) {
     console.error('Container missing');
   }
   ```

2. **CSS conflict:**
   ```css
   #routeMapContainer {
     width: 100%;
     height: 400px;
     border-radius: 8px;
     overflow: hidden;
   }
   ```

3. **Zoom level invalid:**
   ```javascript
   zoom: Math.min(Math.max(zoom, 1), 20)  // Clamp 1-20
   ```

### Routes Not Showing
**Check:**
1. Routes data is valid array
2. Coordinates are [longitude, latitude]
3. Coordinates are within world bounds

```javascript
// Debug:
console.log('Routes:', routes);
console.log('First route coords:', routes[0]?.coordinates);
```

### Slow Performance
**Optimization:**
1. Reduce number of route points (simplify)
2. Disable layer animations
3. Use vector tiles instead of raster

## Production Deployment

### Generate Production Token
1. Go to [Mapbox tokens](https://account.mapbox.com/tokens)
2. Click "Create a token"
3. Set restrictions:
   - Scope: Maps SDK
   - Domains: Your production domain (e.g., capstone.example.com)
4. Save new token

### Update Configuration
1. In production, replace token:
   ```javascript
   mapboxgl.accessToken = 'PROD_TOKEN_HERE';
   ```

2. Update domain restrictions:
   - Development: `localhost`
   - Staging: `staging.capstone.com`
   - Production: `capstone.com`

### Monitor Usage
1. Dashboard: [account.mapbox.com](https://account.mapbox.com)
2. Check "Usage" tab
3. Set up billing alerts

## Advanced Features (Optional)

### Add Traffic Layer
```javascript
routeMap.addLayer({
  id: 'traffic',
  type: 'line',
  source: 'mapbox',
  'source-layer': 'traffic',
  paint: {
    'line-color': '#ff0000',
    'line-opacity': 0.7
  }
}, 'route-0');
```

### Add Geocoding
```javascript
// Convert address to coordinates
fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/Legazpi%20City.json?access_token=${mapboxgl.accessToken}`)
  .then(r => r.json())
  .then(data => {
    const coords = data.features[0].geometry.coordinates;
    console.log('Legazpi:', coords);
  });
```

### Add Directions API
```javascript
// Get actual route from Mapbox Directions API
fetch(`https://api.mapbox.com/directions/v5/mapbox/driving/${from};${to}?access_token=${mapboxgl.accessToken}`)
  .then(r => r.json())
  .then(data => {
    const route = data.routes[0];
    console.log('Duration:', route.duration);
    console.log('Distance:', route.distance);
  });
```

## Further Resources

- **Official Docs:** https://docs.mapbox.com/mapbox-gl-js/
- **API Reference:** https://docs.mapbox.com/mapbox-gl-js/api/
- **Examples:** https://docs.mapbox.com/mapbox-gl-js/examples/
- **Forum:** https://github.com/mapbox/mapbox-gl-js/discussions

## Summary

✅ **You're done!** The route map is now ready to use.

**Verification:**
1. Token configured: ✓
2. Map displays: ✓
3. Routes visible: ✓
4. Markers show: ✓

**Next:** Test with actual events using [ROUTE_OPTIMIZATION_TESTING.md](ROUTE_OPTIMIZATION_TESTING.md)

---

**Last Updated:** February 13, 2025  
**Mapbox GL Version:** v2.15.0  
**Free Tier Status:** ✅ Active
