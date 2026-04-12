<?php
header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch($action) {
        case 'get_shop_analytics':
            getShopAnalytics($conn);
            break;
        
        case 'get_detailed_shop_feedback':
            getDetailedShopFeedback($conn);
            break;
        
        case 'get_all_shops_summary':
            getAllShopsSummary($conn);
            break;
        
        case 'get_product_analytics':
            getProductAnalytics($conn);
            break;
        
        case 'get_feedback_by_rating':
            getFeedbackByRating($conn);
            break;
        
        case 'export_analytics':
            exportAnalytics($conn);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log('Shop Analytics API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Get comprehensive analytics for a specific shop
 */
function getShopAnalytics($conn) {
    $shopId = isset($_GET['shop_id']) ? trim($_GET['shop_id']) : '';
    
    if (!$shopId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'shop_id required']);
        exit;
    }
    
    // Get shop basic info
    $shopQuery = "SELECT * FROM shops WHERE id = ?";
    $stmt = $conn->prepare($shopQuery);
    $stmt->bind_param('s', $shopId);
    $stmt->execute();
    $shopResult = $stmt->get_result();
    $shop = $shopResult->fetch_assoc();
    
    if (!$shop) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Shop not found']);
        exit;
    }
    
    // Get shop feedback statistics
    $feedbackQuery = "SELECT 
                        COUNT(*) as total_reviews,
                        ROUND(AVG(rating), 2) as average_rating,
                        MIN(rating) as min_rating,
                        MAX(rating) as max_rating,
                        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star,
                        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                        SUM(CASE WHEN rating >= 3 AND rating < 4 THEN 1 ELSE 0 END) as neutral_reviews,
                        SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_reviews,
                        DATE(MAX(created_at)) as last_review_date
                     FROM feedback 
                     WHERE target_type = 'shop' AND target_id = ?";
    
    $feedbackStmt = $conn->prepare($feedbackQuery);
    $feedbackStmt->bind_param('s', $shopId);
    $feedbackStmt->execute();
    $feedbackResult = $feedbackStmt->get_result();
    $feedbackStats = $feedbackResult->fetch_assoc();
    
    // Get recent reviews with details
    $recentQuery = "SELECT id, user_name, user_email, rating, message, metadata, created_at, anonymous
                    FROM feedback 
                    WHERE target_type = 'shop' AND target_id = ?
                    ORDER BY created_at DESC
                    LIMIT 20";
    
    $recentStmt = $conn->prepare($recentQuery);
    $recentStmt->bind_param('s', $shopId);
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    $recentReviews = [];
    while ($row = $recentResult->fetch_assoc()) {
        $row['metadata'] = json_decode($row['metadata'], true);
        $recentReviews[] = $row;
    }
    
    // Get rating distribution for chart
    $ratingDistribution = [];
    for ($i = 1; $i <= 5; $i++) {
        $distQuery = "SELECT COUNT(*) as count FROM feedback 
                      WHERE target_type = 'shop' AND target_id = ? AND rating = ?";
        $distStmt = $conn->prepare($distQuery);
        $distStmt->bind_param('si', $shopId, $i);
        $distStmt->execute();
        $distResult = $distStmt->get_result();
        $distRow = $distResult->fetch_assoc();
        $ratingDistribution[$i] = $distRow['count'];
    }
    
    // Get reviews by month for trend
    $trendQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, ROUND(AVG(rating), 2) as avg_rating
                   FROM feedback 
                   WHERE target_type = 'shop' AND target_id = ?
                   GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                   ORDER BY month DESC
                   LIMIT 12";
    
    $trendStmt = $conn->prepare($trendQuery);
    $trendStmt->bind_param('s', $shopId);
    $trendStmt->execute();
    $trendResult = $trendStmt->get_result();
    $trendData = [];
    while ($row = $trendResult->fetch_assoc()) {
        $trendData[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'shop' => $shop,
        'statistics' => $feedbackStats,
        'recentReviews' => $recentReviews,
        'ratingDistribution' => $ratingDistribution,
        'trendData' => array_reverse($trendData)
    ]);
}

/**
 * Get detailed feedback for a specific shop
 */
function getDetailedShopFeedback($conn) {
    $shopId = isset($_GET['shop_id']) ? trim($_GET['shop_id']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $filterRating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
    
    if (!$shopId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'shop_id required']);
        exit;
    }
    
    // Build dynamic query with filters
    $query = "SELECT id, user_name, user_email, rating, message, metadata, created_at, anonymous
              FROM feedback 
              WHERE target_type = 'shop' AND target_id = ?";
    
    $params = [$shopId];
    $types = 's';
    
    if ($filterRating > 0 && $filterRating <= 5) {
        $query .= " AND rating = ?";
        $params[] = $filterRating;
        $types .= 'i';
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $row['metadata'] = json_decode($row['metadata'], true);
        $reviews[] = $row;
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM feedback 
                   WHERE target_type = 'shop' AND target_id = ?";
    if ($filterRating > 0) {
        $countQuery .= " AND rating = ?";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param('si', $shopId, $filterRating);
    } else {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param('s', $shopId);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'total' => $countRow['total'],
        'limit' => $limit,
        'offset' => $offset
    ]);
}

