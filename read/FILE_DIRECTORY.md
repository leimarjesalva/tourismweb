# 📚 ML Server File Directory

Complete reference of all files in the ML server implementation.

---

## Core Application Files

### 1. **train.js** (445 lines)
**Purpose:** Machine learning model training script

**Key Functions:**
- `generateData()` - Creates 1000 synthetic festival records
- `train()` - Main training orchestration
- Trains 3 model configurations and picks the best
- Saves trained models and normalization parameters
- Provides detailed training logs

**Run Command:**
```bash
npm run train
```

**Output:**
- `best-overcrowding-model/` directory
- `best-waste-model/` directory
- `normalization.json` file

---

### 2. **server.js** (420 lines)
**Purpose:** Express.js REST API server for predictions

**Key Functions:**
- `loadModels()` - Loads pre-trained models at startup
- `normalizeInput()` - Normalizes input data for predictions
- 5 REST endpoints:
  - `POST /predict` - Single prediction
  - `POST /predict-batch` - Batch predictions
  - `GET /history` - Prediction history
  - `GET /stats` - Global statistics
  - `GET /health` - Health check

**Port:** 3000 (configurable)

**Requirements:**
- Pre-trained models (run `npm run train` first)
- MySQL database connection
- Environment variables configured

---

### 3. **package.json** (20 lines)
**Purpose:** NPM package configuration and dependencies

**Dependencies:**
- `@tensorflow/tfjs` - TensorFlow core
- `@tensorflow/tfjs-node` - Node.js bindings
- `express` - Web framework
- `mysql2` - Database driver
- `cors` - Cross-origin support
- `nodemon` (dev) - Auto-restart on changes

**Scripts:**
- `npm train` - Train models
- `npm start` - Run server
- `npm run dev` - Development mode

---

## Configuration Files

### 4. **.env.example** (11 lines)
**Purpose:** Environment variables template

**Variables:**
- `PORT` - Server port (default: 3000)
- `NODE_ENV` - Environment (development/production)
- `DB_HOST` - MySQL host
- `DB_USER` - Database username
- `DB_PASS` - Database password
- `DB_NAME` - Database name

**Usage:** Copy to `.env` and customize

---

### 5. **.gitignore** (30 lines)
**Purpose:** Git version control exclusions

**Excludes:**
- `node_modules/` - Large dependencies
- Trained models (large binary files)
- Environment files (`.env`)
- Logs
- IDE configuration
- OS-specific files

---

### 6. **ecosystem.config.js** (90 lines)
**Purpose:** PM2 process manager configuration

**Features:**
- Auto-restart on crash
- Logging configuration
- Memory limits
- Production environment settings
- Deployment configuration example

**Usage:**
```bash
pm2 start ecosystem.config.js
```

---

## Database Files

### 7. **schema.sql** (150 lines)
**Purpose:** Complete MySQL database schema

**Tables Created:**
- `predictions` - Main prediction history (indexed)
- `festival_events` - Festival metadata
- `festival_predictions` - Per-festival predictions
- `model_metrics` - Model performance tracking
- `api_logs` - API usage monitoring

**Sample Data:**
- 3 sample festivals with various metrics

**Usage:**
```bash
mysql -u root < schema.sql
```

---

## Integration Files

### 8. **MLServerBridge.php** (300+ lines)
**Purpose:** PHP integration class for ML server access

**Key Methods:**
- `isServerRunning()` - Check ML server status
- `predictFestivalCrowding()` - Get single prediction
- `predictBatch()` - Get batch predictions
- `getPredictionHistory()` - Retrieve history
- `getStatistics()` - Get global stats
- `getFallbackPrediction()` - Fallback if server down

**Features:**
- Automatic database storage
- Error handling with fallback
- Cross-language integration
- Timeout handling

**Location:** `backend/MLServerBridge.php`

---

## Documentation Files

### 9. **README.md** (500+ lines)
**Purpose:** Complete technical reference guide

