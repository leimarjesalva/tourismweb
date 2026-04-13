<?php
/**
 * NLP Analytics API
 * Integrates Python NLP module with PHP backend
 * Analyzes guest feedback using sentiment analysis, keyword extraction, and topic modeling
 */

header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch($action) {
        case 'analyze_shop_feedback':
            analyzeShopFeedback($conn);
            break;
        
        case 'analyze_all_feedback':
            analyzeAllFeedback($conn);
            break;
        
        case 'analyze_sentiment':
            analyzeSentiment();
            break;
        
        case 'extract_keywords':
            extractKeywords();
            break;
        
        case 'get_analytics_summary':
            getAnalyticsSummary($conn);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log('NLP Analytics API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Analyze feedback for a specific shop
 */
function analyzeShopFeedback($conn) {
    $shopId = isset($_GET['shop_id']) ? trim($_GET['shop_id']) : '';
    
    if (!$shopId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'shop_id required']);
        exit;
    }
    
    // Get shop feedback
    $query = "SELECT id, rating, message, user_name, created_at 
              FROM feedback 
              WHERE target_type = 'shop' AND target_id = ? AND message IS NOT NULL AND message != ''
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $shopId);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $feedback = [];
    
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    
    if (empty($feedback)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'shop_id' => $shopId,
            'total_feedback' => 0,
            'message' => 'No feedback to analyze'
        ]);
        exit;
    }
    
    // Call Python NLP module
    $analysis = callNLPAnalyzer('analyze_batch', $feedback);
    
    // Get shop info for response
    $shopQuery = "SELECT name FROM shops WHERE id = ?";
    $shopStmt = $conn->prepare($shopQuery);
    $shopStmt->bind_param('s', $shopId);
    $shopStmt->execute();
    $shopResult = $shopStmt->get_result();
    $shop = $shopResult->fetch_assoc();
    
    $analysis['shop_id'] = $shopId;
    $analysis['shop_name'] = $shop ? $shop['name'] : 'Unknown';
    
    http_response_code(200);
    echo json_encode($analysis);
}

/**
 * Analyze all feedback across all shops and products
 */
function analyzeAllFeedback($conn) {
    $filterType = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : null; // 'shop' or 'product'
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    
    $query = "SELECT id, rating, message, target_type, target_id, target_name, user_name, created_at 
              FROM feedback 
              WHERE message IS NOT NULL AND message != ''";
    
    $params = [];
    $types = '';
    if ($filterType) {
        $query .= " AND target_type = ?";
        $params[] = $filterType;
        $types .= 's';
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    
    if (empty($feedback)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'total_feedback' => 0,
            'message' => 'No feedback to analyze'
        ]);
        exit;
    }
    
    // Call Python NLP module
    $analysis = callNLPAnalyzer('analyze_batch', $feedback);
    $analysis['filter_type'] = $filterType;
    
    http_response_code(200);
    echo json_encode($analysis);
}

/**
 * Analyze sentiment of a single message
 */
function analyzeSentiment() {
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    
    if (!$text) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'text required']);
        exit;
    }
    
    $result = callNLPAnalyzer('analyze_sentiment', ['text' => $text]);
    http_response_code(200);
    echo json_encode($result);
}

/**
 * Extract keywords from a message
 */
function extractKeywords() {
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    $topN = isset($_POST['top_n']) ? intval($_POST['top_n']) : 5;
    
    if (!$text) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'text required']);
        exit;
    }
    
    $result = callNLPAnalyzer('extract_keywords', [
        'text' => $text,
        'top_n' => $topN
    ]);
    http_response_code(200);
    echo json_encode($result);
}

/**
 * Get comprehensive analytics summary
 */
function getAnalyticsSummary($conn) {
    $query = "SELECT 
                COUNT(*) as total_reviews,
                ROUND(AVG(rating), 2) as average_rating,
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_count,
                target_type,
                COUNT(DISTINCT target_id) as unique_targets
              FROM feedback
              WHERE message IS NOT NULL AND message != ''
              GROUP BY target_type";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $summary = [
        'success' => true,
        'overview' => [],
        'top_rated_shops' => getTopRatedShops($conn),
        'lowest_rated_shops' => getLowestRatedShops($conn),
        'recent_analysis' => getRecentAnalysis($conn)
    ];
    
    while ($row = $result->fetch_assoc()) {
        $summary['overview'][] = $row;
    }
    
    http_response_code(200);
    echo json_encode($summary);
}

/**
 * Get top-rated shops
 */
