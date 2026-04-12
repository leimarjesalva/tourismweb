# NLP Analytics Implementation Guide

Complete guide for integrating Natural Language Processing analytics into the Legazpi Explorer admin dashboard and guest interface.

## 📋 Implementation Checklist

- [ ] Install Python NLP dependencies
- [ ] Verify NLP module installation
- [ ] Add NLP JavaScript library to HTML files
- [ ] Create NLP tab in admin dashboard
- [ ] Add NLP analytics to shop detail view (guest)
- [ ] Test all NLP features
- [ ] Optimize performance

## 🚀 Quick Start (5 minutes)

### Step 1: Install Python Dependencies

Open Command Prompt and run:

```bash
pip install nltk spacy scikit-learn
python -m spacy download en_core_web_sm
```

### Step 2: Add JavaScript Library

Add to `frontend/index.html` (before closing `</body>`):

```html
<script src="nlp-analytics.js"></script>
```

Add to `frontend/admin_dashboard.html` (before closing `</body>`):

```html
<script src="nlp-analytics.js"></script>
<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
```

### Step 3: Test Installation

Open browser console and run:

```javascript
// Test NLP API
nlpAnalytics.getAnalyticsSummary().then(data => console.log(data));
```

## 📊 Admin Dashboard Integration

### Add NLP Tab to Admin Sidebar

Edit `frontend/admin_dashboard.html` nav section:

```html
<button class="admin-nav-btn" onclick="showTab(event, 'nlpTab')">
  🤖 NLP Analytics
</button>
```

### Create NLP Analytics Tab

Add to admin_dashboard.html (after other tabs):

```html
<!-- NLP Analytics Tab -->
<div id="nlpTab" class="tab-content" style="display: none;">
  <div style="padding: 20px;">
    <h2>🤖 Natural Language Processing Analytics</h2>
    
    <div style="margin-bottom: 20px;">
      <label>Analyze Feedback:</label>
      <select id="nlpFilterType" style="padding: 8px; margin-right: 10px;">
        <option value="">All Feedback</option>
        <option value="shop">Shops Only</option>
        <option value="product">Products Only</option>
      </select>
      <button onclick="loadNLPAnalytics()" style="
        padding: 8px 16px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
      ">Run Analysis</button>
    </div>
    
    <div id="nlpAnalysisContainer"></div>
  </div>
</div>
```

### Add JavaScript to Admin Dashboard

Add this to your admin_dashboard.html script section:

```javascript
async function loadNLPAnalytics() {
  const filterType = document.getElementById('nlpFilterType').value;
  const container = document.getElementById('nlpAnalysisContainer');
  
  // Show loading
  container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">⏳ Analyzing feedback...</div>';
  
  // Generate dashboard (null = all feedback)
  await nlpAnalytics.generateDashboard('nlpAnalysisContainer', null);
}

// Load on page ready
document.addEventListener('DOMContentLoaded', () => {
  // Set up NLP tab click handler
  const nlpBtn = document.querySelector('[onclick*="nlpTab"]');
  if (nlpBtn) {
    nlpBtn.addEventListener('click', () => {
      setTimeout(loadNLPAnalytics, 100);
    });
  }
});
```

## 👥 Guest-Facing NLP Analytics

### Add NLP Insights to Shop Detail Modal

Add to shop modal in `frontend/index.html` (after Guest Ratings section):

```html
<!-- NLP Analytics Section -->
<div style="padding: 28px; border-top: 1px solid #e2e8f0;">
  <h3 style="margin: 0 0 16px 0; color: #0f172a; font-size: 1rem; font-weight: 700;">
    🔍 What Guests Are Saying
  </h3>
  <div id="shopNLPAnalytics"></div>
</div>
```

### Load NLP Analytics When Shop Opens

In your `openShopModalFunc()`, add:

```javascript
// After loading shop ratings, load NLP analytics
if (window.nlpAnalytics) {
  nlpAnalytics.generateDashboard('shopNLPAnalytics', shop.id);
}
```

## 📈 Comprehensive Dashboard Features

### 1. Sentiment Distribution
Shows the breakdown of positive, neutral, and negative feedback with:
- Doughnut chart visualization
- Percentage breakdowns
- Trend analysis over time

### 2. Aspect-Based Analysis
Identifies what customers are talking about:
- Location mentions
- Service quality
- Product pricing
- Cleanliness
- Atmosphere
- Speed of service
- Comfort level

### 3. Keyword Cloud
Visual representation of most-mentioned terms:
- Size indicates frequency
- Interactive hover effects
- Color-coded by relevance

### 4. Detailed Feedback Analysis
For each review:
- Sentiment classification (😊 😐 😞)
- Sentiment score (-1 to +1)
- Extracted aspects
- Key terms mentioned
- Summary extraction

## 🔧 Advanced Configuration

### Custom Sentiment Thresholds

Edit `nlp_analyzer.py`:

```python
self.sentiment_thresholds = {
    'positive': 0.05,      # Compound score >= 0.05
    'neutral': (-0.05, 0.05),
    'negative': -0.05      # Compound score <= -0.05
}
```

