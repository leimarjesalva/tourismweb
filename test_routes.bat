@echo off
REM Route Optimization API Test Script for Windows

echo.
echo ====== Route Optimization System - API Test ======
echo.

REM Test 1: Get events
echo [1] Fetching events list...
curl -s "http://localhost/capstone/backend/api.php?action=list_events" > temp_events.json
type temp_events.json
echo.

REM Extract first event ID with Legazpi
echo [2] Looking for Legazpi City events...
cd /d "%~dp0"

REM Test 2: Try route API with event 1
echo [3] Testing route API with event ID=1, crowd=8000...
curl -s "http://localhost/capstone/backend/api.php?action=suggest_optimal_route&event_id=1&crowd_level=8000" > temp_routes.json
type temp_routes.json
echo.

REM Cleanup
del temp_events.json temp_routes.json 2>nul

echo ====== Test Complete ======
echo.
echo If you see route data above, the API is working!
echo If not, check:
echo   1. XAMPP is running (Apache + MySQL)
echo   2. Database has events table with data
echo   3. Check http://localhost/capstone/test_routes.html for detailed test
echo.
pause
