<?php
// Quick diagnostics script for feedback system
header('Content-Type: application/json');

echo "=== CAPSTONE FEEDBACK SYSTEM DIAGNOSTICS ===\n\n";

// 1. Check database connection
echo "1. DATABASE CONNECTION\n";
try {
    $mysqli = new mysqli('127.0.0.1', 'root', '', 'capstone_db', 3306);
    if ($mysqli->connect_errno) {
        echo "❌ Connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ Connected to capstone_db\n";
        $mysqli->set_charset('utf8mb4');
        
        // 2. Check feedback table exists
        echo "\n2. FEEDBACK TABLE\n";
        $result = $mysqli->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='capstone_db' AND TABLE_NAME='feedback'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Feedback table exists\n";
            
            // 3. Check table structure
            echo "\n3. TABLE COLUMNS\n";
            $colResult = $mysqli->query("SHOW COLUMNS FROM feedback");
            $columns = [];
            while($col = $colResult->fetch_assoc()) {
                $columns[] = $col['Field'];
                echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }
            
            // 4. Check for required columns
            echo "\n4. REQUIRED COLUMNS CHECK\n";
            $required = ['id', 'user_email', 'user_name', 'target_type', 'target_id', 'target_name', 'rating', 'message', 'created_at'];
            foreach($required as $col) {
                if(in_array($col, $columns)) {
                    echo "  ✅ $col\n";
                } else {
                    echo "  ❌ MISSING: $col\n";
                }
            }
            
            // 5. Check metadata column
            echo "\n5. OPTIONAL COLUMNS\n";
            if(in_array('metadata', $columns)) {
                echo "  ✅ metadata (JSON)\n";
            } else {
                echo "  ⚠️ metadata - NOT FOUND (will be handled gracefully)\n";
            }
            
            // 6. Count existing feedback
            echo "\n6. DATA COUNT\n";
            $countResult = $mysqli->query("SELECT COUNT(*) as total FROM feedback");
            $countRow = $countResult->fetch_assoc();
            echo "  Total feedback records: " . $countRow['total'] . "\n";
            
            // 7. Sample recent feedback
            if($countRow['total'] > 0) {
                echo "\n7. SAMPLE RECENT FEEDBACK\n";
                $sampleResult = $mysqli->query("SELECT id, user_name, target_type, rating, created_at FROM feedback ORDER BY created_at DESC LIMIT 3");
                while($row = $sampleResult->fetch_assoc()) {
                    echo "  ID: " . $row['id'] . " | Name: " . $row['user_name'] . " | Type: " . $row['target_type'] . " | Rating: " . $row['rating'] . "⭐ | Date: " . substr($row['created_at'], 0, 10) . "\n";
                }
            }
            
        } else {
            echo "❌ Feedback table NOT FOUND\n";
            echo "   Please run: /backend/schema.sql\n";
        }
        
        // 8. Test API call
        echo "\n8. API ENDPOINT TEST\n";
        echo "  Testing: /capstone/backend/ratings_api.php?action=get_ratings\n";
        
        $mysqli->close();
    }
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== END DIAGNOSTICS ===\n";
echo "\nTo run this script:\n";
echo "1. Save as /backend/diagnostics.php\n";
echo "2. Visit: http://localhost/capstone/backend/diagnostics.php\n";
echo "3. Check results above\n";
?>