### Aspect Dictionary Customization

Add custom aspects in `nlp_analyzer.py`:

```python
aspect_keywords = {
    'your_aspect': ['keyword1', 'keyword2', 'keyword3'],
    'cleanliness': ['clean', 'dirty', 'hygiene', 'sanitation'],
    # Add more...
}
```

### Performance Tuning

For large datasets, modify batch processing in PHP:

```php
// Process in chunks
$chunkSize = 50;
$chunks = array_chunk($feedback, $chunkSize);

foreach ($chunks as $chunk) {
    $result = callNLPAnalyzer('analyze_batch', $chunk);
    // Process results...
}
```

## 📡 API Reference

### Analyze Shop Feedback
```
GET /capstone/backend/nlp_analytics_api.php?action=analyze_shop_feedback&shop_id=1

Response:
{
  "success": true,
  "shop_id": "1",
  "shop_name": "Lilay's Pasalubong",
  "total_feedback": 5,
  "sentiment_distribution": {
    "positive": 4,
    "neutral": 1,
    "negative": 0
  },
  "average_sentiment_score": 0.625,
  "top_keywords": [
    {"keyword": "quality", "count": 3},
    {"keyword": "authentic", "count": 2}
  ],
  "aspect_mentions": {
    "quality": 4,
    "price": 2,
    "location": 1
  },
  "detailed_analysis": [...]
}
```

### Analyze All Feedback
```
GET /capstone/backend/nlp_analytics_api.php?action=analyze_all_feedback&filter_type=shop&limit=100
```

### Get Analytics Summary
```
GET /capstone/backend/nlp_analytics_api.php?action=get_analytics_summary

Response includes:
- Top-rated shops with sentiment analysis
- Lowest-rated shops with problems identified
- Recent feedback analysis with NLP insights
```

## 🐛 Troubleshooting

### Issue: "Python command not found"
**Solution**: Add Python to system PATH
```bash
# Windows
set PATH=%PATH%;C:\Python39
```

### Issue: "ModuleNotFoundError: No module named 'nltk'"
**Solution**: Reinstall dependencies
```bash
pip install --upgrade nltk spacy scikit-learn
```

### Issue: NLP takes too long
**Solution**: 
- Increase PHP timeout: `set_time_limit(300);` in the PHP file
- Process feedback in smaller batches
- Cache results in database

### Issue: No sentiment scores appearing
**Solution**: 
- Verify VADER lexicon: `nltk.download('vader_lexicon')`
- Check if feedback has message field populated
- Test API directly: `/capstone/backend/nlp_analytics_api.php?action=analyze_sentiment&text=test`

## 📊 Visualization Dependencies

The NLP analytics uses Chart.js for visualizations. Already included via CDN in the JavaScript, but can be customized.

To use different chart library, modify `nlp-analytics.js`:

```javascript
// Current: Chart.js
// Switch to: D3.js, Plotly.js, or ECharts
```

## 🎯 Use Cases

### For Shop Owners
- Monitor customer satisfaction trends
- Identify service improvement areas
- Track what customers love about their shop

### For Admin Dashboard
- Identify shops with negative feedback
- Understand common customer complaints
- Discover business opportunities
- Track sentiment over time

### For Guests
- See what other guests are saying
- Understand key aspects of each shop
- Make informed decisions

## 📈 Analytics Examples

### Example 1: Shop with High Sentiment
```json
{
  "shop": "Quality Coffee House",
  "sentiment": "positive",
  "top_keywords": ["excellent", "cozy", "friendly"],
  "aspects": ["atmosphere", "service", "quality"],
  "recommendation": "This shop excels in hospitality"
}
```

### Example 2: Shop Needing Improvement
```json
{
  "shop": "Quick Market",
  "sentiment": "negative",
  "issues": ["crowded", "slow", "expensive"],
  "aspects": ["speed", "price", "comfort"],
  "recommendation": "Address service speed and pricing"
}
```

## 🔐 Security Considerations

- NLP analysis only uses feedback already in database
- No external data transmission
- Results cached locally
- User feedback remains anonymous if selected

## 📚 Further Reading

- [VADER Sentiment Analysis](https://github.com/cjhutto/vaderSentiment)
- [spaCy NLP Library](https://spacy.io/)
- [NLTK Documentation](https://www.nltk.org/)

## ✅ Verification Checklist

After implementation, verify:

- [ ] Python script runs without errors
- [ ] Admin dashboard NLP tab loads
- [ ] Sentiment charts display correctly
- [ ] Keyword clouds render
- [ ] Shop detail view shows NLP insights
- [ ] Performance is acceptable (< 5 sec load time)
- [ ] Error handling works (graceful fallbacks)
- [ ] Mobile responsive layout

## 🎉 You're Ready!

Your Legazpi Explorer now has advanced NLP analytics! Monitor guest feedback like never before.

For issues or suggestions, check the logs:
- PHP errors: `/logs/php_errors.log`
- Python output: Check command line output
- JavaScript: Browser console (F12)
