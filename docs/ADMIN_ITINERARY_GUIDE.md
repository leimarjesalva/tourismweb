# Admin Itinerary Management - Implementation Guide

## ✅ What Has Been Implemented

### 1. Backend API Endpoints (backend/api.php)
The following new API endpoints have been added for admin-only operations:

#### Destinations Management:
- **add_itinerary_destination** - Add a new custom destination
  - Parameters: name, category, latitude, longitude, description, activities, image
  - Validates: Coordinates must be within Legazpi City
  
- **list_itinerary_destinations** - Get all custom destinations
  - Returns: Array of destination objects with all fields
  
- **edit_itinerary_destination** - Update an existing destination
  - Parameters: id, name, category, latitude, longitude, description, activities, image
  
- **delete_itinerary_destination** - Remove a destination
  - Parameters: id

#### Hotels Management:
- **add_itinerary_hotel** - Add a new custom hotel
  - Parameters: name, category, latitude, longitude, rating, ratePerNight, phone, address, description, features, image
  - Validates: Coordinates must be within Legazpi City
  
- **list_itinerary_hotels** - Get all custom hotels
  - Returns: Array of hotel objects with all fields
  
- **edit_itinerary_hotel** - Update an existing hotel
  - Parameters: id, name, category, latitude, longitude, rating, ratePerNight, phone, address, description, features, image
  
- **delete_itinerary_hotel** - Remove a hotel
  - Parameters: id

### 2. Database Tables (backend/schema.sql)
Two new tables have been created:

#### itinerary_destinations
```sql
id, name, category, latitude, longitude, description, activities, image, created_at, updated_at
```

#### itinerary_hotels
```sql
id, name, category, latitude, longitude, rating, rate_per_night, phone, address, description, features, image, created_at, updated_at
```

### 3. Admin Dashboard UI (admin_dashboard.html)
- **New Tab**: "✈️ Itinerary System" added to admin sidebar
  - Form to add/edit destinations with validation
  - Form to add/edit hotels with validation
  - List views showing all custom destinations and hotels
  - Edit and Delete buttons for each entry

### 4. JavaScript Functions (admin_dashboard.html)
- `loadItineraryDestinations()` - Fetch and display all destinations
- `loadItineraryHotels()` - Fetch and display all hotels
- `editItineraryDestination(id)` - Load destination for editing
- `editItineraryHotel(id)` - Load hotel for editing
- `deleteItineraryDestination(id)` - Remove destination with confirmation
- `deleteItineraryHotel(id)` - Remove hotel with confirmation
- `resetItineraryDestinationForm()` - Clear destination form
- `resetItineraryHotelForm()` - Clear hotel form
- `loadItineraryManagementTab()` - Load data when tab is opened

## 🚀 How to Use

### Step 1: Set Up Database Tables
Run the setup script to create the tables:
```
Visit: http://localhost/capstone/backend/setup_itinerary_tables.php
```
You should see a success message confirming table creation.

### Step 2: Access Admin Panel
1. Log in to the admin dashboard at `/admin_dashboard.html`
2. Click "✈️ Itinerary System" in the sidebar
3. You'll see two sections:
   - ➕ Add Destination to Itinerary (top)
   - ➕ Add Hotel to Itinerary (middle)

### Step 3: Add Custom Destination
1. Fill in the destination form:
   - **Destination Name*** (required) - e.g., "Mayon Volcano"
   - **Category** - e.g., "Natural Landmark"
   - **Latitude*** (required) - e.g., 13.1550
   - **Longitude*** (required) - e.g., 123.7050
   - **Description** - Details about the destination
   - **Activities** (comma-separated) - e.g., "Hiking, Photography"
   - **Image URL** - Link to destination image
2. Click "➕ Add Destination"
3. Confirm success message appears
4. Destination appears in the list below

