#!/bin/bash
# Quick Reference - Test All Parade Route Features

echo "🎊 PARADE ROUTE SYSTEM - QUICK TEST GUIDE"
echo "==========================================="
echo ""

# Test 1: Get parade details
echo "1️⃣  TEST: Get Parade Details for Event ID 1"
echo "Command:"
echo '  curl "http://localhost/capstone/backend/api.php?action=get_event_parade_details&event_id=1"'
echo ""

# Test 2: Get route suggestions
echo "2️⃣  TEST: Get Route Suggestions (crowd=100 visitors)"
echo "Command:"
echo '  curl "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=100"'
echo ""

# Test 3: Get ML metrics
echo "3️⃣  TEST: Get ML Model Metrics"
echo "Command:"
echo '  curl "http://localhost/capstone/backend/ml/ml_trainer.php?action=metrics"'
echo ""

# Test 4: List all events
echo "4️⃣  TEST: List All Events"
echo "Command:"
echo '  curl "http://localhost/capstone/backend/api.php?action=list_events"'
echo ""

# Test 5: Web UI Tests
echo "5️⃣  TEST VIA WEB UI:"
echo ""
echo "  • Guest Home Page:"
echo "    http://localhost/capstone/index.html"
echo "    → Click any event → View Parade Route Details"
echo ""
echo "  • Admin Dashboard:"
echo "    http://localhost/capstone/admin_dashboard.html"
echo "    → Create/Edit event"
echo "    → Set coordinates via map picker"
echo "    → Save and verify on home page"
echo ""
echo "  • API Test Suite:"
echo "    http://localhost/capstone/test_parade_api.html"
echo "    → Test all endpoints interactively"
echo ""

echo "==========================================="
echo "✅ All tests ready to run!"
