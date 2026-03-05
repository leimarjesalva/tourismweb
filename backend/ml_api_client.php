<?php
/**
 * ML Server API Client
 * Handles communication with the TensorFlow.js ML server for predictions
 */

class MLServerClient {
    private $ml_server_url = 'http://localhost:3000';
    private $timeout = 10;
    
    /**
     * Get crowd prediction for an event
     */
    public function predictCrowd($attendance, $venue_capacity, $weekend, $is_free, $duration_hours, $food_stalls, $weather) {
        $payload = [
            'attendance' => (float)$attendance,
            'venue_capacity' => (float)$venue_capacity,
            'weekend' => (int)$weekend,
            'is_free' => (int)$is_free,
            'duration_hours' => (float)$duration_hours,
            'food_stalls' => (float)$food_stalls,
            'weather' => (int)$weather
        ];
        
        return $this->makeRequest('/predict', $payload);
    }
    
    /**
     * Batch predictions for multiple events
     */
    public function predictBatch($events) {
        $payload = ['events' => $events];
        return $this->makeRequest('/predict-batch', $payload);
    }
    
    /**
     * Get prediction history
     */
    public function getHistory($limit = 100) {
        return $this->makeRequest('/history?limit=' . $limit, null, 'GET');
    }
    
    /**
     * Get ML server statistics
     */
    public function getStats() {
        return $this->makeRequest('/stats', null, 'GET');
    }
    
    /**
     * Check if ML server is healthy
     */
    public function checkHealth() {
        try {
            $response = $this->makeRequest('/health', null, 'GET');
            return $response['status'] === 'ML Server is running ✅';
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Make HTTP request to ML server
     */
    private function makeRequest($endpoint, $data = null, $method = 'POST') {
        $url = $this->ml_server_url . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("ML Server Error: $error");
        }
        
        if ($http_code !== 200) {
            throw new Exception("ML Server returned HTTP $http_code");
        }
        
        return json_decode($response, true);
    }
}

// Initialize client globally
$ml_client = new MLServerClient();

/**
 * Helper function to get crowd prediction
 */
function getPrediction($attendance, $venue_capacity, $weekend, $is_free, $duration_hours, $food_stalls, $weather) {
    global $ml_client;
    try {
        $result = $ml_client->predictCrowd($attendance, $venue_capacity, $weekend, $is_free, $duration_hours, $food_stalls, $weather);
        return [
            'success' => true,
            'data' => $result
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Helper function to check ML server status
 */
function checkMLServer() {
    global $ml_client;
    return $ml_client->checkHealth();
}

/**
 * Helper function to save prediction to database
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
            $prediction_data['raw_input'][0] ?? 0,
            $prediction_data['raw_input'][1] ?? 0,
            $prediction_data['raw_input'][2] ?? 0,
            $prediction_data['raw_input'][3] ?? 0,
            $prediction_data['raw_input'][4] ?? 0,
            $prediction_data['raw_input'][5] ?? 0,
            $prediction_data['raw_input'][6] ?? 0,
            $prediction_data['overcrowded'] ?? 0,
            $prediction_data['waste_prediction'] ?? 0,
            $prediction_data['overcrowding_probability'] ?? 0
        ]);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