function getTopRatedShops($conn, $limit = 5) {
    $query = "SELECT 
                target_id,
                target_name,
                COUNT(*) as review_count,
                ROUND(AVG(rating), 2) as avg_rating,
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count
              FROM feedback
              WHERE target_type = 'shop' AND message IS NOT NULL AND message != ''
              GROUP BY target_id
              ORDER BY avg_rating DESC, review_count DESC
              LIMIT " . intval($limit);
    
    $result = $conn->query($query);
    $shops = [];
    
    while ($row = $result->fetch_assoc()) {
        // Get NLP analysis for shop
        $shopFeedbackQuery = "SELECT id, rating, message FROM feedback WHERE target_id = ? AND target_type = 'shop'";
        $stmt = $conn->prepare($shopFeedbackQuery);
        $stmt->bind_param('s', $row['target_id']);
        $stmt->execute();
        $fbResult = $stmt->get_result();
        
        $feedbackData = [];
        while ($fb = $fbResult->fetch_assoc()) {
            $feedbackData[] = $fb;
        }
        
        if (!empty($feedbackData)) {
            $nlpAnalysis = callNLPAnalyzer('analyze_batch', $feedbackData);
            $row['sentiment_analysis'] = $nlpAnalysis;
        }
        
        $shops[] = $row;
    }
    
    return $shops;
}

/**
 * Get lowest-rated shops
 */
function getLowestRatedShops($conn, $limit = 5) {
    $query = "SELECT 
                target_id,
                target_name,
                COUNT(*) as review_count,
                ROUND(AVG(rating), 2) as avg_rating,
                SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_count
              FROM feedback
              WHERE target_type = 'shop' AND message IS NOT NULL AND message != ''
              GROUP BY target_id
              HAVING COUNT(*) >= 2
              ORDER BY avg_rating ASC, review_count DESC
              LIMIT " . intval($limit);
    
    $result = $conn->query($query);
    $shops = [];
    
    while ($row = $result->fetch_assoc()) {
        // Get NLP analysis for shop
        $shopFeedbackQuery = "SELECT id, rating, message FROM feedback WHERE target_id = ? AND target_type = 'shop'";
        $stmt = $conn->prepare($shopFeedbackQuery);
        $stmt->bind_param('s', $row['target_id']);
        $stmt->execute();
        $fbResult = $stmt->get_result();
        
        $feedbackData = [];
        while ($fb = $fbResult->fetch_assoc()) {
            $feedbackData[] = $fb;
        }
        
        if (!empty($feedbackData)) {
            $nlpAnalysis = callNLPAnalyzer('analyze_batch', $feedbackData);
            $row['sentiment_analysis'] = $nlpAnalysis;
        }
        
        $shops[] = $row;
    }
    
    return $shops;
}

/**
 * Get recent detailed analysis
 */
function getRecentAnalysis($conn, $limit = 20) {
    $query = "SELECT id, rating, message, target_type, target_name, user_name, created_at
              FROM feedback
              WHERE message IS NOT NULL AND message != ''
              ORDER BY created_at DESC
              LIMIT " . intval($limit);
    
    $result = $conn->query($query);
    $recent = [];
    
    while ($row = $result->fetch_assoc()) {
        $sentiment = callNLPAnalyzer('analyze_sentiment', ['text' => $row['message']]);
        $keywords = callNLPAnalyzer('extract_keywords', ['text' => $row['message'], 'top_n' => 3]);
        
        $row['sentiment_analysis'] = $sentiment;
        $row['keywords'] = isset($keywords['keywords']) ? $keywords['keywords'] : $keywords;
        $recent[] = $row;
    }
    
    return $recent;
}

/**
 * Call the Python NLP analyzer
 * 
 * @param string $action The NLP action to perform
 * @param mixed $data Data to send to the NLP module
 * @return array Result from NLP analyzer
 */
function callNLPAnalyzer($action, $data) {
    $pythonScript = __DIR__ . '/nlp_analyzer.py';
    
    // Check if Python script exists
    if (!file_exists($pythonScript)) {
        return [
            'success' => false,
            'error' => 'NLP module not found. Please ensure nlp_analyzer.py is installed.'
        ];
    }
    
    // Build input data
    $inputData = [
        'action' => $action,
        'feedback' => $data
    ];
    
    if ($action === 'analyze_batch') {
        $inputData['feedback'] = $data;
    } else {
        $inputData = array_merge($inputData, is_array($data) ? $data : ['text' => $data]);
    }
    
    // Call Python script
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout
        2 => array("pipe", "w")   // stderr
    );
    
    $process = proc_open('python "' . $pythonScript . '"', $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        // Send data to Python
        fwrite($pipes[0], json_encode($inputData));
        fclose($pipes[0]);
        
        // Read output
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnCode = proc_close($process);
        
        if ($returnCode !== 0) {
            error_log('Python error: ' . $error);
            return [
                'success' => false,
                'error' => 'NLP processing failed. Check server logs.'
            ];
        }
        
        // Parse JSON output
        $result = json_decode($output, true);
        return $result ? $result : ['success' => false, 'error' => 'Invalid response from NLP module'];
    } else {
        return [
            'success' => false,
            'error' => 'Failed to execute NLP module'
        ];
    }
}

?>
