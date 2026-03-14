# ✅ ML Server Setup Checklist

Use this checklist to verify your ML server installation is complete and working correctly.

---

## Pre-Installation Checklist

- [ ] Node.js 14+ installed (`node --version`)
- [ ] npm installed (`npm --version`)
- [ ] XAMPP installed with MySQL running
- [ ] MySQL accessible (`mysql -u root` works)
- [ ] Port 3000 is available (check: `netstat -an | grep 3000`)
- [ ] At least 2GB free disk space
- [ ] Reliable internet connection (for npm install)

---

## Installation Checklist

- [ ] Navigated to `backend/ml` directory
- [ ] Ran `npm install` successfully
- [ ] All dependencies installed (check: `node_modules` folder exists)
- [ ] Ran `mysql < schema.sql` (or executed via MySQL)
- [ ] Database `ibalong_ai` created
- [ ] Tables created (verify with `mysql -u root -e "USE ibalong_ai; SHOW TABLES;"`)
- [ ] Created `.env` file (optional, defaults work)
- [ ] Ran `npm run train` successfully
- [ ] Training completed with models created
- [ ] Models saved:
  - [ ] `best-overcrowding-model/` folder exists
  - [ ] `best-waste-model/` folder exists
  - [ ] `normalization.json` file exists

---

## Server Launch Checklist

- [ ] Started server with `npm start`
- [ ] Server shows welcome message on port 3000
- [ ] No errors in console output
- [ ] Database connection successful

---

## Functionality Tests

### Test 1: Health Check
- [ ] Command:
  ```bash
  curl http://localhost:3000/health
  ```
- [ ] Expected: `{"status":"ML Server is running ✅"}`
- [ ] Result: ✅ PASS / ❌ FAIL

### Test 2: Single Prediction
- [ ] Command:
  ```bash
  curl -X POST http://localhost:3000/predict \
    -H "Content-Type: application/json" \
    -d '{"attendance":20000,"venue_capacity":50000,"weekend":1,"is_free":1,"duration_hours":8,"food_stalls":45,"weather":0}'
  ```
- [ ] Expected: JSON with `"success":true` and prediction data
- [ ] Got valid probability between 0-100%
- [ ] Got waste_kg as positive number
- [ ] Result: ✅ PASS / ❌ FAIL

### Test 3: Batch Predictions
- [ ] Command:
  ```bash
  curl -X POST http://localhost:3000/predict-batch \
    -H "Content-Type: application/json" \
    -d '{"predictions":[{"attendance":20000,"venue_capacity":50000,"weekend":1,"is_free":1,"duration_hours":8,"food_stalls":45,"weather":0},{"attendance":35000,"venue_capacity":40000,"weekend":1,"is_free":0,"duration_hours":6,"food_stalls":60,"weather":1}]}'
  ```
- [ ] Expected: Array with 2 predictions
- [ ] Both predictions returned successfully
- [ ] Result: ✅ PASS / ❌ FAIL

### Test 4: History
- [ ] Command:
  ```bash
  curl http://localhost:3000/history
  ```
- [ ] Expected: JSON array with predictions
- [ ] Shows count of saved predictions
- [ ] Result: ✅ PASS / ❌ FAIL

### Test 5: Statistics
- [ ] Command:
  ```bash
  curl http://localhost:3000/stats
  ```
- [ ] Expected: JSON with statistics
- [ ] Shows total_predictions count
- [ ] Shows average values
- [ ] Result: ✅ PASS / ❌ FAIL

---

## Performance Benchmarks

### Prediction Speed
- [ ] Single prediction: < 100ms
- [ ] Batch (10 predictions): < 500ms
- [ ] Batch (50 predictions): < 2s

### Model Accuracy
- [ ] Classification accuracy: > 85%
- [ ] Regression MSE: < 100

### Database
- [ ] All prediction tables populated
- [ ] Can query `predictions` table
- [ ] Can query `festival_events` table

---

## Integration Tests

### Test PHP Bridge
- [ ] MLServerBridge.php file exists in `backend/`
- [ ] File is readable and valid PHP syntax
- [ ] Can be included in existing PHP files

