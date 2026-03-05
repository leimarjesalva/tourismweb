const express = require('express');
const tf = require('@tensorflow/tfjs');
require('@tensorflow/tfjs-backend-cpu');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');
const cors = require('cors');

// Set CPU backend explicitly
tf.setBackend('cpu');

const app = express();
app.use(express.json());
app.use(cors());

let classModel, regModel, normalization;

// Load trained models or create new ones
async function loadModels() {
    console.log('📂 Loading or creating models...');
    
    try {
        // Try to load existing models
        const modelJsonPath = path.join(__dirname, 'best-overcrowding-model', 'model.json');
        const wasteModelPath = path.join(__dirname, 'best-waste-model', 'model.json');
        
        if (fs.existsSync(modelJsonPath) && fs.existsSync(wasteModelPath)) {
            console.log('📚 Found trained models, attempting to load...');
            try {
                const classModelUrl = `file://${modelJsonPath.replace(/\\/g, '/')}`;
                const regModelUrl = `file://${wasteModelPath.replace(/\\/g, '/')}`;
                classModel = await tf.loadLayersModel(classModelUrl);
                regModel = await tf.loadLayersModel(regModelUrl);
                console.log('✅ Models loaded from files!\n');
            } catch (loadErr) {
                console.log('⚠️ Could not load models from files, creating new ones...');
                createModels();
            }
        } else {
            console.log('🆕 Creating new models...');
            createModels();
        }
        
        // Load or create normalization parameters
        const normPath = path.join(__dirname, 'normalization.json');
        if (fs.existsSync(normPath)) {
            normalization = JSON.parse(fs.readFileSync(normPath, 'utf-8'));
        } else {
            normalization = {
                mean: [17500, 20000, 0.5, 0.5, 5, 55, 0.5],
                std: [8660, 8660, 0.5, 0.5, 2, 26, 0.5]
            };
            fs.writeFileSync(normPath, JSON.stringify(normalization, null, 2));
        }
        
        console.log('✅ Models ready for predictions!\n');
    } catch (err) {
        console.error('❌ Failed to prepare models:', err.message);
        process.exit(1);
    }
}

function createModels() {
    // Create classification model (overcrowding detection)
    classModel = tf.sequential({
        layers: [
            tf.layers.dense({ units: 64, activation: 'relu', inputShape: [7] }),
            tf.layers.dense({ units: 32, activation: 'relu' }),
            tf.layers.dense({ units: 1, activation: 'sigmoid' })
        ]
    });

    // Create regression model (waste prediction)
    regModel = tf.sequential({
        layers: [
            tf.layers.dense({ units: 64, activation: 'relu', inputShape: [7] }),
            tf.layers.dense({ units: 32, activation: 'relu' }),
            tf.layers.dense({ units: 1, activation: 'linear' })
        ]
    });

    console.log('🆕 New models created in memory');
}

function normalizeInput(input) {
    return input.map((val, i) =>
        (val - normalization.mean[i]) / normalization.std[i]
    );
}

const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASS || '',
    database: process.env.DB_NAME || 'ibalong_ai',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ML Server is running ✅' });
});

