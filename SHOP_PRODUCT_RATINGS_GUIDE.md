# 🏪 Guest Shop & Product Ratings & Feedback System

## Overview
Guest visitors can now rate and provide feedback for shops and products within the Legazpi Explorer platform. All feedback and ratings are displayed in both the shop modal and the dedicated feedback & ratings page.

## ✨ Features Implemented

### 1. **Shop Ratings Display** 🏪
- View overall shop rating (1-5 stars)
- See rating breakdown (positive, neutral, negative percentages)
- Display recent reviews from guests
- Show total number of reviews

### 2. **Product Ratings** 🛍️
- Individual ratings for each product
- Display average rating on product cards
- Show number of reviews per product
- Click any product to leave a review

### 3. **Guest Feedback Form** 💬
- Rate shops and products on 1-5 star scale
- Write detailed feedback messages
- Submit anonymously (optional)
- Guest name and email (required for non-anonymous)
- Pre-selected target (shop/product) when opened from modal

### 4. **Feedback & Ratings Page** 📊
- **Shop & Product Ratings Summary** at the top
- Display top 8 rated shops and products with:
  - Average rating
  - Number of reviews
  - Item type badge (Shop/Product)
- Full feedback list with:
  - Guest name and email
  - Star ratings
  - Feedback message
  - Timestamp
  - Filters by type, rating, and search

## 🚀 How to Use

### For Guests - Rating a Shop

1. **Navigate to the Shop section** on the homepage
2. **Click on any shop** to open the shop modal
3. **View the "💬 Guest Ratings & Reviews" section** showing:
   - Overall shop rating
   - Rating breakdown by sentiment
   - Recent reviews from other guests
4. **Click "✨ Write Review"** button
5. **Fill out the feedback form:**
   - Select rating (1-5 stars)
   - Enter your name
   - Enter your email
   - Write your feedback message
   - (Optional) Check "Submit anonymously"
6. **Click "📤 Submit Feedback"**
7. Your review will be saved and appear on the feedback page

### For Guests - Rating a Product

1. **Open any shop** (click on shop name)
2. **View the products section** showing all products with ratings
3. **Click on any product card** to open the feedback form (pre-selected for that product)
4. **Complete the feedback form** as above
5. Your product review will be saved

### Viewing Ratings & Feedback

1. **On Shop Modal:**
   - See shop ratings immediately when modal opens
   - View recent guest reviews
   - See individual product ratings

2. **On Feedback Page** (`/capstone/frontend/feedback.html`):
   - See "Shop & Product Ratings" summary at top
   - View all feedback organized by type
   - Filter by feedback type, rating, sort by date/rating
   - Search reviews by guest name

## 📊 Data Structure

All ratings and feedback are stored in the database `feedback` table:

```
Fields:
- id: Unique rating ID
- user_name: Guest name
- user_email: Guest email
- target_type: 'shop' or 'product'
- target_id: Shop/Product ID
- target_name: Shop/Product name
- rating: 1-5 stars
- message: Feedback text
- anonymous: 0 for named, 1 for anonymous
- feedback_type: 'review'
- created_at: Timestamp
```

## 🔧 Technical Implementation

### Files Created
1. **frontend/shop-ratings.js** - ShopRatingsManager class
   - Handles rating API calls
   - Creates rating display HTML
   - Manages feedback forms

### Files Modified
1. **frontend/index.html**
   - Added shop-ratings.js script
   - Enhanced shop modal with rating section
   - Added product loading with ratings
   - Added "Write Review" buttons

2. **frontend/feedback-system.js**
   - Added `openModalForTarget()` method
   - Allows pre-selecting target in feedback form

3. **frontend/feedback.html**
   - Added ratings summary section
   - Added `loadRatingsSummary()` function
   - Added CSS for ratings grid

### API Endpoints Used

1. **Submit Rating:**
   ```
   POST /capstone/backend/ratings_api.php?action=submit_rating
   Content-Type: application/json
   
   Body: {
     user_name: string,
     user_email: string,
     target_type: 'shop' or 'product',
     target_id: string,
     target_name: string,
     rating: 1-5,
     message: string,
     anonymous: 0 or 1,
     feedback_type: 'review'
   }
   ```