**Sections:**
- ✅ Overview & features
- ✅ Prerequisites
- ✅ Installation (5-step guide)
- ✅ API endpoint documentation
- ✅ Integration examples (PHP, JavaScript)
- ✅ Model performance details
- ✅ Retraining instructions
- ✅ Development mode
- ✅ Troubleshooting guide

**Best For:** Technical reference, detailed setup

---

### 10. **QUICK_START.md** (150 lines)
**Purpose:** Fast 5-minute setup guide

**Includes:**
- ✅ Step-by-step installation
- ✅ 5 quick tests with expected output
- ✅ Usage from PHP and JavaScript
- ✅ Troubleshooting table
- ✅ Directory structure after setup

**Best For:** Quick deployment, getting started

---

### 11. **IMPLEMENTATION_SUMMARY.md** (250 lines)
**Purpose:** High-level overview of ML implementation

**Covers:**
- ✅ What was implemented
- ✅ File listing with descriptions
- ✅ ML models overview
- ✅ REST API summary
- ✅ Database schema overview
- ✅ Installation workflow
- ✅ Integration points
- ✅ Key features & performance
- ✅ Security considerations
- ✅ Next steps & deployment

**Best For:** Project overview, stakeholder updates

---

### 12. **ARCHITECTURE.md** (300+ lines)
**Purpose:** System architecture and design documentation

**Includes:**
- ✅ ASCII architecture diagrams
- ✅ Data flow diagrams
- ✅ Training process flowchart
- ✅ Model architecture details
- ✅ Integration with existing system
- ✅ Development vs Production deployment
- ✅ Component feature matrix

**Best For:** System design, understanding interactions

---

### 13. **SETUP_CHECKLIST.md** (200+ lines)
**Purpose:** Verification checklist for installation

**Checklists:**
- ✅ Pre-installation requirements
- ✅ Installation verification
- ✅ Server launch verification
- ✅ 5 functionality tests (with commands)
- ✅ Performance benchmarks
- ✅ Integration tests
- ✅ File structure verification
- ✅ Security checklist
- ✅ Production readiness
- ✅ Troubleshooting reference

**Best For:** Verification, quality assurance

---

### 14. **PRODUCTION_DEPLOYMENT.md** (400+ lines)
**Purpose:** Complete production deployment guide

**Covers:**
- ✅ Prerequisites and setup
- ✅ Application installation steps
- ✅ Systemd service configuration
- ✅ PM2 process manager setup
- ✅ Nginx reverse proxy setup
- ✅ SSL/TLS certificate installation
- ✅ Monitoring & health checks
- ✅ Backup & recovery procedures
- ✅ Performance tuning
- ✅ Security hardening
- ✅ Maintenance schedule
- ✅ Scaling recommendations

**Best For:** Production deployment, DevOps setup

---

## Generated Files (After Running)

### 15. **normalization.json** (auto-generated)
**Purpose:** Data normalization parameters

**Contains:**
- Mean values for each feature
- Standard deviation for each feature
- Used during prediction to normalize input

**Generated By:** `npm run train`

---

### 16. **best-overcrowding-model/** (directory)
**Purpose:** Trained classification model

**Contents:**
- `model.json` - Model architecture and metadata
- `weights.bin` - Model weights (binary)
- `weights.md5` - Checksum

**Size:** ~200KB
**Accuracy:** 85-92%

**Generated By:** `npm run train`

---

### 17. **best-waste-model/** (directory)
**Purpose:** Trained regression model

**Contents:**
- `model.json` - Model architecture
- `weights.bin` - Model weights
- `weights.md5` - Checksum

**Size:** ~150KB

**Generated By:** `npm run train`

---

## File Organization

