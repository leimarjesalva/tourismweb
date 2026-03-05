const tf = require('@tensorflow/tfjs');
require('@tensorflow/tfjs-backend-cpu');
const fs = require('fs');
const path = require('path');

tf.setBackend('cpu');

console.log('🚗 ROUTE OPTIMIZATION ML MODEL TRAINER');
console.log('=====================================\n');

// Generate synthetic training data for route optimization
// Features: crowdLevel, timeOfDay, dayOfWeek, weather, eventType, eventSize
// Output: bestRouteIndex (0=Northern, 1=Main, 2=Downtown)
function generateRouteData(samples = 500) {
    const data = [];
    const labels = [];
    
    for (let i = 0; i < samples; i++) {
        const crowdLevel = Math.random() * 15000; // 0-15000 expected visitors
        const timeOfDay = Math.random() * 24; // 0-24 hours
        const dayOfWeek = Math.floor(Math.random() * 7); // 0-6 (Mon-Sun)
        const weather = Math.random(); // 0=Rain, 1=Clear (normalized)
        const eventType = Math.random(); // 0=Festival, 1=Concert
        const eventSize = Math.random(); // 0=Small, 1=Large
        
        // ML Logic: Route selection based on crowd level
        let bestRoute = 0; // Northern bypass (default best)
        
        if (crowdLevel > 5000 && crowdLevel <= 10000) {
            // Medium crowd: Main street might work
            bestRoute = Math.random() > 0.6 ? 0 : 1;
        } else if (crowdLevel > 10000) {
            // Large crowd: Must use bypass
            bestRoute = 0;
        } else {
            // Small crowd: Main street is faster
            bestRoute = timeOfDay >= 8 && timeOfDay <= 18 ? 1 : 0;
        }
        
        // Weather affects routes
        if (weather < 0.3) { // Rainy
            bestRoute = 0; // Bypass is safer
        }
        
        // Weekend or event size affects congestion
        if (dayOfWeek === 5 || dayOfWeek === 6) { // Weekend
            if (crowdLevel > 3000) bestRoute = 0;
        }
        
        data.push([crowdLevel, timeOfDay, dayOfWeek, weather, eventType, eventSize]);
        labels.push(bestRoute);
    }
    
    return { data, labels };
}

// Create route optimization model
function createRouteModel() {
    console.log('🏗️  Creating Route Optimization Neural Network...\n');
    
    const model = tf.sequential({
        layers: [
            tf.layers.dense({
                units: 64,
                activation: 'relu',
                inputShape: [6],
                kernelInitializer: 'heNormal'
            }),
            tf.layers.dropout({ rate: 0.3 }),
            
            tf.layers.dense({
                units: 32,
                activation: 'relu',
                kernelInitializer: 'heNormal'
            }),
            tf.layers.dropout({ rate: 0.2 }),
            
            tf.layers.dense({
                units: 16,
                activation: 'relu'
            }),
            
            // Output layer: 3 classes (0=Northern, 1=Main, 2=Downtown)
            tf.layers.dense({
                units: 3,
                activation: 'softmax'
            })
        ]
    });
    
    model.compile({
        optimizer: tf.train.adam(0.005),
        loss: 'sparseCategoricalCrossentropy',
        metrics: ['accuracy']
    });
    
    console.log('✓ Model architecture ready\n');
    return model;
}

// Train the model
async function trainRouteModel() {
    console.log('📊 Generating training data...');
    const { data, labels } = generateRouteData(1000);
    
    // Normalize data
    const xs = tf.tensor2d(data);
    const ys = tf.tensor1d(labels, 'int32');
    
    // Get min/max for normalization
    const min = xs.min();
    const max = xs.max();
    const normalized = xs.sub(min).div(max.sub(min));
    
    console.log('✓ Data generated and normalized\n');
    
    const model = createRouteModel();
    
    console.log('🎯 Training model...\n');
    
    // Train with callbacks
    const history = await model.fit(normalized, ys, {
        epochs: 100,
        batchSize: 32,
        validationSplit: 0.2,
        shuffle: true,
        verbose: 0,
        callbacks: {
            onEpochEnd: (epoch, logs) => {
                if ((epoch + 1) % 10 === 0) {
                    console.log(`Epoch ${epoch + 1}: loss = ${logs.loss.toFixed(4)}, accuracy = ${logs.acc.toFixed(4)}`);
                }
            }
        }
    });
    
    console.log('\n✅ Training complete!\n');
    
    // Cleanup
    min.dispose();
    max.dispose();
    normalized.dispose();
    ys.dispose();
    xs.dispose();
    
    return model;
}

// Test predictions
async function testModel(model) {
    console.log('🧪 Testing predictions...\n');
    
    // Test cases: [crowdLevel, timeOfDay, dayOfWeek, weather, eventType, eventSize]
    const testCases = [
        { data: [2000, 14, 3, 0.8, 0, 0], label: 'Small crowd, afternoon, clear' },
        { data: [8000, 18, 5, 0.9, 1, 1], label: 'Medium crowd, evening, Friday' },
        { data: [12000, 16, 6, 0.7, 1, 1], label: 'Large crowd, afternoon, Saturday' },
        { data: [5000, 9, 1, 0.2, 0, 0], label: 'Small-medium, rainy' }
    ];
    
    const routeNames = ['🟢 Northern Bypass', '🟠 Main Street', '🔴 Downtown'];
    
    for (const test of testCases) {
        const input = tf.tensor2d([test.data]);
        const prediction = model.predict(input);
        const probs = await prediction.data();
        const routeIdx = Array.from(probs).indexOf(Math.max(...probs));
        
        console.log(`Scenario: ${test.label}`);
        console.log(`  Recommendation: ${routeNames[routeIdx]}`);
        console.log(`  Confidence: ${(Math.max(...probs) * 100).toFixed(1)}%\n`);
        
        prediction.dispose();
        input.dispose();
    }
}

// Save model
async function saveModel(model) {
    console.log('💾 Saving model...');
    
    const modelPath = path.join(__dirname, 'best-route-model');
    
    // Create directory if it doesn't exist
    if (!fs.existsSync(modelPath)) {
        fs.mkdirSync(modelPath, { recursive: true });
    }
    
    await model.save(`file://${modelPath}`);
    
    console.log(`✓ Model saved to ${modelPath}\n`);
}

// Main execution
async function main() {
    try {
        console.log('Starting Route Optimization Training...\n');
        
        const model = await trainRouteModel();
        await testModel(model);
        await saveModel(model);
        
        console.log('✅ Route optimization model training complete!');
        console.log('   Outputs: best-route-model/\n');
        
        model.dispose();
        
    } catch (err) {
        console.error('❌ Error:', err.message);
        process.exit(1);
    }
}

main();
