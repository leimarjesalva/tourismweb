# NLP Analytics Setup Guide

This document explains how to set up and configure the Natural Language Processing (NLP) module for the Legazpi Explorer analytics system.

## Overview

The NLP module analyzes guest feedback to extract:
- **Sentiment Analysis**: Detect positive, negative, or neutral sentiment
- **Keyword Extraction**: Identify important topics and terms
- **Aspect Extraction**: Determine what aspects of the service are being reviewed
- **Text Summarization**: Generate concise summaries of feedback
- **Topic Modeling**: Discover common themes in feedback

## Requirements

- Python 3.7+
- pip (Python package manager)
- PHP 7.4+
- MySQL/MariaDB

## Installation Steps

### 1. Install Python NLP Libraries

```bash
# Navigate to the project directory
cd c:\xampp\htdocs\capstone\backend

# Install required Python packages
pip install nltk spacy scikit-learn

# Download spaCy English language model
python -m spacy download en_core_web_sm
```

### 2. Verify Installation

Test the NLP module:

```bash
# Test sentiment analysis
python nlp_analyzer.py
```

Create a test file `test_nlp.php`:

```php
<?php
// Test NLP API
$response = file_get_contents('http://localhost/capstone/backend/nlp_analytics_api.php?action=analyze_sentiment&text=This amazing shop has great products!');
echo json_decode($response, true);
?>
```

### 3. Database Setup

Ensure your `feedback` table has these columns:
```sql
- id: INT PRIMARY KEY
- target_type: VARCHAR (shop, product)
- target_id: VARCHAR
- target_name: VARCHAR
- rating: INT (1-5)
- message: TEXT
- user_name: VARCHAR
- user_email: VARCHAR
- created_at: TIMESTAMP
```

## API Endpoints

### Analyze Shop Feedback
```
GET /capstone/backend/nlp_analytics_api.php?action=analyze_shop_feedback&shop_id=1
```

Returns:
```json
{
  "success": true,
  "total_feedback": 10,
  "sentiment_distribution": {
    "positive": 7,
    "neutral": 2,
    "negative": 1
  },
  "average_sentiment_score": 0.425,
  "top_keywords": [
    {"keyword": "great", "count": 3},
    {"keyword": "quality", "count": 2}
  ],
  "aspect_mentions": {
    "quality": 5,
    "service": 3,
    "price": 2
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
```

Returns comprehensive analytics with sentiment analysis, top-rated shops, and recent feedback analysis.

### Single Text Analysis
```
POST /capstone/backend/nlp_analytics_api.php?action=analyze_sentiment
Content-Type: application/x-www-form-urlencoded

text=Your feedback text here
```

## Frontend Integration

The NLP data is automatically integrated into:

1. **Admin Dashboard** (`admin_dashboard.html`)
   - View sentiment distribution for shops
   - See top keywords mentioned in feedback
   - Identify common aspects being reviewed
   - Monitor sentiment trends over time

2. **Analytics Dashboard** (new component)
   - Detailed sentiment analysis charts
   - Aspect-based feedback breakdown
   - Keyword cloud visualization

## Frontend Implementation Guide

### 1. Admin Dashboard Integration

Add to admin_dashboard.html:

```html
<!-- NLP Analytics Section -->
<div id="nlpAnalyticsTab" style="display: none; padding: 20px;">
  <h2>🤖 NLP Analytics</h2>
  
  <!-- Sentiment Distribution Chart -->
  <div id="sentimentChart"></div>
  
  <!-- Top Keywords -->
  <div id="topKeywords"></div>
  
  <!-- Aspect Mentions -->
  <div id="aspectMentions"></div>
  
  <!-- Recent Feedback Analysis -->
  <div id="recentFeedbackAnalysis"></div>
</div>
```

### 2. JavaScript Helper Functions

Add to a new file or existing analytics script:

```javascript
class NLPAnalytics {
  constructor() {
    this.apiBase = '/capstone/backend/nlp_analytics_api.php';
  }
  
  async analyzeShop(shopId) {
    const response = await fetch(`${this.apiBase}?action=analyze_shop_feedback&shop_id=${shopId}`);
    return response.json();
  }
  
  async analyzeAllFeedback(filterType = null) {
    let url = `${this.apiBase}?action=analyze_all_feedback`;
    if (filterType) url += `&filter_type=${filterType}`;
    const response = await fetch(url);
    return response.json();
  }
  
  async getAnalyticsSummary() {
    const response = await fetch(`${this.apiBase}?action=get_analytics_summary`);
    return response.json();
  }
  
  displaySentimentChart(data) {
    // Create visualization using Chart.js or similar
    const ctx = document.getElementById('sentimentChart').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Positive', 'Neutral', 'Negative'],
        datasets: [{
          data: [
            data.sentiment_distribution.positive,
            data.sentiment_distribution.neutral,
            data.sentiment_distribution.negative
          ],
          backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
        }]
      }
    });
  }
}
```

## Troubleshooting

### "Python not found" error
- Ensure Python is installed and added to PATH
- Test with: `python --version`

### NLP module not found
- Verify `nlp_analyzer.py` is in `/capstone/backend/`
- Check file permissions

### Memory errors
- Increase PHP memory_limit in php.ini
- Process feedback in batches

### No sentiment scores returned
- Ensure VADER lexicon downloaded: `nltk.download('vader_lexicon')`
- Check if message field is populated in database

## Performance Optimization

For large datasets:

1. **Batch Processing**: Analyze feedback in chunks of 50-100
2. **Caching**: Store NLP results in database for quick retrieval
3. **Async Processing**: Use background jobs for bulk analysis

## Future Enhancements

- Machine learning model for aspect-based sentiment analysis
- Multi-language support
- Real-time feedback analysis
- Predictive analytics for ratings

## Support

For issues, check:
- `/logs/` directory for error logs
- Browser console for JavaScript errors
- PHP error logs for backend errors
