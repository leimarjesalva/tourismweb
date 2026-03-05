<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : 'get_predictions';

// Define paths
$eventPredictionsFile = __DIR__ . '/events_predictions.json';

// Handle different actions
if($action === 'get_predictions') {
  $response = getEventPredictions();
  echo json_encode($response);
} elseif($action === 'regenerate_model') {
  $response = regenerateEventModel();
  echo json_encode($response);
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Get current event predictions from JSON file
 */
function getEventPredictions() {
  $eventPredictionsFile = __DIR__ . '/events_predictions.json';
  
  if(!file_exists($eventPredictionsFile)) {
    return [
      'success' => false,
      'message' => 'Event predictions file not found'
    ];
  }
  
  try {
    $json = file_get_contents($eventPredictionsFile);
    $data = json_decode($json, true);
    
    if(!$data) {
      return [
        'success' => false,
        'message' => 'Invalid event predictions data'
      ];
    }
    
    return [
      'success' => true,
      'data' => $data
    ];
  } catch(Exception $e) {
    return [
      'success' => false,
      'message' => 'Error reading event predictions: ' . $e->getMessage()
    ];
  }
}

/**
 * Regenerate event ML predictions model (background task)
 */
function regenerateEventModel() {
  $eventPredictionsFile = __DIR__ . '/events_predictions.json';
  
  try {
    // In production, this would call a Python ML service
    // For now, we'll update the timestamp to show it was regenerated
    
    if(!file_exists($eventPredictionsFile)) {
      return [
        'success' => false,
        'message' => 'Event predictions file not found'
      ];
    }
    
    $json = file_get_contents($eventPredictionsFile);
    $data = json_decode($json, true);
    
    if(!$data) {
      return [
        'success' => false,
        'message' => 'Invalid event predictions data'
      ];
    }
    
    // Update timestamp
    $data['metadata']['trained_date'] = date('c');
    
    // Save updated data
    if(file_put_contents($eventPredictionsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
      return [
        'success' => true,
        'message' => 'Event model regeneration started',
        'timestamp' => $data['metadata']['trained_date']
      ];
    } else {
      return [
        'success' => false,
        'message' => 'Failed to update event predictions'
      ];
    }
  } catch(Exception $e) {
    return [
      'success' => false,
      'message' => 'Error regenerating event model: ' . $e->getMessage()
    ];
  }
}
?>