### Test Database Connectivity
- [ ] PHP can connect to MySQL
- [ ] Can read from `ibalong_ai` database
- [ ] Can write predictions to database

---

## File Structure Verification

- [ ] ✅ backend/ml/
  - [ ] ✅ train.js (training script)
  - [ ] ✅ server.js (API server)
  - [ ] ✅ package.json (dependencies)
  - [ ] ✅ .env.example (config template)
  - [ ] ✅ schema.sql (database setup)
  - [ ] ✅ .gitignore (version control)
  - [ ] ✅ README.md (full documentation)
  - [ ] ✅ QUICK_START.md (quick guide)
  - [ ] ✅ IMPLEMENTATION_SUMMARY.md (summary)
  - [ ] ✅ ARCHITECTURE.md (system design)
  - [ ] ✅ SETUP_CHECKLIST.md (this file)
  - [ ] ✅ normalization.json (created by training)
  - [ ] ✅ best-overcrowding-model/ (created by training)
  - [ ] ✅ best-waste-model/ (created by training)

- [ ] ✅ backend/
  - [ ] ✅ MLServerBridge.php (PHP integration)

---

## Security Checklist

- [ ] [ ] .env file created with DB credentials
- [ ] [ ] .env file is git-ignored
- [ ] [ ] Sensitive data not in code
- [ ] [ ] CORS restricted in production settings
- [ ] [ ] API input validation working
- [ ] [ ] Error messages don't expose internals

---

## Documentation Checklist

- [ ] [ ] README.md reviewed (40+ sections)
- [ ] [ ] QUICK_START.md completed
- [ ] [ ] ARCHITECTURE.md understood
- [ ] [ ] API endpoints documented
- [ ] [ ] Example code available
- [ ] [ ] Troubleshooting guide reviewed

---

## Troubleshooting Verification

If any test FAILED, check:

### Failed Health Check?
- [ ] Port 3000 is open
- [ ] Server output shows no errors
- [ ] Try: `lsof -i :3000` to check port

### Failed Prediction Test?
- [ ] Database connection working
- [ ] Models trained successfully
- [ ] Check server logs for errors
- [ ] Try smaller prediction data

### Models Not Found?
- [ ] Run `npm run train` again
- [ ] Check folders exist with files
- [ ] Verify normalization.json created

### Database Issues?
- [ ] MySQL is running
- [ ] `ibalong_ai` database exists
- [ ] Tables created successfully
- [ ] Try: `mysql -u root -e "SHOW DATABASES;"`

---

## Production Readiness Checklist

- [ ] All tests passing
- [ ] No console errors
- [ ] Database optimized
- [ ] Error handling tested
- [ ] Security review complete
- [ ] Documentation complete
- [ ] Performance benchmarks met
- [ ] Deployment plan created

---

## Next Steps After Verification

### Immediate (Today)
1. [ ] Complete all tests above
2. [ ] Fix any failing tests
3. [ ] Review documentation
4. [ ] Test PHP integration

### Short Term (This Week)
1. [ ] Integrate with admin dashboard
2. [ ] Add predictions to festival events
3. [ ] Display predictions in UI
4. [ ] Train with real data

### Long Term (Ongoing)
1. [ ] Monitor prediction accuracy
2. [ ] Collect real event feedback
3. [ ] Retrain models periodically
4. [ ] Optimize based on results

---

## Sign-Off

When all items are checked:

- **Setup Date:** _______________
- **Completed By:** _______________
- **Verified By:** _______________
- **Status:** ✅ READY FOR PRODUCTION

---

## Notes & Issues

Use this section to track any issues encountered:

```
Issue 1:
  - Problem: ___________________________
  - Solution: __________________________
  - Resolved: ✅ / ❌

Issue 2:
  - Problem: ___________________________
  - Solution: __________________________
  - Resolved: ✅ / ❌
```

---

## Support Resources

If you get stuck:

1. **README.md** - Full technical documentation
2. **QUICK_START.md** - 5-minute setup guide
3. **ARCHITECTURE.md** - System design & diagrams
4. **server.js** - Well-commented API code
5. **train.js** - Annotated training script

---

**Remember:** Check the logs! Server output often shows what's wrong.

**All Set?** You're ready to deploy! 🚀