### Step 4: Add Custom Hotel
1. Fill in the hotel form:
   - **Hotel Name*** (required) - e.g., "The Marison Hotel"
   - **Category** - e.g., "3-Star"
   - **Latitude*** (required) - e.g., 13.1130
   - **Longitude*** (required) - e.g., 123.7540
   - **Rating** - e.g., 4.5
   - **Price per Night (₱)** - e.g., 2800
   - **Phone Number** - e.g., "(052) 480-9999"
   - **Address** - Street address in Legazpi
   - **Description** - Hotel details
   - **Features** (comma-separated) - e.g., "WiFi, Restaurant, Pool"
   - **Image URL** - Link to hotel image
2. Click "➕ Add Hotel"
3. Confirm success message appears
4. Hotel appears in the list below

### Step 5: Edit Entries
1. Click the "✏️ Edit" button next to any destination or hotel
2. Form fills with current values
3. Submit button changes to "💾 Update [Item]"
4. Make changes and submit
5. List refreshes with updated values

### Step 6: Delete Entries
1. Click "🗑️" button next to any entry
2. Confirm deletion in popup
3. Entry is removed from database and list

## 🔐 Security Features
- **Admin-only**: All operations require admin authentication (require_admin() check)
- **Coordinate Validation**: All coordinates are validated to be within Legazpi City municipal boundaries
- **Prepared Statements**: All database queries use prepared statements to prevent SQL injection
- **XSS Prevention**: User input is properly escaped in HTML output

## 📊 Data Validation
- **Name**: Required field (string)
- **Coordinates**: Required, must be within Legazpi City
- **Rating**: Optional, decimal 0-5 for hotels
- **Price**: Optional, integer for hotels

## 🔗 Integration with User Itinerary
**Next steps to integrate with user-facing system:**
1. Modify `script.js` to load custom destinations from database
2. Update destination dropdown in `index.html` to include admin-added destinations
3. Update hotel suggestion algorithm to include admin-added hotels
4. Fetch data at page load: `GET /backend/api.php?action=list_itinerary_destinations`

## 📝 API Response Examples

### Success Response (Add Destination)
```json
{
  "success": true,
  "id": 1
}
```

### Success Response (List Destinations)
```json
{
  "success": true,
  "destinations": [
    {
      "id": 1,
      "name": "Mayon Volcano",
      "category": "Natural Landmark",
      "latitude": 13.1550,
      "longitude": 123.7050,
      "description": "Active volcano...",
      "activities": "Hiking, Photography",
      "image": "http://...",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00"
    }
  ]
}
```

### Error Response
```json
{
  "success": false,
  "error": "Coordinates must be within Legazpi City"
}
```

## ⚡ Performance Notes
- Lists are loaded on-demand when the tab is opened
- No pagination yet (consider adding for large datasets)
- Images are stored as URLs (not uploaded to server)
- Database queries are indexed on created_at for efficiency

## 🐛 Troubleshooting

### Issue: "Coordinates must be within Legazpi City"
**Solution**: Use valid Legazpi City coordinates:
- North: ~13.20°N
- South: ~13.10°N  
- East: ~123.78°E
- West: ~123.65°E

### Issue: Form submission fails silently
**Check**:
1. Browser console for errors (F12)
2. Network tab shows API response
3. Admin authentication is current (not logged out)

### Issue: Database tables don't exist
**Solution**: Run setup script again
```
http://localhost/capstone/backend/setup_itinerary_tables.php
```

## 📋 Feature Checklist
- ✅ Backend API endpoints created
- ✅ Database tables created
- ✅ Admin UI form for destinations
- ✅ Admin UI form for hotels
- ✅ List views with edit/delete
- ✅ Form validation
- ✅ Coordinate boundary validation
- ✅ Admin authentication check
- ❌ Image upload (URLs only for now)
- ❌ Bulk import/export
- ❌ Advanced filtering/search

## 🔄 Next Integration Steps
1. **Update script.js**: Modify destination database to fetch from API
2. **Update index.html**: Add admin-managed destinations to dropdown
3. **Update hotel suggestions**: Include custom hotels in recommendations
4. **Add user-facing display**: Show which destinations are admin-recommended vs user-suggested

---
**System Status**: ✅ Admin Management Complete | ⏳ Awaiting User Integration

