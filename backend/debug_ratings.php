<?php
/**
 * Debug Ratings System
 * Shows full status of databases, APIs, and sample data
 */

header('Content-Type: text/html; charset=utf-8');
require_once('db.php');

$conn = get_db();

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Ratings System Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        h3 { color: #666; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9f9f9; font-weight: bold; }
        tr:hover { background: #f5f5f5; }
        .good { color: green; font-weight: bold; }
        .bad { color: red; font-weight: bold; }
        .info { color: #0066cc; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .api-test { margin: 15px 0; padding: 15px; background: #f0f7ff; border-left: 3px solid #0066cc; border-radius: 4px; }
        button { padding: 8px 16px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0052a3; }
        .result { margin-top: 10px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Ratings System Debug Dashboard</h1>
    <p>Last updated: <strong>{$_SERVER['REQUEST_TIME_FLOAT']}</strong></p>
</div>

HTML;

// === DATABASE CHECK ===
echo '<div class="container">';
echo '<h2>Database Status</h2>';

// Check shops table
$shopsResult = $conn->query("SELECT COUNT(*) as count FROM shops");
$shopsRow = $shopsResult->fetch_assoc();
$shopsCount = $shopsRow['count'];

// Check feedback table
$feedbackResult = $conn->query("SELECT COUNT(*) as count FROM feedback");
$feedbackRow = $feedbackResult->fetch_assoc();
$feedbackCount = $feedbackRow['count'];

echo "✅ Shops: <span class='good'>$shopsCount shops</span><br>";
echo "✅ Feedback: <span class='good'>$feedbackCount feedback records</span><br>";

echo '<h3>📊 Feedback Statistics by Type</h3>';
$statsResult = $conn->query("SELECT target_type, COUNT(*) as count, ROUND(AVG(rating), 2) as avg_rating FROM feedback GROUP BY target_type");
echo '<table>';
echo '<tr><th>Type</th><th>Count</th><th>Avg Rating</th></tr>';
while($row = $statsResult->fetch_assoc()) {
    echo '<tr>';
    echo '<td><code>' . htmlspecialchars($row['target_type']) . '</code></td>';
    echo '<td>' . $row['count'] . '</td>';
    echo '<td>' . $row['avg_rating'] . '/5</td>';
    echo '</tr>';
}
echo '</table>';

// === SHOPS WITH FEEDBACK ===
echo '<h3>🏪 Shops and Their Feedback</h3>';
$shopsWithFeedback = $conn->query("
    SELECT 
        s.id, 
        s.shop_name, 
        COUNT(f.id) as feedback_count,
        ROUND(AVG(f.rating), 2) as avg_rating
    FROM shops s
    LEFT JOIN feedback f ON f.target_type = 'shop' AND f.target_id = s.id
    GROUP BY s.id, s.shop_name
    ORDER BY feedback_count DESC
    LIMIT 10
");

echo '<table>';
echo '<tr><th>Shop ID</th><th>Shop Name</th><th>Feedback Count</th><th>Avg Rating</th></tr>';
while($row = $shopsWithFeedback->fetch_assoc()) {
    $status = $row['feedback_count'] > 0 ? '<span class="good">✓</span>' : '<span class="bad">✗</span>';
    echo '<tr>';
    echo '<td><code>' . $row['id'] . '</code></td>';
    echo '<td>' . htmlspecialchars($row['shop_name']) . '</td>';
    echo '<td>' . $row['feedback_count'] . ' ' . $status . '</td>';
    echo '<td>' . ($row['avg_rating'] ? $row['avg_rating'] . '/5' : '-') . '</td>';
    echo '</tr>';
}
echo '</table>';

echo '</div>';

// === API TESTS ===
echo '<div class="container">';
echo '<h2>🌐 API Endpoint Tests</h2>';

// Get a shop with feedback for testing
$testShopResult = $conn->query("
    SELECT s.id, s.shop_name
    FROM shops s
    WHERE EXISTS (
        SELECT 1 FROM feedback f 
        WHERE f.target_type = 'shop' AND f.target_id = s.id
    )
    LIMIT 1
");

if($testShop = $testShopResult->fetch_assoc()) {
    $testShopId = $testShop['id'];
    $testShopName = $testShop['shop_name'];
    
    echo "<h3>📍 Testing Shop: <code>$testShopId</code> - $testShopName</h3>";
    
    // Test get_target_ratings
    echo '<div class="api-test">';
    echo '<strong>Endpoint:</strong> <code>ratings_api.php?action=get_target_ratings&target_type=shop&target_id=' . $testShopId . '</code><br>';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://localhost/capstone/backend/ratings_api.php?action=get_target_ratings&target_type=shop&target_id=' . $testShopId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $apiError = curl_error($ch);
    curl_close($ch);
    
    if($apiError) {
        echo '<div class="result"><span class="bad">❌ cURL Error: ' . $apiError . '</span></div>';
    } else {
        echo '<strong>HTTP Status:</strong> <span class="' . ($httpCode == 200 ? 'good' : 'bad') . '">' . $httpCode . '</span><br>';
        $decodedResponse = json_decode($apiResponse, true);
        echo '<strong>Response:</strong><br>';
        echo '<div class="result">';
        echo '<pre>' . json_encode($decodedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        echo '</div>';
        
        if($decodedResponse && isset($decodedResponse['reviews'])) {
            echo '<span class="good">✓ Returns ' . count($decodedResponse['reviews']) . ' reviews</span><br>';
        }
        if($decodedResponse && isset($decodedResponse['statistics'])) {
            echo '<span class="good">✓ Contains statistics</span><br>';
        }
    }
    echo '</div>';
} else {
    echo '<p><span class="bad">⚠️ No shops with feedback found. Create feedback first to test.</span></p>';
}

echo '</div>';

// === RECENT FEEDBACK ===
echo '<div class="container">';
echo '<h2>📝 Recent Feedback Records</h2>';

$recentFeedback = $conn->query("
    SELECT 
        id, 
        target_type, 
        target_id, 
        target_name, 
        user_name, 
        rating, 
        message,
        created_at
    FROM feedback
    ORDER BY created_at DESC
    LIMIT 20
");

echo '<table>';
echo '<tr><th>ID</th><th>Type</th><th>Target</th><th>User</th><th>Rating</th><th>Message</th><th>Date</th></tr>';
while($row = $recentFeedback->fetch_assoc()) {
    $stars = str_repeat('⭐', $row['rating']);
    echo '<tr>';
    echo '<td><code>' . $row['id'] . '</code></td>';
    echo '<td><code>' . $row['target_type'] . '</code></td>';
    echo '<td>' . htmlspecialchars($row['target_id']) . ' - ' . htmlspecialchars($row['target_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['user_name']) . '</td>';
    echo '<td>' . $stars . ' (' . $row['rating'] . '/5)</td>';
    echo '<td>' . htmlspecialchars(substr($row['message'], 0, 50)) . '...</td>';
    echo '<td>' . $row['created_at'] . '</td>';
    echo '</tr>';
}
echo '</table>';

echo '</div>';

// === FRONTEND TEST ===
echo '<div class="container">';
echo '<h2>🧪 Frontend Test</h2>';
echo '<p><a href="../frontend/index.html" target="_blank"><button>Open Frontend</button></a> and open browser DevTools (F12) to see detailed console logs.</p>';
echo '</div>';

?>
</body>
</html>
