# Capstone Defense Questions: Legazpi Explorer

## 🧠 Technical Implementation (20 Points)

1. **ML Model Training**: Explain how the ARIMA(1,1,1) model achieves 92.75% accuracy AND event type classification. Show formulas from `ml_trainer.php`.

2. **Risk Visualization**: Walk through the overcrowding probability calculation and heatmapping algorithm.

3. **Database Design**: Why use separate tables for `ml_predictions` vs `event_predictions`? Show JOIN queries.

4. **Anonymous Analytics**: How does the session tracking work without user login? Privacy concerns?

## 🏗️ Architecture & Scalability (20 Points)

5. **Production Deployment**: Complete AWS/GCP deployment plan (EC2, RDS, CDN, SSL).

6. **Database Optimization**: Show EXPLAIN on busiest queries. Add indexes for 10K daily users.

7. **ML Retraining**: Strategy for daily/weekly model retraining in production.

8. **API Rate Limiting**: Implement Redis-based rate limiting for `/predict`.

## 📊 Business Intelligence (20 Points)

9. **ROI Analysis**: Calculate waste savings from ML predictions (using 2026 data).

10. **Conversion Funnel**: Analyze `activity_logs` for tourist-to-itinerary conversion rate.

11. **User Segmentation**: Cluster analysis on `anonymous_sessions` by device/city.

12. **A/B Testing**: Test ML recommendations vs random hotel suggestions.

## 🔧 Code Quality & Security (20 Points)

13. **SQL Injection Defense**: Demonstrate PDO prepared statements vs mysqli.

14. **XSS Prevention**: Show `htmlspecialchars()` usage in admin panels.

15. **CORS Policy**: Secure API endpoints for mobile app integration.

16. **Progressive Enhancement**: Why no-JS fallback for itinerary generator?

## 🚀 Future Enhancements (20 Points)

17. **Push Notifications**: Firebase Cloud Messaging for risk alerts.

18. **Payment Integration**: Stripe/PayMongo for itinerary bookings.

19. **Multi-language**: i18n implementation for Japanese/Korean tourists.

20. **Offline PWA**: Service worker + Workbox for offline itinerary viewing.

---

## 💡 Bonus Questions (Extra Credit)

**Advanced ML**:
- How would you implement LSTM for multi-event prediction?
- Explain model drift detection and auto-retraining.

**DevOps**:
- Docker multi-container setup (nginx + php-fpm + mysql)?
- GitHub Actions CI/CD pipeline?

**Monetization**:
- Affiliate partnerships with hotels (tracking links)?
- Premium itineraries ($5 for personalized PDF)?

---

**Answer ANY 5 questions + 2 bonuses for full credit.**
**Prepare database screenshots showing 700+ logs and ML metrics.**

*Questions designed to test production-level understanding*

