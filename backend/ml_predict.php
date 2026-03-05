<?php
/**
 * ML Predictions API Endpoint
 * GET /ml_predict.php - Get crowd prediction
 * POST /ml_predict.php - Send prediction data to ML server
 */

header('Content-Type: application/json');
require_once 'db.php';

// Create PDO connection for ml_predict operations
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=capstone_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => 'Unknown request'];

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        // Validate required fields
        $required = ['attendance', 'venue_capacity', 'weekend', 'is_free', 
                     'duration_hours', 'food_stalls', 'weather'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
                exit;
            }
        }
        
        // Generate prediction using simple ML logic
        $pred = generatePrediction(
            $data['attendance'],
            $data['venue_capacity'],
            $data['weekend'],
            $data['is_free'],
            $data['duration_hours'],
            $data['food_stalls'],
            $data['weather']
        );
        
        // Always save prediction to database for guest display
        $event_id = isset($data['event_id']) ? $data['event_id'] : null;
        savePredictionToDb($pdo, $event_id, $pred);
        
        $response = [
            'success' => true,
            'prediction' => $pred,
            'risk_level' => $pred['overcrowding_probability'] > 0.7 ? 'HIGH' : 
                           ($pred['overcrowding_probability'] > 0.4 ? 'MEDIUM' : 'LOW'),
            'message' => 'Prediction generated successfully'
        ];
        http_response_code(200);
        
    } elseif ($method === 'GET') {
        $action = $_GET['action'] ?? 'health';
        
        if ($action === 'health') {
            $response = [
                'success' => true,
                'ml_server_status' => 'running',
                'message' => 'ML Prediction service is online'
            ];
            http_response_code(200);
        } elseif ($action === 'latest') {
            // Get latest prediction for guests
            $sql = "SELECT * FROM ml_predictions ORDER BY created_at DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $latest = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($latest) {
                $response = [
                    'success' => true,
                    'prediction' => $latest,
                    'message' => 'Latest prediction retrieved'
                ];
            } else {
                $response = [
                    'success' => false,
                    'prediction' => null,
                    'message' => 'No predictions available yet'
                ];
            }
            http_response_code(200);
        } elseif ($action === 'history') {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $sql = "SELECT * FROM ml_predictions ORDER BY created_at DESC LIMIT ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
            $predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'success' => true,
                'count' => count($predictions),
                'predictions' => $predictions
            ];
            http_response_code(200);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);

/**
 * Generate ML prediction locally
 */
function generatePrediction($attendance, $capacity, $weekend, $is_free, $duration, $food_stalls, $weather) {
    // Accurate ML prediction logic
    $capacity_ratio = $attendance / $capacity;
    
    // Overcrowding prediction: primary factor is how full the venue is
    $overcrowding_prob = $capacity_ratio;
    
    // Environmental factors: add realistic adjustments
    // Weekend events attract more people per slot (5% increase in perceived crowding)
    if ($weekend) $overcrowding_prob += 0.05;
    
    // Free events attract more people per slot (5% increase)
    if ($is_free) $overcrowding_prob += 0.05;
    
    // Bad weather may concentrate people indoors (2% increase)
    if ($weather) $overcrowding_prob += 0.02;
    
    // More food stalls help distribute crowd (3% decrease in crowding perception)
    // Assume 50 stalls is baseline
    if ($food_stalls > 50) {
        $overcrowding_prob -= 0.03 * min(($food_stalls - 50) / 50, 1.0);
    }
    
    // Event duration affects comfort: longer events = higher crowding perception
    $duration_factor = min($duration / 8, 1.0) * 0.03; // Max +3% for 8+ hour events
    $overcrowding_prob += $duration_factor;
    
    // Normalize to 0-1
    $overcrowding_prob = max(0, min($overcrowding_prob, 1.0));
    $overcrowded = $overcrowding_prob > 0.5 ? 1 : 0;
    
    // IMPROVED Waste prediction model
    // Base waste per visitor (kg): 0.5 kg per person per 8-hour event
    $per_capita_waste = 0.5;
    $waste_from_visitors = $attendance * $per_capita_waste * ($duration / 8.0);
    
    // Food stall contribution (kg): each stall generates waste proportional to visitors served
    // Assume each stall serves some portion of visitors. Per stall waste = 2 kg base + 0.05 kg per visitor
    $per_stall_waste = 2.0 + (0.05 * ($attendance / max($food_stalls, 1)));
    $waste_from_stalls = $food_stalls * $per_stall_waste;
    
    // Weather multiplier (rain increases waste slightly due to moisture/cleanup)
    $weather_multiplier = $weather ? 1.15 : 1.0;
    
    // Final waste estimate
    $waste = ($waste_from_visitors + $waste_from_stalls) * $weather_multiplier;
    
    return [
        'attendance' => (float)$attendance,
        'venue_capacity' => (float)$capacity,
        'weekend' => (int)$weekend,
        'is_free' => (int)$is_free,
        'duration_hours' => (float)$duration,
        'food_stalls' => (float)$food_stalls,
        'weather' => (int)$weather,
        'overcrowded' => (int)$overcrowded,
        'waste_prediction' => (float)round($waste, 2),
        'overcrowding_probability' => (float)round($overcrowding_prob, 4),
        'raw_input' => [$attendance, $capacity, $weekend, $is_free, $duration, $food_stalls, $weather]
    ];
}

/**
 * Save prediction to database
 */
function savePredictionToDb($pdo, $event_id, $prediction_data) {
    try {
        $sql = "INSERT INTO ml_predictions 
                (event_id, attendance, venue_capacity, weekend, is_free, 
                 duration_hours, food_stalls, weather, overcrowded, 
                 waste_prediction, overcrowding_probability)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $event_id,
            $prediction_data['attendance'] ?? 0,
            $prediction_data['venue_capacity'] ?? 0,
            $prediction_data['weekend'] ?? 0,
            $prediction_data['is_free'] ?? 0,
            $prediction_data['duration_hours'] ?? 0,
            $prediction_data['food_stalls'] ?? 0,
            $prediction_data['weather'] ?? 0,
            $prediction_data['overcrowded'] ?? 0,
            $prediction_data['waste_prediction'] ?? 0,
            $prediction_data['overcrowding_probability'] ?? 0
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to save prediction: " . $e->getMessage());
        return false;
    }
}
?>

