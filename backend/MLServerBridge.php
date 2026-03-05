<?php
/**
 * Legazpi Festival ML Server Bridge
 * 
 * This file integrates the Node.js ML server with the existing PHP backend
 * allowing festival predictions through PHP API endpoints
 */

class MLServerBridge {
    private $ml_server_url = 'http://localhost:3000';
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Set ML server URL (for custom configuration)
     */
    public function setServerUrl($url) {
        $this->ml_server_url = $url;
    }
    
    /**
     * Check if ML server is running
     */
    public function isServerRunning() {
        try {
            $response = @file_get_contents($this->ml_server_url . '/health');
            return $response !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get single prediction from ML server
     */
    public function predictFestivalCrowding($data) {
        try {
            $payload = json_encode([
                'attendance' => (float)$data['attendance'],
                'venue_capacity' => (float)$data['venue_capacity'],
                'weekend' => (int)($data['weekend'] ?? 0),
                'is_free' => (int)($data['is_free'] ?? 0),
                'duration_hours' => (float)($data['duration_hours'] ?? 6),
                'food_stalls' => (float)($data['food_stalls'] ?? 20),
                'weather' => (int)($data['weather'] ?? 0)
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $payload,
                    'timeout' => 10
                ]
            ]);
            
            $response = @file_get_contents(
                $this->ml_server_url . '/predict',
                false,
                $context
            );
            
            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'ML Server unavailable',
                    'fallback' => true
                ];
            }
            
            $result = json_decode($response, true);
            
            // Store in database if successful
            if ($result['success']) {
                $this->storePrediction($data, $result['prediction']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback' => true
            ];
        }
    }
    
    /**
     * Get batch predictions
     */
    public function predictBatch($dataArray) {
        try {
            $predictions = array_map(function($data) {
                return [
                    'attendance' => (float)$data['attendance'],
                    'venue_capacity' => (float)$data['venue_capacity'],
                    'weekend' => (int)($data['weekend'] ?? 0),
                    'is_free' => (int)($data['is_free'] ?? 0),
                    'duration_hours' => (float)($data['duration_hours'] ?? 6),
                    'food_stalls' => (float)($data['food_stalls'] ?? 20),
                    'weather' => (int)($data['weather'] ?? 0)
                ];
            }, $dataArray);
            
            $payload = json_encode(['predictions' => $predictions]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $payload,
                    'timeout' => 30
                ]
            ]);
            
            $response = @file_get_contents(
                $this->ml_server_url . '/predict-batch',
                false,
                $context
            );
            
            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'ML Server unavailable'
                ];
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get prediction history
     */
    public function getPredictionHistory($limit = 100) {
        try {
            $response = @file_get_contents(
                $this->ml_server_url . '/history',
                false,
                stream_context_create(['http' => ['timeout' => 5]])
            );
            
            if ($response === false) {
                return ['success' => false, 'error' => 'ML Server unavailable'];
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get ML server statistics
     */
    public function getStatistics() {
        try {
            $response = @file_get_contents(
                $this->ml_server_url . '/stats',
                false,
                stream_context_create(['http' => ['timeout' => 5]])
            );
            
            if ($response === false) {
                return ['success' => false, 'error' => 'ML Server unavailable'];
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Store prediction in local database
     */
    private function storePrediction($input, $prediction) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO ibalong_ai.predictions 
                (attendance, venue_capacity, weekend, is_free, duration_hours, food_stalls, weather, overcrowded, waste_prediction)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->bind_param(
                'ddiiddifi',
                $input['attendance'],
                $input['venue_capacity'],
                $input['weekend'],
                $input['is_free'],
                $input['duration_hours'],
                $input['food_stalls'],
                $input['weather'],
                $prediction['prediction']['is_overcrowded'] ? 1 : 0,
                $prediction['prediction']['predicted_waste_kg']
            );
            
            $stmt->execute();
            $stmt->close();
            
        } catch (Exception $e) {
            // Log but don't throw - prediction was successful
            error_log('DB storage error: ' . $e->getMessage());
        }
    }
    
    /**
     * Simple fallback prediction (if ML server unavailable)
     */
    public function getFallbackPrediction($data) {
        $attendance = (float)$data['attendance'];
        $capacity = (float)$data['venue_capacity'];
        $is_free = (int)($data['is_free'] ?? 0);
        $weekend = (int)($data['weekend'] ?? 0);
        $food_stalls = (float)($data['food_stalls'] ?? 20);
        $duration = (float)($data['duration_hours'] ?? 6);
        
        $capacity_ratio = $attendance / $capacity;
        
        // Simple heuristics
        $overcrowded = (
            $capacity_ratio > 0.9 ||
            ($weekend && $is_free && $capacity_ratio > 0.75)
        ) ? 1 : 0;
        
        $waste = $attendance * 0.6 * ($duration / 8) + $food_stalls * 15 + 500;
        
        return [
            'success' => true,
            'fallback' => true,
            'prediction' => [
                'is_overcrowded' => (bool)$overcrowded,
                'overcrowding_probability' => round($capacity_ratio * 100, 1) . '%',
                'predicted_waste_kg' => (int)$waste
            ]
        ];
    }
}

?>
