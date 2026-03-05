<?php
require_once __DIR__ . '/db.php';
session_start();

// Set admin session for testing
$_SESSION['is_admin'] = true;

$db = get_db();

// Get all events without predictions
$res = $db->query("SELECT id, title, capacity FROM events WHERE prediction IS NULL OR prediction = '' LIMIT 5");

if (!$res) {
    echo "Error: " . $db->error . "\n";
    exit;
}

echo "Generating predictions for events...\n\n";

while($ev = $res->fetch_assoc()) {
    $event_id = $ev['id'];
    $capacity = $ev['capacity'] ?? 50000;
    
    // Generate a sample prediction using same logic as generateDayPrediction()
    $prediction = [
        'attendance' => (int)($capacity * 0.7),
        'expected_visitors' => (int)($capacity * 0.7),
        'waste_prediction' => (int)(($capacity * 0.7) * 0.08),
        'predicted_waste_kg' => (int)(($capacity * 0.7) * 0.08),
        'waste' => ['total_kg' => (int)(($capacity * 0.7) * 0.08)],
        'scenarios' => [
            'low' => [
                'attendance' => (int)($capacity * 0.5),
                'waste_prediction' => (int)(($capacity * 0.5) * 0.08),
                'overcrowding_probability' => 0.2
            ],
            'medium' => [
                'attendance' => (int)($capacity * 0.7),
                'waste_prediction' => (int)(($capacity * 0.7) * 0.08),
                'overcrowding_probability' => 0.5
            ],
            'high' => [
                'attendance' => (int)($capacity * 0.85),
                'waste_prediction' => (int)(($capacity * 0.85) * 0.08),
                'overcrowding_probability' => 0.75
            ]
        ],
        'hourly' => array_fill(0, 24, (int)($capacity * 0.7 / 8)),
        'hourlyWaste' => array_fill(0, 24, (int)(($capacity * 0.7 / 8) * 0.08)),
        'phase' => [
            'arrival' => (int)($capacity * 0.2),
            'peak' => (int)($capacity * 0.5),
            'leaving' => (int)($capacity * 0.3)
        ]
    ];
    
    $prediction_json = json_encode($prediction);
    
    $stmt = $db->prepare('UPDATE events SET prediction=? WHERE id=?');
    $stmt->bind_param('si', $prediction_json, $event_id);
    
    if ($stmt->execute()) {
        echo "✅ Event ID {$event_id} ({$ev['title']}): Prediction saved\n";
        echo "   Expected visitors: " . $prediction['expected_visitors'] . "\n";
        echo "   Predicted waste: " . $prediction['predicted_waste_kg'] . " kg\n";
    } else {
        echo "❌ Event ID {$event_id}: Failed - " . $stmt->error . "\n";
    }
}

echo "\nDone! Check the guest site to see predictions.\n";
?>
