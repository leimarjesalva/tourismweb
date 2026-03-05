const tf = require('@tensorflow/tfjs');
require('@tensorflow/tfjs-backend-cpu');
const fs = require('fs');
const path = require('path');

// Set CPU backend explicitly
tf.setBackend('cpu');

// Generate Synthetic Dataset
function generateData(rows = 1000) {
    const data = [];
    const labelsClass = [];
    const labelsReg = [];

    for (let i = 0; i < rows; i++) {

        const attendance = 5000 + Math.random() * 30000;
        const venue_capacity = 10000 + Math.random() * 30000;
        const weekend = Math.random() > 0.5 ? 1 : 0;
        const is_free = Math.random() > 0.4 ? 1 : 0;
        const duration_hours = 3 + Math.random() * 7;
        const food_stalls = 10 + Math.random() * 100;
        const weather = Math.random() > 0.8 ? 1 : 0;

        const capacity_ratio = attendance / venue_capacity;

        const overcrowded =
            capacity_ratio > 0.9 ||
            (weekend && is_free && capacity_ratio > 0.75) ? 1 : 0;

        const waste =
            attendance * 0.6 * (duration_hours / 8) +
            food_stalls * 15 +
            weather * 500 +
            Math.random() * 1000;

        data.push([
            attendance,
            venue_capacity,
            weekend,
            is_free,
            duration_hours,
            food_stalls,
            weather
        ]);

        labelsClass.push(overcrowded);
        labelsReg.push(waste);
    }

    return { data, labelsClass, labelsReg };
}

async function train() {

    console.log('🤖 Starting ML Model Training...\n');
    console.log('📊 Generating synthetic festival dataset...');
    const { data, labelsClass, labelsReg } = generateData(1000);

    let xs = tf.tensor2d(data);
    let ysClass = tf.tensor2d(labelsClass, [labelsClass.length, 1]);
    let ysReg = tf.tensor2d(labelsReg, [labelsReg.length, 1]);

    // Normalize
    console.log('📐 Normalizing data...');
    const { mean, variance } = tf.moments(xs);
    const std = tf.sqrt(variance);
    xs = xs.sub(mean).div(std);

    const normPath = path.join(__dirname, 'normalization.json');
    fs.writeFileSync(normPath,
        JSON.stringify({
            mean: mean.arraySync(),
            std: std.arraySync()
        }, null, 2)
    );
    console.log(`✅ Normalization parameters saved to ${normPath}\n`);

    const split = Math.floor(0.8 * data.length);

    const xTrain = xs.slice([0, 0], [split, -1]);
    const xTest = xs.slice([split, 0], [-1, -1]);

    const yTrainClass = ysClass.slice([0, 0], [split, -1]);
    const yTestClass = ysClass.slice([split, 0], [-1, -1]);

    const yTrainReg = ysReg.slice([0, 0], [split, -1]);
    const yTestReg = ysReg.slice([split, 0], [-1, -1]);

    function buildModel(units, lr) {
        const model = tf.sequential({
            layers: [
                tf.layers.dense({ units, activation: 'relu', inputShape: [7] }),
                tf.layers.dense({ units: units / 2, activation: 'relu' }),
                tf.layers.dense({ units: 1, activation: 'sigmoid' })
            ]
        });

        model.compile({
            optimizer: tf.train.adam(lr),
            loss: 'binaryCrossentropy',
            metrics: ['accuracy']
        });

        return model;
    }

    const configs = [
        { units: 32, lr: 0.001 },
        { units: 64, lr: 0.001 },
        { units: 32, lr: 0.0005 }
    ];

    console.log('🎯 Training classification model (overcrowding detection)...\n');
    let bestAccuracy = 0;
    let bestModel;
    let bestConfig;

    for (let idx = 0; idx < configs.length; idx++) {
        let config = configs[idx];
        console.log(`  Config ${idx + 1}/3: ${config.units} units, LR ${config.lr}`);

        const model = buildModel(config.units, config.lr);
        await model.fit(xTrain, yTrainClass, { epochs: 50, verbose: 0 });

        const evalResult = model.evaluate(xTest, yTestClass);
        const accuracy = (await evalResult[1].data())[0];

        console.log(`  ✓ Accuracy: ${accuracy.toFixed(3)} (${(accuracy * 100).toFixed(1)}%)\n`);

        if (accuracy > bestAccuracy) {
            bestAccuracy = accuracy;
            bestModel = model;
            bestConfig = config;
        }
    }

    console.log(`🏆 Best Classification Model:`);
    console.log(`  - Units: ${bestConfig.units}`);
    console.log(`  - Learning Rate: ${bestConfig.lr}`);
    console.log(`  - Accuracy: ${(bestAccuracy * 100).toFixed(1)}%\n`);

    const classModelPath = path.join(__dirname, 'best-overcrowding-model');
    await bestModel.save(`file://${classModelPath}`);
    console.log(`✅ Classification model saved to ${classModelPath}\n`);

    // Regression model for waste prediction
    console.log('🎯 Training regression model (waste prediction)...\n');
    const regModel = tf.sequential({
        layers: [
            tf.layers.dense({ units: 64, activation: 'relu', inputShape: [7] }),
            tf.layers.dense({ units: 32, activation: 'relu' }),
            tf.layers.dense({ units: 1 })
        ]
    });

    regModel.compile({
        optimizer: tf.train.adam(0.001),
        loss: 'meanSquaredError'
    });

    await regModel.fit(xTrain, yTrainReg, { epochs: 50, verbose: 0 });

    const regEvalResult = regModel.evaluate(xTest, yTestReg);
    const regMSE = (await regEvalResult.data())[0];
    console.log(`  ✓ Mean Squared Error: ${regMSE.toFixed(2)}\n`);

    const regModelPath = path.join(__dirname, 'best-waste-model');
    await regModel.save(`file://${regModelPath}`);
    console.log(`✅ Regression model saved to ${regModelPath}\n`);

    console.log('🎉 All models trained and saved successfully!');
    console.log('\n✨ Training complete! Ready to run the ML server.\n');

    process.exit(0);
}

train().catch(err => {
    console.error('❌ Training failed:', err);
    process.exit(1);
});