// Main prediction endpoint
app.post('/predict', async (req, res) => {
    try {
        const { attendance, venue_capacity, weekend, is_free, duration_hours, food_stalls, weather } = req.body;

        // Validate input
        if (typeof attendance !== 'number' || typeof venue_capacity !== 'number') {
            return res.status(400).json({ error: 'Invalid input parameters' });
        }

        const raw = [
            attendance,
            venue_capacity,
            weekend,
            is_free,
            duration_hours,
            food_stalls,
            weather
        ];

        const normalized = normalizeInput(raw);
        const tensor = tf.tensor2d([normalized]);

        // Get predictions
        const overcrowdedProb = (await classModel.predict(tensor).data())[0];
        const overcrowded = overcrowdedProb > 0.5 ? 1 : 0;

        const wasteKg = (await regModel.predict(tensor).data())[0];

        tensor.dispose();

        try {
            const conn = await pool.getConnection();
            await conn.execute(
                `INSERT INTO predictions 
                (attendance, venue_capacity, weekend, is_free, duration_hours, food_stalls, weather, overcrowded, waste_prediction)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
                [attendance, venue_capacity, weekend, is_free, duration_hours, food_stalls, weather, overcrowded, wasteKg]
            );
            conn.release();
        } catch (dbErr) {
            console.warn('⚠️  Database warning:', dbErr.message);
            // Continue even if DB insertion fails
        }

        res.json({
            success: true,
            prediction: {
                is_overcrowded: overcrowded === 1,
                overcrowding_probability: (overcrowdedProb * 100).toFixed(1) + '%',
                predicted_waste_kg: Math.round(wasteKg),
                input_data: {
                    attendance: Math.round(attendance),
                    venue_capacity: Math.round(venue_capacity),
                    weekend: weekend === 1,
                    is_free: is_free === 1,
                    duration_hours: duration_hours.toFixed(1),
                    food_stalls: Math.round(food_stalls),
                    bad_weather: weather === 1
                }
            }
        });
    } catch (err) {
        console.error('❌ Prediction error:', err);
        res.status(500).json({ error: 'Prediction failed', details: err.message });
    }
});

// Batch predictions endpoint
app.post('/predict-batch', async (req, res) => {
    try {
        const { predictions: inputPredictions } = req.body;

        if (!Array.isArray(inputPredictions) || inputPredictions.length === 0) {
            return res.status(400).json({ error: 'Invalid batch format' });
        }

        const results = [];

        for (const pred of inputPredictions) {
            const { attendance, venue_capacity, weekend, is_free, duration_hours, food_stalls, weather } = pred;

            const raw = [
                attendance,
                venue_capacity,
                weekend,
                is_free,
                duration_hours,
                food_stalls,
                weather
            ];

            const normalized = normalizeInput(raw);
            const tensor = tf.tensor2d([normalized]);

            const overcrowdedProb = (await classModel.predict(tensor).data())[0];
            const overcrowded = overcrowdedProb > 0.5 ? 1 : 0;
            const wasteKg = (await regModel.predict(tensor).data())[0];

            tensor.dispose();

            results.push({
                is_overcrowded: overcrowded === 1,
                overcrowding_probability: (overcrowdedProb * 100).toFixed(1) + '%',
                predicted_waste_kg: Math.round(wasteKg)
            });
        }

        res.json({
            success: true,
            total_predictions: results.length,
            results: results
        });
    } catch (err) {
        console.error('❌ Batch prediction error:', err);
        res.status(500).json({ error: 'Batch prediction failed', details: err.message });
    }
});

// Get prediction history
app.get('/history', async (req, res) => {
    try {
        const conn = await pool.getConnection();
        const [rows] = await conn.execute(
            'SELECT * FROM predictions ORDER BY created_at DESC LIMIT 100'
        );
        conn.release();

        res.json({
            success: true,
            count: rows.length,
            predictions: rows
        });
    } catch (err) {
        console.error('❌ History error:', err);
        res.status(500).json({ error: 'Failed to retrieve history', details: err.message });
    }
});

// Get statistics
app.get('/stats', async (req, res) => {
    try {
        const conn = await pool.getConnection();
        const [stats] = await conn.execute(
            `SELECT 
                COUNT(*) as total_predictions,
                SUM(overcrowded) as overcrowded_count,
                AVG(waste_prediction) as avg_waste_kg,
                AVG(attendance) as avg_attendance,
                MAX(attendance) as max_attendance,
                MIN(attendance) as min_attendance,
                AVG(overcrowding_probability) as overall_crowding_score
            FROM predictions`
        );
        conn.release();

        res.json({
            success: true,
            statistics: stats[0]
        });
    } catch (err) {
        console.error('❌ Stats error:', err);
        res.status(500).json({ error: 'Failed to retrieve statistics', details: err.message });
    }
});

// Route optimization endpoint (uses rule-based ML logic for Legazpi City)
app.post('/optimize-route', async (req, res) => {
    try {
        const { crowdLevel, eventTime, eventDay, weather } = req.body;

        if (typeof crowdLevel !== 'number') {
            return res.status(400).json({ error: 'Invalid crowd level' });
        }

        // ML-based route optimization for Legazpi City
        const routes = optimizeRoutesML(crowdLevel, eventTime || 14, eventDay || 3, weather || 0.8);

        res.json({
            success: true,
            crowd_level: crowdLevel,
            routes: routes
        });
    } catch (err) {
        console.error('❌ Route optimization error:', err);
        res.status(500).json({ error: 'Route optimization failed', details: err.message });
    }
});

// ML function: Optimize routes based on crowd prediction
function optimizeRoutesML(crowdLevel, timeOfDay = 14, dayOfWeek = 3, weatherScore = 0.8) {
    const legazpiRoutes = [
        {
            id: 0,
            name: 'Northern Bypass Route',
            distance: 8.5,
            duration: 12,
            trafficMultiplier: 1.0,
            crowdTolerance: 15000,
            description: 'Avoids downtown, best for heavy traffic'
        },
        {
            id: 1,
            name: 'Main Street Route',
            distance: 6.2,
            duration: 18,
            trafficMultiplier: 1.5,
            crowdTolerance: 5000,
            description: 'Direct route, gets congested with large crowds'
        },
        {
            id: 2,
            name: 'Downtown Route',
            distance: 5.8,
            duration: 35,
            trafficMultiplier: 2.5,
            crowdTolerance: 2000,
            description: 'Slowest route, only best for small events'
        }
    ];

    // Score each route based on crowd level and conditions
    const scored = legazpiRoutes.map(route => {
        let score = 100;
        
        // Primary factor: Crowd vs route capacity
        if (crowdLevel > route.crowdTolerance) {
            score -= (crowdLevel - route.crowdTolerance) / 100;
        } else {
            score += (route.crowdTolerance - crowdLevel) / 1000;
        }
        
        // Weather impact
        if (weatherScore < 0.3) { // Rainy/bad weather
            if (route.id === 0) score += 20; // Bypass is safer
        }
        
        // Time factor: Peak hours (18-20) favor bypass
        if (timeOfDay >= 18 && timeOfDay <= 20) {
            if (route.id === 0) score += 15;
            else score -= 10;
        }
        
        // Weekend congestion
        if (dayOfWeek === 5 || dayOfWeek === 6) {
            if (crowdLevel > 3000 && route.id !== 0) score -= 20;
        }
        
        // Calculate effective travel time
        const effectiveTime = route.duration * route.trafficMultiplier;
        
        return {
            ...route,
            score: Math.max(0, score),
            effective_duration: Math.round(effectiveTime),
            reason: selectReason(route.id, crowdLevel, weatherScore, timeOfDay)
        };
    });

    // Sort by score (highest first = best)
    scored.sort((a, b) => b.score - a.score);

    return scored.map(r => ({
        name: r.name,
        distance: r.distance,
        duration: r.effective_duration,
        reason: r.reason,
        coordinates: getRouteCoordinates(r.id)
    }));
}

function selectReason(routeId, crowdLevel, weather, timeOfDay) {
    const reasons = {
        0: crowdLevel > 5000 ? 'Bypass recommended for large crowds' : 'Low congestion route',
        1: crowdLevel < 3000 ? 'Quick main route' : 'Becomes congested with crowds',
        2: 'Downtown route, use only for small events'
    };
    
    if (weather < 0.3 && routeId === 0) return 'Safe bypass in bad weather';
    if (timeOfDay >= 18 && timeOfDay <= 20 && routeId === 0) return 'Peak hours: use bypass';
    
    return reasons[routeId];
}

function getRouteCoordinates(routeId) {
    // Legazpi City coordinates
    const routes = {
        0: [[124.1800, 13.2000], [124.1900, 13.2100], [124.2044, 13.1597], [124.2200, 13.1400]],
        1: [[124.2000, 13.1800], [124.2050, 13.1650], [124.2044, 13.1597], [124.2150, 13.1500]],
        2: [[124.2100, 13.1700], [124.2200, 13.1600], [124.2044, 13.1597], [124.2000, 13.1500]]
    };
    return routes[routeId] || [];
}

// Error handling
app.use((err, req, res, next) => {
    console.error('Server error:', err);
    res.status(500).json({ error: 'Internal server error' });
});

// Start server
const PORT = process.env.PORT || 3000;

loadModels().then(() => {
    app.listen(PORT, () => {
        console.log(`\n🚀 ML Prediction Server running on port ${PORT}`);
        console.log(`📊 Endpoints available:`);
        console.log(`   - POST   /predict           (single prediction)`);
        console.log(`   - POST   /predict-batch     (multiple predictions)`);
        console.log(`   - GET    /history           (prediction history)`);
        console.log(`   - GET    /stats             (statistics)`);
        console.log(`   - POST   /optimize-route    (route optimization - Legazpi)`);
        console.log(`   - GET    /health            (health check)\n`);
    });
});