/**
 * Get summary analytics for all shops
 */
function getAllShopsSummary($conn) {
    $query = "SELECT 
                s.id as shop_id,
                s.name as shop_name,
                s.address as shop_address,
                COUNT(f.id) as total_reviews,
                ROUND(AVG(f.rating), 2) as average_rating,
                MAX(f.created_at) as last_review_date,
                SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN f.rating < 3 THEN 1 ELSE 0 END) as negative_count
              FROM shops s
              LEFT JOIN feedback f ON s.id = f.target_id AND f.target_type = 'shop'
              GROUP BY s.id, s.name, s.address
              ORDER BY total_reviews DESC";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $shops = [];
    while ($row = $result->fetch_assoc()) {
        $shops[] = $row;
    }
    
    // Overall statistics
    $totalQuery = "SELECT 
                    COUNT(DISTINCT target_id) as total_shops,
                    COUNT(*) as total_reviews,
                    ROUND(AVG(rating), 2) as overall_avg_rating
                   FROM feedback 
                   WHERE target_type = 'shop'";
    
    $totalResult = $conn->query($totalQuery);
    $totalStats = $totalResult->fetch_assoc();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'shops' => $shops,
        'overallStats' => $totalStats
    ]);
}

/**
 * Get product-level analytics within a shop
 */
function getProductAnalytics($conn) {
    $shopId = isset($_GET['shop_id']) ? trim($_GET['shop_id']) : '';
    
    if (!$shopId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'shop_id required']);
        exit;
    }
    
    // Get products and their ratings
    $query = "SELECT 
                target_name as product_name,
                COUNT(*) as total_reviews,
                ROUND(AVG(rating), 2) as average_rating,
                MIN(rating) as min_rating,
                MAX(rating) as max_rating,
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as negative_reviews,
                MAX(created_at) as last_review_date
              FROM feedback 
              WHERE target_type = 'product' AND target_id LIKE ?
              GROUP BY target_name
              ORDER BY total_reviews DESC";
    
    $pattern = $shopId . '%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
}

/**
 * Get feedback filtered by rating level
 */
function getFeedbackByRating($conn) {
    $rating = isset($_GET['rating']) ? intval($_GET['rating']) : 5;
    $targetType = isset($_GET['target_type']) ? trim($_GET['target_type']) : 'shop';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 30;
    
    $query = "SELECT id, target_id, target_name, user_name, user_email, rating, message, metadata, created_at, anonymous
              FROM feedback 
              WHERE target_type = ? AND rating = ?
              ORDER BY created_at DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $targetType, $rating, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $row['metadata'] = json_decode($row['metadata'], true);
        $reviews[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'rating' => $rating,
        'targetType' => $targetType
    ]);
}

/**
 * Export analytics as JSON for reporting
 */
function exportAnalytics($conn) {
    $shopId = isset($_GET['shop_id']) ? trim($_GET['shop_id']) : null;
    
    $analytics = [];
    
    if ($shopId) {
        // Export single shop
        $shopQuery = "SELECT * FROM novelty_shops WHERE id = ?";
        $stmt = $conn->prepare($shopQuery);
        $stmt->bind_param('s', $shopId);
        $stmt->execute();
        $shopResult = $stmt->get_result();
        $analytics['shop'] = $shopResult->fetch_assoc();
        
        // Get all feedback for this shop
        $feedbackQuery = "SELECT * FROM feedback 
                         WHERE target_type = 'shop' AND target_id = ?
                         ORDER BY created_at DESC";
        $feedbackStmt = $conn->prepare($feedbackQuery);
        $feedbackStmt->bind_param('s', $shopId);
        $feedbackStmt->execute();
        $feedbackResult = $feedbackStmt->get_result();
        
        $feedback = [];
        while ($row = $feedbackResult->fetch_assoc()) {
            $row['metadata'] = json_decode($row['metadata'], true);
            $feedback[] = $row;
        }
        $analytics['feedback'] = $feedback;
    } else {
        // Export all shops
        $shopsQuery = "SELECT * FROM novelty_shops ORDER BY name";
        $shopsResult = $conn->query($shopsQuery);
        $shops = [];
        while ($row = $shopsResult->fetch_assoc()) {
            $shops[] = $row;
        }
        $analytics['shops'] = $shops;
        
        // Get all shop feedback
        $allFeedbackQuery = "SELECT * FROM feedback 
                            WHERE target_type = 'shop'
                            ORDER BY created_at DESC";
        $allFeedbackResult = $conn->query($allFeedbackQuery);
        $allFeedback = [];
        while ($row = $allFeedbackResult->fetch_assoc()) {
            $row['metadata'] = json_decode($row['metadata'], true);
            $allFeedback[] = $row;
        }
        $analytics['feedback'] = $allFeedback;
    }
    
    $filename = 'shop_analytics_' . date('Y-m-d_H-i-s') . '.json';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: application/json');
    
    echo json_encode($analytics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
?>
