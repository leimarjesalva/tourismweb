# ML Model Training Process

## Overview
Custom ML models are trained using **TensorFlow.js** in `backend/ml/train.js`. Two models are created:

1. **Overcrowding Classification** (Binary: Safe/Overcrowded)
2. **Waste Prediction** (Regression: kg waste generated)

## Training Dataset
**Synthetic data generation** (1000 rows, 80/20 train/test split):

| Feature | Description | Range/Example |
|---------|-------------|---------------|
| attendance | Expected visitors | 5000-35000 |
| venue_capacity | Max safe capacity | 10000-40000 |
| weekend | Event on weekend? | 0/1 |
| is_free | Free admission? | 0/1 |
| duration_hours | Event length | 3-10 hrs |
| food_stalls | Number of vendors | 10-110 |
| weather | Good weather? | 0/1 |

**Labels**:
- `overcrowded`: 1 if capacity_ratio > 0.9 or (weekend+free+ratio>0.75)
- `waste_kg`: attendance × factors + noise

## Normalization
Features normalized: `(x - mean) / std`
- Parameters auto-saved to `normalization.json`
- Ensures model stability across input scales

## Model Architectures

### Classification Model (Overcrowding)
```
Input (7) → Dense(32/64, relu) → Dense(32, relu) → Dense(1, sigmoid)
Loss: binaryCrossentropy
Optimizer: Adam (lr=0.001/0.0005)
```

### Regression Model (Waste)
```
Input (7) → Dense(64, relu) → Dense(32, relu) → Dense(1, linear)  
Loss: meanSquaredError
Optimizer: Adam (lr=0.001)
```

## Hyperparameter Search
**Grid search** over 3 configurations:
```
Config 1: 32 units, lr=0.001
Config 2: 64 units, lr=0.001  
Config 3: 32 units, lr=0.0005
```
- Train 50 epochs per config
- **Best model selected by test accuracy**
- Saved to `best-overcrowding-model/` and `best-waste-model/`

## Training Workflow
```bash
cd backend/ml
npm install
node train.js
```

**Console Output Example**:
```
📊 Generating synthetic festival dataset...
📐 Normalizing data...
🎯 Training classification model...
  Config 1/3: 32 units, LR 0.001 → Accuracy: 0.923 (92.3%)
  Config 2/3: 64 units, LR 0.001 → Accuracy: 0.915 (91.5%)
  Config 3/3: 32 units, LR 0.0005 → Accuracy: 0.938 (93.8%)
🏆 Best: 32 units, lr=0.0005, 93.8% accuracy
✅ Models saved!

🎯 Training regression model...
  Mean Squared Error: 1234.56
✅ Training complete!
```

## Model Persistence
```
best-overcrowding-model/
├── model.json (weights + topology)
├── weights.bin
└── group1-shard1of1.bin

best-waste-model/ (similar structure)
normalization.json (mean/std per feature)
```

## Server Loading
`server.js` auto-loads trained models on startup:
```js
if (fs.existsSync(modelJsonPath)) {
    model = await tf.loadLayersModel(`file://${modelJsonPath}`);
}
```

## Retraining Triggers
- Manually: `node train.js`
- Data drift detected (>10% prediction error)
- New venue/event types added
- Monthly scheduled (cron/Docker)
