<?php
header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch($action) {
        
        // Submit a new rating/feedback
        case 'submit_rating':
            handleSubmitRating($conn);
            break;
        
        // Get all ratings with optional filtering
        case 'get_ratings':
            handleGetRatings($conn);
            break;
        
        // Get ratings for specific target (shop, product, destination, hotel)
        case 'get_target_ratings':
            handleGetTargetRatings($conn);
            break;
        
        // Get rating statistics
        case 'get_rating_stats':
            handleGetRatingStats($conn);
            break;
        
        // Delete a rating (admin only)
        case 'delete_rating':
            handleDeleteRating($conn);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log('Ratings API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

function handleSubmitRating($conn) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    $required = ['user_name', 'user_email', 'target_type', 'target_id', 'target_name', 'rating', 'message'];
    foreach($required as $field) {
        if(!isset($data[$field]) || trim($data[$field]) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit;
        }
    }
    
    $user_name = trim($data['user_name']);
    $user_email = trim($data['user_email']);
    $target_type = trim($data['target_type']); // shop, product, destination, hotel
    $target_id = trim($data['target_id']);
    $target_name = trim($data['target_name']);
    $rating = intval($data['rating']);
    $message = trim($data['message']);
    $anonymous = isset($data['anonymous']) ? intval($data['anonymous']) : 0;
    $feedback_type = isset($data['feedback_type']) ? trim($data['feedback_type']) : 'review';
    $image_url = isset($data['image_url']) ? trim($data['image_url']) : NULL;
    $metadata = isset($data['metadata']) ? $data['metadata'] : NULL;
    
    // Validate inputs
    if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }
    
    if($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Rating must be between 1 and 5']);
        exit;
    }
    
    $valid_types = ['shop', 'product', 'destination', 'hotel', 'system'];
    if(!in_array($target_type, $valid_types)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid target type']);
        exit;
    }
    
    // Insert feedback/rating
    $query = "INSERT INTO feedback 
              (user_email, user_name, feedback_type, target_type, target_id, target_name, rating, message, anonymous, image, metadata, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    if(!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('ssssssissss', $user_email, $user_name, $feedback_type, $target_type, $target_id, $target_name, $rating, $message, $anonymous, $image_url, $metadata);
    
    if(!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $rating_id = $stmt->insert_id;
    $stmt->close();
    
    // Update average rating for the target
    updateTargetAverageRating($conn, $target_type, $target_id);
    
    // Save backup JSON
    $feedbackDir = __DIR__ . '/feedback/';
    if(!is_dir($feedbackDir)) {
        mkdir($feedbackDir, 0755, true);
    }
    
    $backupJson = $feedbackDir . 'rating_' . $rating_id . '.json';
    $backupData = [
        'id' => $rating_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'user_name' => $user_name,
        'user_email' => $user_email,
        'target_type' => $target_type,
        'target_id' => $target_id,
        'target_name' => $target_name,
        'rating' => $rating,
        'message' => $message,
        'anonymous' => $anonymous,
        'feedback_type' => $feedback_type,
        'metadata' => $metadata
    ];
    file_put_contents($backupJson, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    error_log("⭐ New rating submitted: [$rating/5] for $target_type ($target_id: $target_name) by $user_email");
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your rating!',
        'rating_id' => $rating_id
    ]);
}

function handleGetRatings($conn) {
    try {
        $target_type = isset($_GET['target_type']) ? $_GET['target_type'] : '';
        $target_id = isset($_GET['target_id']) ? $_GET['target_id'] : '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        // Check if metadata column exists first
        $tableCheckQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='feedback' AND COLUMN_NAME='metadata' AND TABLE_SCHEMA=DATABASE()";
        $tableCheckResult = $conn->query($tableCheckQuery);
        $hasMetadata = $tableCheckResult && $tableCheckResult->num_rows > 0;
        
        // Build query safely - handle missing metadata column gracefully
        if($hasMetadata) {
            $query = "SELECT id, user_email, user_name, feedback_type, target_type, target_id, target_name, rating, message, anonymous, image as image_url, metadata, created_at 
                      FROM feedback WHERE target_type IS NOT NULL";
        } else {
            $query = "SELECT id, user_email, user_name, feedback_type, target_type, target_id, target_name, rating, message, anonymous, image as image_url, NULL as metadata, created_at 
                      FROM feedback WHERE target_type IS NOT NULL";
        }
        
        if($target_type) {
            $query .= " AND target_type = '" . $conn->real_escape_string($target_type) . "'";
        }
        
        if($target_id) {
            $query .= " AND target_id = '" . $conn->real_escape_string($target_id) . "'";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT " . $limit . " OFFSET " . $offset;
        
        $result = $conn->query($query);
        
        if(!$result) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        
        $ratings = [];
        while($row = $result->fetch_assoc()) {
            if($row['metadata']) {
                $row['metadata'] = json_decode($row['metadata'], true);
            }
            $ratings[] = $row;
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM feedback WHERE target_type IS NOT NULL";
        if($target_type) {
            $countQuery .= " AND target_type = '" . $conn->real_escape_string($target_type) . "'";
        }
        if($target_id) {
            $countQuery .= " AND target_id = '" . $conn->real_escape_string($target_id) . "'";
        }
        
        $countResult = $conn->query($countQuery);
        if(!$countResult) {
            throw new Exception('Count query failed: ' . $conn->error);
        }
        
        $countRow = $countResult->fetch_assoc();
        $total = $countRow['total'];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $ratings,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleGetTargetRatings($conn) {
    $target_type = isset($_GET['target_type']) ? $_GET['target_type'] : '';
    $target_id = isset($_GET['target_id']) ? $_GET['target_id'] : '';
    
    if(!$target_type || !$target_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'target_type and target_id required']);
        exit;
    }
    
    $query = "SELECT id, user_email, user_name, feedback_type, target_type, target_id, target_name, rating, message, anonymous, image as image_url, metadata, created_at 
              FROM feedback 
              WHERE target_type = ? AND target_id = ?
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    if(!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('ss', $target_type, $target_id);
    
    if(!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $ratings = [];
    while($row = $result->fetch_assoc()) {
        $row['metadata'] = json_decode($row['metadata'], true);
        $ratings[] = $row;
    }
    
    // Calculate statistics
    $statsQuery = "SELECT 
                    COUNT(*) as total_reviews,
                    ROUND(AVG(rating), 2) as average_rating,
                    MIN(rating) as min_rating,
                    MAX(rating) as max_rating,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                    SUM(CASE WHEN rating < 4 AND rating >= 3 THEN 1 ELSE 0 END) as neutral_reviews,
                    SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_reviews
                   FROM feedback 
                   WHERE target_type = ? AND target_id = ?";
    
    $statsStmt = $conn->prepare($statsQuery);
    if(!$statsStmt) {
        throw new Exception('Stats prepare failed: ' . $conn->error);
    }
    
    $statsStmt->bind_param('ss', $target_type, $target_id);
    if(!$statsStmt->execute()) {
        throw new Exception('Stats execute failed: ' . $statsStmt->error);
    }
    
    $statsResult = $statsStmt->get_result();
    $stats = $statsResult->fetch_assoc();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'target_type' => $target_type,
        'target_id' => $target_id,
        'statistics' => $stats,
        'reviews' => $ratings
    ]);
}

function handleGetRatingStats($conn) {
    $target_type = isset($_GET['target_type']) ? $_GET['target_type'] : '';
    $target_id = isset($_GET['target_id']) ? $_GET['target_id'] : '';
    
    // If both target_type and target_id are provided, get stats for specific target
    if($target_type && $target_id) {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    ROUND(AVG(rating), 2) as average_rating,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                    SUM(CASE WHEN rating < 4 AND rating >= 3 THEN 1 ELSE 0 END) as neutral_reviews,
                    SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_reviews
                  FROM feedback 
                  WHERE target_type = ? AND target_id = ?";
        
        $stmt = $conn->prepare($query);
        if(!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('ss', $target_type, $target_id);
        if(!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'target_type' => $target_type,
            'target_id' => $target_id,
            'total_reviews' => $stats['total_reviews'],
            'average_rating' => $stats['average_rating'],
            'positive_reviews' => $stats['positive_reviews'],
            'neutral_reviews' => $stats['neutral_reviews'],
            'negative_reviews' => $stats['negative_reviews']
        ]);
        return;
    }
    
    // Otherwise get overall stats by target_type
    $query = "SELECT 
                target_type,
                COUNT(*) as total_reviews,
                ROUND(AVG(rating), 2) as average_rating,
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                SUM(CASE WHEN rating < 4 AND rating >= 3 THEN 1 ELSE 0 END) as neutral_reviews,
                SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_reviews
              FROM feedback 
              WHERE target_type IS NOT NULL
              GROUP BY target_type
              ORDER BY total_reviews DESC";
    
    $result = $conn->query($query);
    if(!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $stats = [];
    while($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'statistics' => $stats
    ]);
}

function handleDeleteRating($conn) {
    // This would be admin-only functionality
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if(!isset($data['rating_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'rating_id required']);
        exit;
    }
    
    $rating_id = intval($data['rating_id']);
    
    // Get the rating details before deleting
    $selectQuery = "SELECT target_type, target_id FROM feedback WHERE id = ?";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->bind_param('i', $rating_id);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $rating = $result->fetch_assoc();
    
    if(!$rating) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Rating not found']);
        exit;
    }
    
    // Delete the rating
    $deleteQuery = "DELETE FROM feedback WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param('i', $rating_id);
    
    if(!$deleteStmt->execute()) {
        throw new Exception('Delete failed: ' . $deleteStmt->error);
    }
    
    // Update average rating
    updateTargetAverageRating($conn, $rating['target_type'], $rating['target_id']);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Rating deleted successfully'
    ]);
}

function updateTargetAverageRating($conn, $target_type, $target_id) {
    // Calculate new average
    $query = "SELECT ROUND(AVG(rating), 2) as avg_rating FROM feedback WHERE target_type = ? AND target_id = ?";
    $stmt = $conn->prepare($query);
    
    if(!$stmt) return;
    
    $stmt->bind_param('ss', $target_type, $target_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $avg_rating = $row['avg_rating'] ?: 0;
    
    // Update the appropriate table
    switch($target_type) {
        case 'shop':
            $updateQuery = "UPDATE shops SET rating = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ds', $avg_rating, $target_id);
            $updateStmt->execute();
            break;
        case 'product':
            $updateQuery = "UPDATE products SET rating = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ds', $avg_rating, $target_id);
            $updateStmt->execute();
            break;
        case 'destination':
            $updateQuery = "UPDATE destinations SET average_rating = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ds', $avg_rating, $target_id);
            $updateStmt->execute();
            break;
        case 'hotel':
            $updateQuery = "UPDATE hotels SET average_rating = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ds', $avg_rating, $target_id);
            $updateStmt->execute();
            break;
    }
}
?>