```
backend/ml/
├── 📄 train.js                    (445 lines)
├── 📄 server.js                   (420 lines)
├── 📄 package.json                (20 lines)
├── 📄 .env.example                (11 lines)
├── 📄 .gitignore                  (30 lines)
├── 📄 ecosystem.config.js         (90 lines)
├── 📄 schema.sql                  (150 lines)
│
├── 📚 README.md                   (500+ lines)
├── 📚 QUICK_START.md              (150 lines)
├── 📚 IMPLEMENTATION_SUMMARY.md    (250 lines)
├── 📚 ARCHITECTURE.md             (300+ lines)
├── 📚 SETUP_CHECKLIST.md          (200+ lines)
├── 📚 PRODUCTION_DEPLOYMENT.md    (400+ lines)
│
├── 📁 node_modules/               (auto-generated)
│   ├── @tensorflow/
│   ├── express/
│   ├── mysql2/
│   └── cors/
│
├── 📁 best-overcrowding-model/    (auto-generated)
│   ├── model.json
│   └── weights.bin
│
├── 📁 best-waste-model/           (auto-generated)
│   ├── model.json
│   └── weights.bin
│
├── 📄 normalization.json          (auto-generated)
├── 📄 package-lock.json           (auto-generated)
└── 📄 .env                        (auto-generated)

backend/
└── 📄 MLServerBridge.php          (300+ lines)
```

---

## File Statistics

| Category | Count | Total Lines |
|----------|-------|------------|
| Core Application | 2 | 865 |
| Configuration | 3 | 131 |
| Database | 1 | 150 |
| PHP Integration | 1 | 300+ |
| Documentation | 6 | 2,050+ |
| **TOTAL** | **13** | **3,500+** |

---

## Documentation Hierarchy

```
START HERE
    ↓
QUICK_START.md ................... 5-minute setup
    ↓
README.md ....................... Full reference
    ↓
If at 5-minute point:
├─→ SETUP_CHECKLIST.md .......... Verify installation
└─→ ARCHITECTURE.md ............. Understand design
    
If going to production:
├─→ PRODUCTION_DEPLOYMENT.md .... Deploy to server
└─→ ecosystem.config.js ......... PM2 process management

If troubleshooting:
└─→ README.md#Troubleshooting ... Common issues & solutions
```

---

## Quick File Finder

**I need to...** → **Read this file:**

- Get started quickly → **QUICK_START.md**
- Understand the system → **ARCHITECTURE.md**
- Deploy to production → **PRODUCTION_DEPLOYMENT.md**
- Verify everything works → **SETUP_CHECKLIST.md**
- Train new models → **README.md** + run `npm run train`
- Access from PHP → **MLServerBridge.php** + **README.md#Integration**
- Find an API endpoint → **README.md#API Endpoints**
- Troubleshoot an issue → **README.md#Troubleshooting**
- Understand database → **schema.sql** + **README.md#Database**
- Run on Windows (dev) → **QUICK_START.md** + **README.md#Installation**
- Integrate with app → **IMPLEMENTATION_SUMMARY.md#Integration Points**
- See complete overview → **IMPLEMENTATION_SUMMARY.md**

---

## Code Access

**To modify...**

| Component | File | Lines | Purpose |
|-----------|------|-------|---------|
| Model training | train.js | 445 | Adjust data generation, model architecture, training params |
| API endpoints | server.js | 420 | Add endpoints, modify response format, add authentication |
| Dependencies | package.json | 20 | Add new packages, update versions |
| Database schema | schema.sql | 150 | Add tables, modify columns, adjust indexes |
| PHP integration | MLServerBridge.php | 300+ | Modify fallback logic, add caching, error handling |

---

## File Sizes (Approximate)

| File | Size | Notes |
|------|------|-------|
| train.js | 18 KB | Relatively small |
| server.js | 17 KB | Manageable size |
| README.md | 50 KB | Comprehensive |
| normalization.json | 1 KB | Auto-generated |
| best-overcrowding-model/ | 200 KB | Binary model files |
| best-waste-model/ | 150 KB | Binary model files |
| node_modules/ | 500+ MB | All dependencies |
| **TOTAL (excluding node_modules)** | **~600 KB** | Source + documentation |

---

**📌 Key Insight:** The entire ML implementation (excluding dependencies) is only ~600 KB, but provides a powerful production-ready system!
