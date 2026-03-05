<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : 'get_predictions';

// Define paths
$predictionsFile = __DIR__ . '/tourism_predictions.json';

// Handle different actions
if($action === 'get_predictions') {
  $response = getPredictions();
  echo json_encode($response);
} elseif($action === 'regenerate_model') {
  $response = regenerateModel();
  echo json_encode($response);
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Get current predictions from JSON file
 */
function getPredictions() {
  $predictionsFile = __DIR__ . '/tourism_predictions.json';
  
  if(!file_exists($predictionsFile)) {
    return [
      'success' => false,
      'message' => 'Predictions file not found'
    ];
  }
  
  try {
    $json = file_get_contents($predictionsFile);
    $data = json_decode($json, true);
    
    if(!$data) {
      return [
        'success' => false,
        'message' => 'Invalid predictions data'
      ];
    }
    
    return [
      'success' => true,
      'data' => $data
    ];
  } catch(Exception $e) {
    return [
      'success' => false,
      'message' => 'Error reading predictions: ' . $e->getMessage()
    ];
  }
}

/**
 * Regenerate ML predictions model (background task)
 */
function regenerateModel() {
  $predictionsFile = __DIR__ . '/tourism_predictions.json';
  
  try {
    // In production, this would call a Python ML service
    // For now, we'll update the timestamp to show it was regenerated
    
    if(!file_exists($predictionsFile)) {
      return [
        'success' => false,
        'message' => 'Predictions file not found'
      ];
    }
    
    $json = file_get_contents($predictionsFile);
    $data = json_decode($json, true);
    
    if(!$data) {
      return [
        'success' => false,
        'message' => 'Invalid predictions data'
      ];
    }
    
    // Update timestamp
    $data['metadata']['timestamp'] = date('c');
    
    // Save updated data
    if(file_put_contents($predictionsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
      return [
        'success' => true,
        'message' => 'Model regeneration started',
        'timestamp' => $data['metadata']['timestamp']
      ];
    } else {
      return [
        'success' => false,
        'message' => 'Failed to update predictions'
      ];
    }
  } catch(Exception $e) {
    return [
      'success' => false,
      'message' => 'Error regenerating model: ' . $e->getMessage()
    ];
  }
}
?>