2. **Get Target Ratings:**
   ```
   GET /capstone/backend/ratings_api.php?action=get_target_ratings
   ?target_type=shop&target_id=123
   
   Returns: {
     success: true,
     statistics: { total_reviews, average_rating, positive_reviews, ... },
     reviews: [ { user_name, rating, message, created_at, ... } ]
   }
   ```

3. **Get All Ratings:**
   ```
   GET /capstone/backend/ratings_api.php?action=get_ratings
   ?target_type=shop&limit=100
   
   Returns: {
     success: true,
     data: [ { ... } ],
     total: number
   }
   ```

## 🎯 User Journey

### Example: Guest Rates a Shop

```
Guest visits homepage
        ↓
Clicks on "Discover Local Novelty Shops"
        ↓
Clicks on a shop (e.g., "Lilay's Pasalubong")
        ↓
Shop modal opens showing:
  - Shop details
  - Current ratings & reviews
  - Product list with ratings
        ↓
Guest clicks "✨ Write Review"
        ↓
Feedback form opens with shop pre-selected
        ↓
Guest fills form:
  - Rates: 5 stars
  - Name: John Visitor
  - Email: john@example.com
  - Message: "Great selection! Will visit again!"
        ↓
Guest clicks "📤 Submit Feedback"
        ↓
Rating saved to database ✅
        ↓
Success message shown
        ↓
Modal closes
        ↓
Guest visits Feedback & Ratings page
        ↓
Sees their review in the feedback list
Sees shop rating updated in summary
```

## 💾 Data Storage

- **Database:** capstone_db
- **Table:** feedback
- **Backup:** backend/feedback/rating_*.json (JSON backup of each rating)

## 🔒 Privacy & Security

- **Anonymous Option:** Guests can submit feedback anonymously
- **Email Validation:** Email addresses are validated
- **Rating Range:** Ratings must be 1-5 stars
- **Message Required:** Feedback message is mandatory

## 🎨 Visual Design

### Rating Display
- **5-star system:** ★ for filled, ☆ for empty
- **Average Rating:** Large, prominent display (e.g., "4.3")
- **Breakdown:** Pie charts showing positive/neutral/negative percentages
- **Count:** "(25 reviews)" format

### Cards
- **Shop Cards:** White background, rounded corners, hover effects
- **Product Cards:** Compact display with price and rating
- **Review Cards:** White with left accent border, expandable

### Colors
- **Shop Type Badge:** Orange (#ff7a18)
- **Product Type Badge:** Blue (#3b82f6)
- **Stars:** Gold (#fbbf24)
- **Ratings Summary:** Gradient background

## ✅ Testing Checklist

- [ ] Can submit shop rating from shop modal
- [ ] Can submit product rating from product card
- [ ] Rating displays in shop modal immediately
- [ ] Rating displays in feedback page summary
- [ ] Anonymous submission works
- [ ] Non-anonymous submission requires email
- [ ] Feedback form pre-selects correct target
- [ ] Product list shows with ratings
- [ ] Filters work on feedback page
- [ ] Stars display correctly for ratings
- [ ] Overall rating calculation is correct

## 🐛 Known Limitations

1. Image upload in feedback forms is prepared but not fully featured
2. Admin moderation tools not yet implemented
3. Email notifications to shop owners not yet implemented
4. Reply to reviews not yet implemented

## 🚀 Future Enhancements

1. **Image Support:** Allow guests to upload photos with reviews
2. **Admin Panel:** Dashboard for managing ratings
3. **Email Notifications:** Notify shop owners of new reviews
4. **Reply System:** Shop owners respond to reviews
5. **Helpful Votes:** Upvote/downvote review helpfulness
6. **Export:** Download ratings as CSV/PDF
7. **Analytics:** Advanced reporting and insights
8. **Rating Moderation:** Approve reviews before publishing

## 📞 Support & Maintenance

For issues or feature requests:
1. Check the feedback page for recent reviews
2. Check database `feedback` table for data integrity
3. Review API responses in browser console
4. Check backend logs for errors

---

**Version:** 1.0
**Date:** April 2026
**Status:** ✅ Production Ready
