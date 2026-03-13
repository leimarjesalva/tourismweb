<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

// ML model storage file
function ml_model_filepath(){
    return __DIR__ . '/data/ml_models.json';
}

function load_ml_models(){
    $path = ml_model_filepath();
    if (!is_readable($path)) return null;
    $raw = @file_get_contents($path);
    $j = json_decode($raw, true);
    return $j ?: null;
}

function save_ml_models($obj){
    $path = ml_model_filepath();
    @mkdir(dirname($path), 0755, true);
    return (bool)@file_put_contents($path, json_encode($obj));
}

// Simple logistic regression (gradient descent) and linear regression trainer
function train_ml_models(){
    $db = get_db();
    $res = $db->query("SELECT attendance, venue_capacity, weekend, is_free, duration_hours, food_stalls, weather, overcrowded, waste_prediction FROM ml_predictions WHERE venue_capacity > 0 AND attendance IS NOT NULL LIMIT 10000");
    if (!$res) return ['success'=>false,'error'=>'No training data'];
    $X = [];
    $y_log = [];
    $y_lin = [];
    while($r = $res->fetch_assoc()){
        $att = floatval($r['attendance']);
        $cap = floatval($r['venue_capacity']);
        if ($cap <= 0) continue;
        // features: attendance_ratio, duration_hours, food_stalls, weekend, is_free, weather
        $feat = [];
        $feat[] = $att / $cap; // ratio
        $feat[] = floatval($r['duration_hours']);
        $feat[] = floatval($r['food_stalls']);
        $feat[] = intval($r['weekend']);
        $feat[] = intval($r['is_free']);
        $feat[] = floatval($r['weather']);
        $X[] = $feat;
        $y_log[] = intval($r['overcrowded']);
        $y_lin[] = floatval($r['waste_prediction']);
    }
    $n = count($X);
    if ($n < 30) return ['success'=>false,'error'=>'Not enough training rows (need >=30)'];

    // Normalize features (mean/std)
    $m = count($X[0]);
    $mean = array_fill(0,$m,0.0);
    $std = array_fill(0,$m,0.0);
    for($j=0;$j<$m;$j++){ foreach($X as $i=>$row) $mean[$j] += $row[$j]; $mean[$j] /= $n; }
    for($j=0;$j<$m;$j++){ foreach($X as $i=>$row) $std[$j] += pow($row[$j]-$mean[$j],2); $std[$j] = sqrt($std[$j]/$n); if ($std[$j] == 0) $std[$j] = 1.0; }
    $Xn = [];
    for($i=0;$i<$n;$i++){ $rrow = []; for($j=0;$j<$m;$j++) $rrow[] = ($X[$i][$j]-$mean[$j])/$std[$j]; $Xn[] = $rrow; }

    // Train logistic regression via gradient descent
    $lr = 0.05; $iters = 800; $weights = array_fill(0,$m,0.0); $bias = 0.0;
    for($it=0;$it<$iters;$it++){
        $dw = array_fill(0,$m,0.0); $dbb = 0.0;
        for($i=0;$i<$n;$i++){
            $z = $bias; for($j=0;$j<$m;$j++) $z += $weights[$j]*$Xn[$i][$j];
            $pred = 1.0/(1.0+exp(-$z));
            $err = $pred - $y_log[$i];
            for($j=0;$j<$m;$j++) $dw[$j] += $err * $Xn[$i][$j];
            $dbb += $err;
        }
        for($j=0;$j<$m;$j++) $weights[$j] -= $lr * ($dw[$j]/$n + 0.001*$weights[$j]);
        $bias -= $lr * ($dbb/$n);
    }

    // Train linear regression (normal equation) for waste (use un-normalized features with bias)
    // We'll use simple linear regression on the same normalized features for stability
    // Compute coefficients via normal equation: (X^T X + lambda I)^{-1} X^T y
    // Build matrices
    $lambda = 0.01;
    // Compute XT_X and XT_y
    $XT_X = array_fill(0,$m, array_fill(0,$m,0.0));
    $XT_y = array_fill(0,$m,0.0);
    for($i=0;$i<$n;$i++){
        for($a=0;$a<$m;$a++){
            for($b=0;$b<$m;$b++) $XT_X[$a][$b] += $Xn[$i][$a]*$Xn[$i][$b];
            $XT_y[$a] += $Xn[$i][$a] * $y_lin[$i];
        }
    }
    for($a=0;$a<$m;$a++) $XT_X[$a][$a] += $lambda;
    // Solve linear system XT_X * w = XT_y via simple Gauss-Jordan (since m is small ~6)
    $A = $XT_X; $bvec = $XT_y; $M = $m;
    // augment
    for($i=0;$i<$M;$i++){ $A[$i][] = $bvec[$i]; }
    // Gauss-Jordan
    for($i=0;$i<$M;$i++){
        // pivot
        $maxRow = $i; for($k=$i+1;$k<$M;$k++) if (abs($A[$k][$i]) > abs($A[$maxRow][$i])) $maxRow = $k;
        if ($i != $maxRow) { $tmp = $A[$i]; $A[$i] = $A[$maxRow]; $A[$maxRow] = $tmp; }
        if (abs($A[$i][$i]) < 1e-12) continue;
        $div = $A[$i][$i]; for($j=$i;$j<=$M;$j++) $A[$i][$j] /= $div;
        for($r2=0;$r2<$M;$r2++) if ($r2 != $i){ $factor = $A[$r2][$i]; for($c=$i;$c<=$M;$c++) $A[$r2][$c] -= $factor * $A[$i][$c]; }
    }
    $w_lin = array_fill(0,$M,0.0);
    for($i=0;$i<$M;$i++) $w_lin[$i] = $A[$i][$M];

    $models = [
        'logistic' => ['weights'=>$weights,'bias'=>$bias,'mean'=>$mean,'std'=>$std],
        'linear_waste' => ['weights'=>$w_lin,'mean'=>$mean,'std'=>$std],
        'trained_at' => date('c'), 'rows'=>$n
    ];
    save_ml_models($models);
    return ['success'=>true,'rows'=>$n,'trained_at'=>date('c')];
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? null);

// Point-in-polygon helper (ray-casting). Polygon is array of [lat, lng] pairs.
function point_in_polygon($pointLat, $pointLng, $polygon) {
    $inside = false;
    $j = count($polygon) - 1;
    for ($i = 0; $i < count($polygon); $i++) {
        $yi = $polygon[$i][0]; $xi = $polygon[$i][1];
        $yj = $polygon[$j][0]; $xj = $polygon[$j][1];
        $intersect = ((($yi > $pointLat) != ($yj > $pointLat)) &&
                      ($pointLng < ($xj - $xi) * ($pointLat - $yi) / ($yj - $yi + 0.0) + $xi));
        if ($intersect) $inside = !$inside;
        $j = $i;
    }
    return $inside;
}

// Load Legazpi municipal boundary from disk (backend/data/legazpi_boundary.json) if available.
// If not present, attempt to fetch from Nominatim and save it locally. The function
// returns an array of [lat, lng] pairs representing a polygon (first ring).
function get_legazpi_boundary_filepath(){
    return __DIR__ . '/data/legazpi_boundary.json';
}

function fetch_legazpi_from_nominatim(){
    $query = urlencode('Legazpi City, Albay, Philippines');
    $url = 'https://nominatim.openstreetmap.org/search.php?q=' . $query . '&polygon_geojson=1&format=jsonv2';
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: capstone-backend/1.0 (contact: admin)\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $context);
    if (!$raw) return null;
    $arr = json_decode($raw, true);
    if (!is_array($arr) || count($arr) === 0) return null;
    // Prefer the first result that contains geojson
    foreach ($arr as $item){
        if (!empty($item['geojson'])){
            $geo = $item['geojson'];
            $out = ['type' => 'Feature', 'geometry' => $geo];
            return $out;
        }
    }
    return null;
}

function legazpi_polygon(){
    static $cached = null;
    if ($cached !== null) return $cached;
    $path = get_legazpi_boundary_filepath();
    // Attempt to read local file first
    if (is_readable($path)){
        $raw = @file_get_contents($path);
        $json = json_decode($raw, true);
        if ($json && isset($json['geometry'])) $geo = $json['geometry'];
        else if ($json && isset($json['type']) && ($json['type']==='Feature' || $json['type']==='Polygon' || $json['type']==='MultiPolygon')) $geo = isset($json['geometry']) ? $json['geometry'] : $json;
        else $geo = null;
    } else {
        // Try to fetch from Nominatim and persist
        $fetched = fetch_legazpi_from_nominatim();
        if ($fetched){
            @file_put_contents($path, json_encode($fetched));
            $geo = $fetched['geometry'];
        } else {
            $geo = null;
        }
    }

    // Convert geometry to first ring of coordinates as array of [lat,lng]
    $coords = [];
    if ($geo && isset($geo['type']) && isset($geo['coordinates'])){
        if ($geo['type'] === 'Polygon'){
            $ring = $geo['coordinates'][0];
        } else if ($geo['type'] === 'MultiPolygon'){
            $ring = $geo['coordinates'][0][0];
        } else {
            $ring = null;
        }
        if ($ring && is_array($ring)){
            foreach ($ring as $pt){
                // GeoJSON is [lng, lat]
                $coords[] = [floatval($pt[1]), floatval($pt[0])];
            }
        }
    }

    // Fallback: original conservative hardcoded polygon
    if (empty($coords)){
        $coords = [
            [13.1982, 123.6638],
            [13.1870, 123.6920],
            [13.1760, 123.7240],
            [13.1625, 123.7505],
            [13.1450, 123.7670],
            [13.1280, 123.7750],
            [13.1060, 123.7750],
            [13.0890, 123.7690],
            [13.0740, 123.7510],
            [13.0640, 123.7300],
            [13.0590, 123.7050],
            [13.0610, 123.6840],
            [13.0730, 123.6690],
            [13.0990, 123.6570],
            [13.1370, 123.6550],
            [13.1720, 123.6560],
            [13.1982, 123.6638]
        ];
    }

    $cached = $coords;
    return $cached;
}

function is_in_legazpi($lat, $lng){
    if (!is_numeric($lat) || !is_numeric($lng)) return false;
    return point_in_polygon(floatval($lat), floatval($lng), legazpi_polygon());
}

// Get coordinates for a destination name
function get_destination_coords($destination_name) {
    $db = get_db();
    // First try to get from destinations table
    $stmt = $db->prepare("SELECT latitude, longitude FROM destinations WHERE name = ? LIMIT 1");
    $stmt->bind_param('s', $destination_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        return ['lat' => floatval($row['latitude']), 'lon' => floatval($row['longitude'])];
    }

    // Fallback to itinerary_destinations table
    $stmt = $db->prepare("SELECT latitude, longitude FROM itinerary_destinations WHERE name = ? LIMIT 1");
    $stmt->bind_param('s', $destination_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        return ['lat' => floatval($row['latitude']), 'lon' => floatval($row['longitude'])];
    }

    // If not found in database, return default Legazpi coordinates
    return ['lat' => 13.1418, 'lon' => 123.7438]; // Default Legazpi coordinates
}

// Helper function to normalize image paths for frontend access
// Converts old database paths to paths that work from frontend/ subfolder
function normalize_image_path($path){
    if (!$path) return $path;
    // External URLs are unchanged
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) return $path;

    // If caller already provided a frontend-relative path, leave it
    if (strpos($path, '../backend/uploads/') === 0 || strpos($path, 'img/uploads/') === 0) return $path;

    // If it's pointing to the backend uploads directory, try to map to frontend
    if (strpos($path, 'backend/uploads/') === 0) {
        $basename = basename($path);
        // prefer the mirrored frontend folder if the file exists there
        $frontendFile = __DIR__ . '/../frontend/img/uploads/' . $basename;
        if (file_exists($frontendFile)) {
            // return a path relative to the frontend HTML (no ../ required)
            return 'img/uploads/' . $basename;
        }
        // otherwise fall back to the original backend location
        return '../' . $path;
    }

    // Anything else (perhaps already correct) is just returned
    return $path;
}

// List events
if ($action === 'list_events'){
    $db = get_db();
    if (!$db) {
        j(['error' => 'Database connection failed', 'events' => []]);
        exit;
    }
    
    $res = $db->query("SELECT id, title, description, image, datetime, location, capacity, author, anonymous, event_type, prediction, start_lat, start_lng, end_lat, end_lng, created_at FROM events ORDER BY created_at DESC");
    if (!$res) {
        j(['error' => $db->error, 'events' => []]);
        exit;
    }
    
    $rows = [];
    while($r = $res->fetch_assoc()) {
        // Normalize image paths for frontend access
        if (!empty($r['image'])) {
            $r['image'] = normalize_image_path($r['image']);
        }
        // Decode prediction JSON if present
        if (!empty($r['prediction'])) {
            $pred = json_decode($r['prediction'], true);
            if (is_array($pred)) {
                $r['prediction'] = $pred;
            }
        }
        $rows[] = $r;
    }
    
    j(['events' => $rows]);
}
// List users (guests)
if ($action === 'list_users'){
    $db = get_db();
    $res = $db->query("SELECT id, name, email, city, created_at FROM users ORDER BY created_at DESC LIMIT 100");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['users'=>$rows]);
}

// Create event (admin)
if ($action === 'create_event'){
    // allow multipart/form-data upload (image handled separately) or JSON
    require_admin();
    $d = json_input();
    $db = get_db();
    $title = $d['title'] ?? '';
    $description = $d['description'] ?? '';
    $image_path = $d['image_path'] ?? null;
    $datetime = $d['datetime'] ?? '';
    $start_time = $d['start_time'] ?? null;
    $end_time = $d['end_time'] ?? null;
    $location = $d['location'] ?? '';
    $capacity = intval($d['capacity'] ?? 0);
    $event_type = $d['event_type'] ?? 'other';
    $author = $d['author'] ?? '';
    $anon = !empty($d['anonymous']) ? 1 : 0;
    
    // Ensure columns exist
    $colCheck = function($db, $col){ $res = $db->query("SHOW COLUMNS FROM events LIKE '".$db->real_escape_string($col)."'"); return $res && $res->num_rows>0; };
    if (!$colCheck($db, 'start_time')) {
        $db->query("ALTER TABLE events ADD COLUMN start_time TIME DEFAULT NULL");
    }
    if (!$colCheck($db, 'end_time')) {
        $db->query("ALTER TABLE events ADD COLUMN end_time TIME DEFAULT NULL");
    }
    if (!$colCheck($db, 'event_type')) {
        $db->query("ALTER TABLE events ADD COLUMN event_type VARCHAR(50) DEFAULT 'other'");
    }
    if (!$colCheck($db, 'prediction')) {
        $db->query("ALTER TABLE events ADD COLUMN prediction LONGTEXT DEFAULT NULL");
    }
    
    $stmt = $db->prepare('INSERT INTO events (title,description,image,datetime,start_time,end_time,event_type,location,capacity,author,anonymous) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    // types: title(s), description(s), image(s), datetime(s), start_time(s), end_time(s), event_type(s), location(s), capacity(i), author(s), anonymous(i)
    $stmt->bind_param('ssssssssisi', $title, $description, $image_path, $datetime, $start_time, $end_time, $event_type, $location, $capacity, $author, $anon);
    if ($stmt->execute()){
        $event_id = $db->insert_id;

        // Persist admin-provided coordinates if present and table supports them
        $start_lat = isset($d['start_lat']) ? floatval($d['start_lat']) : null;
        $start_lng = isset($d['start_lng']) ? floatval($d['start_lng']) : null;
        $end_lat = isset($d['end_lat']) ? floatval($d['end_lat']) : null;
        $end_lng = isset($d['end_lng']) ? floatval($d['end_lng']) : null;
        if (($start_lat !== null || $start_lng !== null || $end_lat !== null || $end_lng !== null) && $colCheck($db,'start_lat')){
            if (($start_lat !== null && $start_lng !== null) && !is_in_legazpi($start_lat, $start_lng)) { error_log('Start coords outside Legazpi municipal boundary; not saved.'); }
            else if (($end_lat !== null && $end_lng !== null) && !is_in_legazpi($end_lat, $end_lng)) { error_log('End coords outside Legazpi municipal boundary; not saved.'); }
            else {
                $ust = $db->prepare('UPDATE events SET start_lat=?, start_lng=?, end_lat=?, end_lng=? WHERE id=?');
                $ust->bind_param('ddddi', $start_lat, $start_lng, $end_lat, $end_lng, $event_id);
                @$ust->execute();
            }
        }

        // Auto-generate ML prediction for this event
        // NOTE: Prediction generation now happens on frontend via event_prediction_api.php
        // This ensures consistent use of the new event type-based models
        // The frontend will call event_prediction_api.php?action=generate after event creation
        // and save the result via api.php?action=save_event_prediction

        j(['success'=>true,'id'=>$event_id]);
    }
    j(['success'=>false,'error'=>$stmt->error]);
}

// Admin: set or update event route coordinates (start/end)
if ($action === 'set_event_route'){
    require_admin();
    $d = json_input();
    $event_id = intval($d['event_id'] ?? $_GET['event_id'] ?? 0);
    $start_lat = isset($d['start_lat']) ? floatval($d['start_lat']) : (isset($_GET['start_lat']) ? floatval($_GET['start_lat']) : null);
    $start_lng = isset($d['start_lng']) ? floatval($d['start_lng']) : (isset($_GET['start_lng']) ? floatval($_GET['start_lng']) : null);
    $end_lat = isset($d['end_lat']) ? floatval($d['end_lat']) : (isset($_GET['end_lat']) ? floatval($_GET['end_lat']) : null);
    $end_lng = isset($d['end_lng']) ? floatval($d['end_lng']) : (isset($_GET['end_lng']) ? floatval($_GET['end_lng']) : null);
    if ($event_id <= 0) j(['success'=>false,'error'=>'invalid event_id']);
    $db = get_db();
    $colCheck = function($db, $col){ $res = $db->query("SHOW COLUMNS FROM events LIKE '".$db->real_escape_string($col)."'"); return $res && $res->num_rows>0; };
    if (!$colCheck($db,'start_lat')){
        j(['success'=>false,'error'=>'events table missing start_lat/start_lng/end_lat/end_lng columns. Run migration to add them.']);
    }
    // validate coordinates using municipal polygon
    if (($start_lat !== null && $start_lng !== null) && !is_in_legazpi($start_lat, $start_lng)) j(['success'=>false,'error'=>'start coordinates are outside Legazpi municipal boundary']);
    if (($end_lat !== null && $end_lng !== null) && !is_in_legazpi($end_lat, $end_lng)) j(['success'=>false,'error'=>'end coordinates are outside Legazpi municipal boundary']);

    $stmt = $db->prepare('UPDATE events SET start_lat=?, start_lng=?, end_lat=?, end_lng=? WHERE id=?');
    $stmt->bind_param('ddddi', $start_lat, $start_lng, $end_lat, $end_lng, $event_id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Generate ML prediction for an event
// Detect event type from title and description (SMART TYPE DETECTION)
function detectEventType($title, $description) {
    $combined = strtolower($title . ' ' . ($description ?? ''));
    
    // Festival/Parade types - HIGH attendance (75-90% capacity)
    if (preg_match('/festival|parade|carnival|fiesta|celebration|grand|annual|city festival/i', $title)) {
        return ['type' => 'festival', 'attendance_ratio' => 0.80, 'food_stalls_multiplier' => 1.5];
    }
    
    // Concert/Music - HIGH attendance (70-85% capacity)
    if (preg_match('/concert|music|band|performance|show|entertainment|live|artist|musician/i', $combined)) {
        return ['type' => 'concert', 'attendance_ratio' => 0.75, 'food_stalls_multiplier' => 1.2];
    }
    
    // Sports events - MODERATE-HIGH attendance (55-75% capacity)
    if (preg_match('/sports|game|match|tournament|race|football|basketball|marathon|competition|athletic|sports event/i', $combined)) {
        return ['type' => 'sports', 'attendance_ratio' => 0.65, 'food_stalls_multiplier' => 0.8];
    }
    
    // Community/Educational - LIGHT-MODERATE attendance (30-50% capacity)
    if (preg_match('/community|seminar|workshop|training|lecture|congress|conference|talk|health|education|meeting|gathering/i', $combined)) {
        return ['type' => 'community', 'attendance_ratio' => 0.40, 'food_stalls_multiplier' => 0.5];
    }
    
    // Market/Fair/Expo - MODERATE-HIGH attendance (60-80% capacity)
    if (preg_match('/market|fair|expo|bazaar|trade|shopping|retail|farmers|craft|bazaar/i', $combined)) {
        return ['type' => 'market', 'attendance_ratio' => 0.70, 'food_stalls_multiplier' => 1.3];
    }
    
    // Food/Dining events - MODERATE attendance (45-65% capacity)
    if (preg_match('/food|taste|culinary|dining|restaurant|cook|feast|banquet|catering|food festival/i', $combined)) {
        return ['type' => 'food', 'attendance_ratio' => 0.55, 'food_stalls_multiplier' => 1.8];
    }
    
    // Default - LIGHT attendance (25-45% capacity)
    return ['type' => 'general', 'attendance_ratio' => 0.35, 'food_stalls_multiplier' => 0.6];
}

function generateEventPrediction($attendance, $capacity, $weekend, $is_free, $duration, $food_stalls, $weather, $title = '', $description = '') {
    // INTELLIGENT Event Type Detection
    $event_info = detectEventType($title, $description);
    $event_type = $event_info['type'];
    $base_attendance_ratio = $event_info['attendance_ratio'];
    $food_stalls_multiplier = $event_info['food_stalls_multiplier'];
    
    // RE-CALCULATE attendance based on event type
    // For small events (< 500 capacity), use smaller multipliers
    // For large events (> 5000), use more aggressive multipliers
    $smart_attendance = intval($capacity * $base_attendance_ratio);
    
    // CONSERVATIVE weekend adjustment: weekends typically see 5-10% more visitors (not 15%)
    if ($weekend) {
        // Only boost if not already at high capacity
        if ($base_attendance_ratio < 0.75) {
            $smart_attendance = intval($smart_attendance * 1.08);
        }
    }
    
    // CONSERVATIVE free event adjustment: free events typically see 10-15% more visitors (not 20%)
    if ($is_free) {
        // Only boost if not already at high capacity
        if ($base_attendance_ratio < 0.75) {
            $smart_attendance = intval($smart_attendance * 1.12);
        }
    }
    
    // Ensure attendance doesn't exceed capacity
    $smart_attendance = min($smart_attendance, intval($capacity * 0.95));
    
    $capacity_ratio = $smart_attendance / $capacity;
    
    // Try to use trained ML models (if available) to predict overcrowding probability and waste
    $overcrowding_prob = 0.0;
    
    // Try GBM model first (if available)
    $gb_model_path = __DIR__ . '/data/gb_model.pkl';
    if (is_readable($gb_model_path)) {
        $feat_data = [
            'attendance' => $smart_attendance,
            'venue_capacity' => $capacity,
            'duration_hours' => floatval($duration),
            'food_stalls' => floatval($food_stalls),
            'weekend' => intval($weekend),
            'is_free' => intval($is_free),
            'weather' => intval($weather)
        ];
        $py = 'python3';
        $script = __DIR__ . '/ml/predict_model.py';
        $input = escapeshellarg(json_encode($feat_data));
        $cmd = "$py " . escapeshellarg($script) . " " . $input . " 2>&1";
        @exec($cmd, $py_out, $rc);
        if ($rc === 0) {
            $txt = implode("\n", $py_out);
            $py_result = json_decode($txt, true);
            if ($py_result && ($py_result['success'] ?? false)) {
                if (isset($py_result['overcrowding_probability'])) {
                    $overcrowding_prob = floatval($py_result['overcrowding_probability']);
                }
                if (isset($py_result['waste_prediction'])) {
                    $waste = floatval($py_result['waste_prediction']);
                }
            }
        }
    }
    
    // If GBM didn't produce results, try simpler logistic regression
    $models = load_ml_models();
    if ($overcrowding_prob === 0.0 && $models && isset($models['logistic']) && isset($models['linear_waste'])) {
        $feat = [];
        $feat[] = ($smart_attendance / max(1, $capacity));
        $feat[] = floatval($duration);
        $feat[] = floatval($food_stalls);
        $feat[] = intval($weekend);
        $feat[] = intval($is_free);
        $feat[] = intval($weather);
        $means = $models['logistic']['mean'];
        $stds = $models['logistic']['std'];
        $w = $models['logistic']['weights'];
        $b = $models['logistic']['bias'];
        // compute normalized features and logistic
        $z = $b;
        for ($i=0;$i<count($feat);$i++){
            $xnorm = ($feat[$i] - ($means[$i] ?? 0)) / ($stds[$i] ?? 1.0);
            $z += ($w[$i] ?? 0) * $xnorm;
        }
        $overcrowding_prob = 1.0 / (1.0 + exp(-$z));
        // waste via linear model (weights are for normalized features)
        $wlin = $models['linear_waste']['weights'];
        $waste_est = 0.0;
        for ($i=0;$i<count($feat);$i++){
            $xnorm = ($feat[$i] - ($models['linear_waste']['mean'][$i] ?? 0)) / ($models['linear_waste']['std'][$i] ?? 1.0);
            $waste_est += ($wlin[$i] ?? 0) * $xnorm;
        }
        // scale waste estimate to sensible positive value
        $waste = max(0.0, round($waste_est, 2));
    } else {
        // Fallback heuristic when no trained model
        // IMPROVED Overcrowding Model: heuristics based on capacity_ratio
        if ($capacity_ratio <= 0.40) {
            $overcrowding_prob = $capacity_ratio * 0.15;
        } elseif ($capacity_ratio <= 0.60) {
            $overcrowding_prob = 0.06 + (($capacity_ratio - 0.40) / 0.20) * 0.14;
        } elseif ($capacity_ratio <= 0.75) {
            $overcrowding_prob = 0.20 + (($capacity_ratio - 0.60) / 0.15) * 0.30;
        } elseif ($capacity_ratio <= 0.85) {
            $overcrowding_prob = 0.50 + (($capacity_ratio - 0.75) / 0.10) * 0.22;
        } else {
            $overcrowding_prob = 0.72 + (($capacity_ratio - 0.85) / 0.15) * 0.28;
        }
        $overcrowding_prob = max(0, min($overcrowding_prob, 1.0));
        // existing waste calculation will be used below
    }
    
    // Only mark as "overcrowded" when truly over capacity
    $overcrowded = $capacity_ratio > 0.90 ? 1 : 0;
    
    // Weather impact: bad weather reduces attendance significantly
    if ($weather) {
        $smart_attendance = intval($smart_attendance * 0.80);  // 20% reduction
        $overcrowding_prob = max(0, $overcrowding_prob - 0.10);
    }
    
    // REALISTIC Waste prediction model - empirically calibrated
    $per_capita_waste = 0.25; // Base waste per person per 8 hours
    
    // Event-type specific waste per person
    if ($event_type === 'festival') {
        $per_capita_waste = 0.40; // Lots of packaging, entertainment waste
    } elseif ($event_type === 'concert') {
        $per_capita_waste = 0.35; // Drink bottles, food wrappers
    } elseif ($event_type === 'food') {
        $per_capita_waste = 0.50; // High food waste, leftovers
    } elseif ($event_type === 'market') {
        $per_capita_waste = 0.38; // Packaging from shopping, bags
    } elseif ($event_type === 'sports') {
        $per_capita_waste = 0.28; // Bottles, towels, equipment
    } elseif ($event_type === 'community') {
        $per_capita_waste = 0.15; // Minimal waste at formal gatherings
    }
    
    $waste_from_visitors = $smart_attendance * $per_capita_waste * ($duration / 8.0);
    
    // Food stall contribution - adjusted by event type and capped realistically
    $adjusted_food_stalls = intval(min($food_stalls * $food_stalls_multiplier, max(1, $smart_attendance / 20)));
    
    // More realistic per-stall waste (not scaling aggressively with visitors)
    $per_stall_waste = 2.0 + (0.005 * ($smart_attendance / max($adjusted_food_stalls, 1)));
    $waste_from_stalls = $adjusted_food_stalls * $per_stall_waste;
    
    // Weather multiplier
    $weather_multiplier = $weather ? 1.08 : 1.0;  // Reduced from 1.1
    
    // Final waste estimate
    $waste = ($waste_from_visitors + $waste_from_stalls) * $weather_multiplier;

    // If a trained linear waste model exists and produced an estimate earlier, prefer it
    if (isset($waste_est) && is_numeric($waste_est) && $waste_est > 0) {
        $waste = round($waste_est, 2);
    }
    
    // Build balanced scenarios: low (conservative), medium (expected), high (peak)
    $scenario_low_att = max(0, intval($smart_attendance * 0.7));
    $scenario_med_att = intval($smart_attendance);
    $scenario_high_att = intval(min($capacity * 0.95, max($smart_attendance, intval($smart_attendance * 1.3))));

    $calc_waste = function($att) use ($per_capita_waste, $duration, $adjusted_food_stalls, $per_stall_waste, $weather_multiplier) {
        $w_vis = $att * $per_capita_waste * ($duration / 8.0);
        $w_stalls = $adjusted_food_stalls * $per_stall_waste;
        return round(($w_vis + $w_stalls) * $weather_multiplier, 2);
    };

    $scenarios = [
        'low' => [
            'attendance' => (float)$scenario_low_att,
            'waste_prediction' => (float)$calc_waste($scenario_low_att),
            'overcrowding_probability' => (float)round(max(0, min(1, $overcrowding_prob * 0.7)), 4),
            'overcrowded' => (int)($scenario_low_att / $capacity > 0.90 ? 1 : 0)
        ],
        'medium' => [
            'attendance' => (float)$scenario_med_att,
            'waste_prediction' => (float)$calc_waste($scenario_med_att),
            'overcrowding_probability' => (float)round($overcrowding_prob, 4),
            'overcrowded' => (int)($scenario_med_att / $capacity > 0.90 ? 1 : 0)
        ],
        'high' => [
            'attendance' => (float)$scenario_high_att,
            'waste_prediction' => (float)$calc_waste($scenario_high_att),
            'overcrowding_probability' => (float)round(min(1, $overcrowding_prob * 1.25), 4),
            'overcrowded' => (int)($scenario_high_att / $capacity > 0.90 ? 1 : 0)
        ]
    ];

    return [
        'attendance' => (float)$smart_attendance,
        'venue_capacity' => (float)$capacity,
        'weekend' => (int)$weekend,
        'is_free' => (int)$is_free,
        'duration_hours' => (float)$duration,
        'food_stalls' => (float)$adjusted_food_stalls,
        'weather' => (int)$weather,
        'event_type' => $event_type,
        'overcrowded' => (int)$overcrowded,
        'waste_prediction' => (float)round($waste, 2),
        'overcrowding_probability' => (float)round($overcrowding_prob, 4),
        'scenarios' => $scenarios
    ];
}

function generateEventPrediction_OLD($attendance, $capacity, $weekend, $is_free, $duration, $food_stalls, $weather) {
    $capacity_ratio = $attendance / $capacity;
    
    // SMARTER Overcrowding Model: Only trigger HIGH risk when venue is actually crowded (>80%) or becoming critical (>90%)
    // LOW RISK: <= 60% capacity
    // MEDIUM RISK: 60-85% capacity
    // HIGH RISK: > 85% capacity
    
    $overcrowding_prob = 0.0;
    
    if ($capacity_ratio < 0.6) {
        $overcrowding_prob = $capacity_ratio * 0.5;  // 0-30% for low capacity
    } elseif ($capacity_ratio < 0.85) {
        $overcrowding_prob = 0.3 + (($capacity_ratio - 0.6) / 0.25) * 0.35;  // 30-65% for medium
    } else {
        $overcrowding_prob = 0.65 + (($capacity_ratio - 0.85) / 0.15) * 0.35;  // 65-100% for high
    }
    
    // Small adjustments for external factors
    if ($weekend && $capacity_ratio > 0.7) $overcrowding_prob += 0.02;
    if ($is_free && $capacity_ratio > 0.7) $overcrowding_prob += 0.02;
    if ($weather && $capacity_ratio > 0.7) $overcrowding_prob += 0.01;
    
    $overcrowding_prob = max(0, min($overcrowding_prob, 1.0));
    $overcrowded = $capacity_ratio > 0.85 ? 1 : 0;
    
    // IMPROVED Waste prediction model - realistic calculation
    // Base waste per visitor (kg): 0.3 kg per person per 8-hour event
    $per_capita_waste = 0.3;
    $waste_from_visitors = $attendance * $per_capita_waste * ($duration / 8.0);
    
    // Food stall contribution (kg): modest waste from food operations
    // 3 kg base waste per stall + 0.01 kg per visitor (not 0.05)
    $per_stall_waste = 3.0 + (0.01 * ($attendance / max($food_stalls, 1)));
    $waste_from_stalls = $food_stalls * $per_stall_waste;
    
    // Weather multiplier (rain increases waste slightly due to more cleanup)
    $weather_multiplier = $weather ? 1.1 : 1.0;
    
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
        'overcrowding_probability' => (float)round($overcrowding_prob, 4)
    ];
}

// Edit event (admin)
if ($action === 'edit_event'){
    require_admin();
    $d = json_input();
    $db = get_db();
    // Extract and prepare variables for bind_param
    $title = $d['title'] ?? '';
    $description = $d['description'] ?? '';
    $datetime = $d['datetime'] ?? '';
    $location = $d['location'] ?? '';
    $capacity = intval($d['capacity'] ?? 0);
    $id = intval($d['id'] ?? 0);
    $image_path = $d['image_path'] ?? null;
    // If image_path provided, update image too
    if (!empty($d['image_path'])){
        $stmt = $db->prepare('UPDATE events SET title=?,description=?,datetime=?,location=?,capacity=?,image=? WHERE id=?');
        $stmt->bind_param('ssssisi', $title, $description, $datetime, $location, $capacity, $image_path, $id);
    } else {
        $stmt = $db->prepare('UPDATE events SET title=?,description=?,datetime=?,location=?,capacity=? WHERE id=?');
        $stmt->bind_param('ssssii', $title, $description, $datetime, $location, $capacity, $id);
    }
    if ($stmt->execute()){
        // If admin provided coordinates, attempt to persist if table supports columns
        $start_lat = isset($d['start_lat']) ? floatval($d['start_lat']) : null;
        $start_lng = isset($d['start_lng']) ? floatval($d['start_lng']) : null;
        $end_lat = isset($d['end_lat']) ? floatval($d['end_lat']) : null;
        $end_lng = isset($d['end_lng']) ? floatval($d['end_lng']) : null;
        $colCheck = function($db, $col){ $res = $db->query("SHOW COLUMNS FROM events LIKE '".$db->real_escape_string($col)."'"); return $res && $res->num_rows>0; };
        if (($start_lat !== null || $start_lng !== null || $end_lat !== null || $end_lng !== null) && $colCheck($db,'start_lat')){
            $ust = $db->prepare('UPDATE events SET start_lat=?, start_lng=?, end_lat=?, end_lng=? WHERE id=?');
            $ust->bind_param('dddii', $start_lat, $start_lng, $end_lat, $end_lng, $id);
            @$ust->execute();
        }
        j(['success'=>true]);
    }
    j(['success'=>false,'error'=>$stmt->error]);
}

// Delete event (admin)
if ($action === 'delete_event'){
    require_admin();
    $d = json_input();
    $id = $d['id'] ?? $_GET['id'] ?? $_POST['id'] ?? null;
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM events WHERE id=?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Shops
if ($action === 'list_shops'){
    $db = get_db();
    $res = $db->query('SELECT * FROM shops ORDER BY created_at DESC');
    $rows = [];
    while($r = $res->fetch_assoc()) {
        // Normalize image paths for frontend access
        if (!empty($r['image'])) {
            $r['image'] = normalize_image_path($r['image']);
        }
        $rows[] = $r;
    }
    j(['shops'=>$rows]);
}

if ($action === 'create_shop'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $contact = $d['contact'] ?? null;
    $owner_name = $d['owner_name'] ?? null;
    $stmt = $db->prepare('INSERT INTO shops (name,description,address,contact,owner_name,image) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('ssssss',$d['name'],$d['description'],$d['address'],$contact,$owner_name,$image);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_shop'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $contact = $d['contact'] ?? null;
    $owner_name = $d['owner_name'] ?? null;
    if ($image) {
        $stmt = $db->prepare('UPDATE shops SET name=?, description=?, address=?, contact=?, owner_name=?, image=? WHERE id=?');
        $stmt->bind_param('ssssssi',$d['name'],$d['description'],$d['address'],$contact,$owner_name,$image,$d['id']);
    } else {
        $stmt = $db->prepare('UPDATE shops SET name=?, description=?, address=?, contact=?, owner_name=? WHERE id=?');
        $stmt->bind_param('sssssi',$d['name'],$d['description'],$d['address'],$contact,$owner_name,$d['id']);
    }
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_shop'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM shops WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Products
if ($action === 'list_products'){
    $db = get_db();
    $shop_id = intval($_GET['shop_id'] ?? 0);
    if ($shop_id > 0) {
        $res = $db->query('SELECT p.*, s.name as shop_name, s.owner_name, pc.name as category_name FROM products p LEFT JOIN shops s ON p.shop_id=s.id LEFT JOIN product_categories pc ON p.category_id = pc.id WHERE p.shop_id=' . $shop_id . ' ORDER BY p.created_at DESC');
    } else {
        $res = $db->query('SELECT p.*, s.name as shop_name, s.owner_name, pc.name as category_name FROM products p LEFT JOIN shops s ON p.shop_id=s.id LEFT JOIN product_categories pc ON p.category_id = pc.id ORDER BY p.created_at DESC');
    }
    if (!$res) {
        j(['error'=>'Query failed: '.$db->error, 'products'=>[]]);
    }
    $rows = [];
    while($r = $res->fetch_assoc()) {
        // Normalize image paths for frontend access
        if (!empty($r['image'])) {
            $r['image'] = normalize_image_path($r['image']);
        }
        $rows[] = $r;
    }
    j(['products'=>$rows]);
}

if ($action === 'create_product'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $category_id = !empty($d['category_id']) ? intval($d['category_id']) : null;
    $stmt = $db->prepare('INSERT INTO products (shop_id,category_id,name,description,price,image) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('iissds',$d['shop_id'],$category_id,$d['name'],$d['description'],$d['price'],$d['image']);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_product'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $category_id = !empty($d['category_id']) ? intval($d['category_id']) : null;
    
    // If image is provided, update it. Otherwise keep existing image
    if (!empty($d['image'])) {
        $stmt = $db->prepare('UPDATE products SET shop_id=?, category_id=?, name=?, description=?, price=?, image=? WHERE id=?');
        // types: shop_id(i), category_id(i), name(s), description(s), price(d), image(s), id(i)
        $stmt->bind_param('iissdsi', $d['shop_id'], $category_id, $d['name'], $d['description'], $d['price'], $d['image'], $d['id']);
    } else {
        $stmt = $db->prepare('UPDATE products SET shop_id=?, category_id=?, name=?, description=?, price=? WHERE id=?');
        $stmt->bind_param('iissdi', $d['shop_id'], $category_id, $d['name'], $d['description'], $d['price'], $d['id']);
    }
    
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_product'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM products WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'list_categories'){
    $db = get_db();
    $shop_id = intval($_GET['shop_id'] ?? 0);
    if ($shop_id > 0) {
        $res = $db->query('SELECT * FROM product_categories WHERE shop_id='.$shop_id.' ORDER BY name ASC');
    } else {
        $res = $db->query('SELECT * FROM product_categories ORDER BY name ASC');
    }
    if (!$res) {
        j(['error'=>'Query failed: '.$db->error, 'categories'=>[]]);
    }
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['categories'=>$rows]);
}

if ($action === 'create_category'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO product_categories (shop_id,name,description) VALUES (?,?,?)');
    $stmt->bind_param('iss',$d['shop_id'],$d['name'],$d['description']);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_category'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('UPDATE product_categories SET name=?, description=? WHERE id=?');
    $stmt->bind_param('ssi',$d['name'],$d['description'],$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_category'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM product_categories WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Feedback (guests)
if ($action === 'add_feedback'){
    $d = json_input();
    $anon = !empty($d['anonymous']) ? 1 : 0;
    $db = get_db();
    // Insert feedback without image for now
    $stmt = $db->prepare('INSERT INTO feedback (user_email,user_name,anonymous,message,rating) VALUES (?,?,?,?,?)');
    $stmt->bind_param('ssisi',$d['email'],$d['name'],$anon,$d['message'],$d['rating']);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// List feedback
if ($action === 'list_feedback'){
    $db = get_db();
    $res = $db->query('SELECT id, user_email as email, user_name as name, anonymous, feedback_type as type, message as feedback, rating, created_at FROM feedback ORDER BY created_at DESC LIMIT 100');
    $feedback = [];
    while ($row = $res->fetch_assoc()) {
        $feedback[] = $row;
    }
    j(['success'=>true,'feedback'=>$feedback]);
}

// Delete feedback (admin only)
if ($action === 'delete_feedback'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM feedback WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Save itinerary
if ($action === 'save_itinerary'){
    $d = json_input();
    $email = $d['email'] ?? '';
    $name = $d['name'] ?? '';
    $anon = !empty($d['anonymous']) ? 1 : 0;
    $title = $d['title'] ?? '';
    $days = intval($d['days'] ?? 1);
    $dest = is_array($d['destinations']) ? json_encode($d['destinations']) : ($d['destinations'] ?? '');
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO itineraries (user_email,user_name,anonymous,title,days,destinations) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('ssisss', $email, $name, $anon, $title, $days, $dest);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Attendance history add (admin)
if ($action === 'log_attendance'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO attendance_history (event_id,location,date,attendance) VALUES (?,?,?,?)');
    $stmt->bind_param('issi',$d['event_id'],$d['location'],$d['date'],$d['attendance']);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Predictive analytics for an event/location
if ($action === 'predict'){
    $location = $_GET['location'] ?? null;
    $event_id = $_GET['event_id'] ?? null;
    $db = get_db();
    $sql = 'SELECT date, attendance FROM attendance_history WHERE ';
    if ($event_id) $sql .= 'event_id=' . intval($event_id) . ' ORDER BY date DESC LIMIT 20';
    else if ($location) $sql .= "location='" . $db->real_escape_string($location) . "' ORDER BY date DESC LIMIT 20";
    else j(['error'=>'missing location or event_id']);

    $res = $db->query($sql);
    $vals = [];
    while($r = $res->fetch_assoc()) $vals[] = ['date'=>$r['date'],'attendance'=>intval($r['attendance'])];

    // Simple predictive: average + linear trend (very basic)
    $n = count($vals);
    if ($n === 0) j(['predicted'=>0,'reason'=>'no history']);
    $sum = 0; $xs = []; $ys = [];
    for ($i=0;$i<$n;$i++){
        $sum += $vals[$i]['attendance'];
        $xs[] = $i;
        $ys[] = $vals[$i]['attendance'];
    }
    $avg = $sum / $n;
    // compute slope by linear regression
    $xbar = $n>0 ? array_sum($xs)/$n : 0;
    $ybar = $avg;
    $num=0;$den=0;
    for ($i=0;$i<$n;$i++){ $num += ($xs[$i]-$xbar)*($ys[$i]-$ybar); $den += ($xs[$i]-$xbar)*($xs[$i]-$xbar); }
    $slope = $den==0?0:$num/$den;
    $pred = max(0, round($ybar + $slope * ($n+1)));

    // waste estimate: assume 0.5 kg/person (configurable)
    $kg_per_person = 0.5;
    $total_kg = $pred * $kg_per_person;
    $recommended_trash_bags = ceil($total_kg / 10);

    $crowd_status = $pred > 0 && isset($_GET['capacity']) && intval($_GET['capacity'])>0 && $pred / intval($_GET['capacity']) > 0.9 ? 'overcrowding' : 'not';

    j(['predicted'=>$pred,'slope'=>$slope,'avg'=>$avg,'waste_kg'=>$total_kg,'trash_bags'=>$recommended_trash_bags,'crowd_status'=>$crowd_status]);
}

// Festival analytics: heatmap points, hourly forecast, phase comparison, confidence
if ($action === 'festival_analytics'){
    $event_id = $_GET['event_id'] ?? null;
    $location = $_GET['location'] ?? null;
    $hour_index = isset($_GET['hour']) ? intval($_GET['hour']) : null; // optional hour selector
    $db = get_db();

    // fetch attendance history aggregated by location
    $sql = 'SELECT location, date, SUM(attendance) as attendance FROM attendance_history';
    $conds = [];
    if ($event_id) $conds[] = 'event_id=' . intval($event_id);
    if ($location) $conds[] = "location='" . $db->real_escape_string($location) . "'";
    if (count($conds) > 0) $sql .= ' WHERE ' . implode(' AND ', $conds);
    $sql .= ' GROUP BY location, date ORDER BY date DESC LIMIT 500';

    $res = $db->query($sql);
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = ['location'=>$r['location'],'date'=>$r['date'],'attendance'=>intval($r['attendance'])];

    // location -> coords mapping (expand as needed)
    $loc_map = [
        'Legazpi City Center' => [13.1448, 123.7435],
        'Ibalong Park' => [13.1417, 123.7351],
        'Embarcadero de Legazpi' => [13.1329, 123.7344],
        'Daraga Church' => [13.1657, 123.6842],
        'Lignon Hill' => [13.1387, 123.7449],
        'Mayon Volcano' => [13.2570, 123.6850]
    ];

    // Aggregate attendance per location
    $agg = [];
    foreach($rows as $r){
        $key = $r['location'] ?: 'Unknown';
        if (!isset($agg[$key])) $agg[$key] = 0;
        $agg[$key] += $r['attendance'];
    }

    // Basic prediction using history
    $vals = array_map(function($r){ return intval($r['attendance']); }, $rows);
    $n = count($vals);
    if ($n === 0){
        $pred = 0; $slope = 0; $avg = 0; $confidence = 30;
    } else {
        $sum = array_sum($vals); $avg = $sum / $n;
        $xs = range(0,$n-1); $ys = $vals;
        $xbar = array_sum($xs)/$n; $ybar = $avg;
        $num = 0; $den = 0;
        for ($i=0;$i<$n;$i++){ $num += ($xs[$i]-$xbar)*($ys[$i]-$ybar); $den += ($xs[$i]-$xbar)*($xs[$i]-$xbar); }
        $slope = $den==0?0:$num/$den;
        $pred = max(0, round($ybar + $slope * ($n+1)));
        $confidence = min(95, 40 + min(50, $n * 4));
    }

    // hourly distribution weights
    $hours = ["6AM","8AM","10AM","12PM","2PM","4PM","6PM","8PM","10PM"];
    $weights = [0.03,0.05,0.12,0.2,0.18,0.15,0.12,0.1,0.05];
    $sumw = array_sum($weights);

    // Build heatmap points per location using predicted_total and optionally filter by hour
    $maxVal = 0;
    $points = [];
    foreach($agg as $loc => $att){
        $coords = $loc_map[$loc] ?? null;
        if (!$coords) continue;
        // default per-location base is proportional to historical total
        $base = intval($att);
        // if we have a global predicted total, distribute by normalized base
        $loc_share = $base;
        $points[] = ['location'=>$loc, 'lat'=>floatval($coords[0]), 'lon'=>floatval($coords[1]), 'base'=>$base, 'pred_share'=>$loc_share];
        if ($loc_share > $maxVal) $maxVal = $loc_share;
    }

    // if hour specified, compute hourly value per point using weights and predicted total
    $out_points = [];
    foreach($points as $p){
        $hourly_att = $p['pred_share'];
        if ($pred > 0){
            // scale predicted total to location proportion of historical shares
            $loc_prop = $maxVal>0 ? ($p['pred_share'] / $maxVal) : (1/count($points));
            $loc_pred_total = round($pred * $loc_prop);
            if ($hour_index !== null && isset($weights[$hour_index])){
                $hourly_att = round($loc_pred_total * ($weights[$hour_index] / $sumw));
            } else {
                // provide hourly breakdown array
                $hourly_att = array_map(function($w) use ($loc_pred_total, $sumw){ return round($loc_pred_total * ($w/$sumw)); }, $weights);
            }
        }
        $out_points[] = ['location'=>$p['location'],'lat'=>$p['lat'],'lon'=>$p['lon'],'attendance'=>$hourly_att, 'intensity'=> $maxVal>0 ? round((is_array($hourly_att)?($hourly_att[0]/$maxVal):($hourly_att/$maxVal)),3) : 0.1];
    }

    // Phase comparison: simple heuristic using thirds of history
    $phase = ['before'=>0,'during'=>0,'after'=>0];
    if ($n>0){
        $t = intdiv($n,3) ?: 1;
        $before = array_slice($vals, 0, $t);
        $during = array_slice($vals, $t, $t);
        $after = array_slice($vals, $t*2);
        $phase['before'] = $before ? round(array_sum($before)/count($before)) : 0;
        $phase['during'] = $during ? round(array_sum($during)/count($during)) : ($vals[$n-1] ?? 0);
        $phase['after'] = $after ? round(array_sum($after)/count($after)) : 0;
    }

    j(['success'=>true,'heatmap_points'=>$out_points,'hourly_labels'=>$hours,'hourly_values'=>($pred>0?array_map(function($w) use($pred,$sumw){ return round($pred * ($w/$sumw)); }, $weights):[]),'phase'=>$phase,'predicted_total'=>$pred,'ai_confidence'=>$confidence,'history_count'=>$n]);
}

// Log generic activity (page views, actions) — used by frontend for analytics
if ($action === 'log_activity'){
    $d = json_input();
    $db = get_db();
    $meta = isset($d['meta']) ? (is_array($d['meta'])?json_encode($d['meta']):$d['meta']) : null;
    $stmt = $db->prepare('INSERT INTO activity_logs (user_email,user_name,anonymous,page,action,meta) VALUES (?,?,?,?,?,?)');
    $anon = !empty($d['anonymous']) ? 1 : 0;
    $stmt->bind_param('sssiss',$d['email'],$d['name'],$anon,$d['page'],$d['action'],$meta);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// New: Unified anonymous event logger (page_view, click, search, heartbeat, page_unload)
if ($action === 'log_event'){
    $d = json_input();
    $db = get_db();
    
    // Handle itinerary/event logging (new system)
    if (!empty($d['action'])){
        // This is an itinerary event (PDF save, etc.)
        $eventAction = $d['action'] ?? 'unknown';
        $eventData = json_encode($d);
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Create table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS admin_event_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_action VARCHAR(100),
            event_data LONGTEXT,
            timestamp DATETIME,
            user_agent TEXT,
            ip_address VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $db->query($createTable);
        
        // Insert event to admin_event_logs
        $stmt = $db->prepare('INSERT INTO admin_event_logs (event_action, event_data, timestamp, user_agent, ip_address) VALUES (?, ?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sssss', $eventAction, $eventData, $timestamp, $userAgent, $ip);
            $stmt->execute();
        }
        
        // Also insert to activity_logs for guest activity tracking
        $userEmail = $d['email'] ?? '';
        $userName = $d['name'] ?? '';
        $pageVal = $d['page'] ?? 'itinerary';
        $metaJson = json_encode($d);
        $stmt2 = $db->prepare('INSERT INTO activity_logs (user_email, user_name, anonymous, page, action, meta) VALUES (?, ?, 1, ?, ?, ?)');
        if ($stmt2) {
            $stmt2->bind_param('sssss', $userEmail, $userName, $pageVal, $eventAction, $metaJson);
            $stmt2->execute();
        }
        
        j(['success'=>true, 'message'=>'Event logged', 'action'=>$eventAction]);
        exit;
    }
    
    // Handle legacy analytics logging (page views, clicks, etc.)
    $sid = $d['session_id'] ?? uniqid('sess_');
    $type = $d['type'] ?? '';
    $ts = isset($d['ts']) ? date('Y-m-d H:i:s', intval($d['ts']/1000)) : date('Y-m-d H:i:s');
    $url = substr($d['url'] ?? '', 0, 255);
    $ua = substr($d['ua'] ?? '', 0, 512);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // helper anonymize IP (very small privacy-preserving truncation)
    function anonymize_ip($ip){
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }
        // IPv6: keep prefix
        return substr($ip,0,45);
    }

    $ip_prefix = anonymize_ip($ip);
    $city = $d['payload']['city'] ?? null;
    $country = $d['payload']['country'] ?? null;
    $device = $d['payload']['device_type'] ?? null;

    // Upsert session row (simple)
    $sidEsc = $db->real_escape_string($sid);
    $now = date('Y-m-d H:i:s');
    $db->query("INSERT INTO anonymous_sessions (session_id, ip_address, city, country, device_type, first_visit, last_visit) VALUES ('{$sidEsc}', '{$db->real_escape_string($ip_prefix)}', '{$db->real_escape_string($city)}', '{$db->real_escape_string($country)}', '{$db->real_escape_string($device)}', '{$now}', '{$now}') ON DUPLICATE KEY UPDATE last_visit=VALUES(last_visit), page_views=page_views+1");

    if ($type === 'page_view'){
        $title = substr($d['payload']['title'] ?? '', 0, 255);
        $dur = intval($d['payload']['duration_s'] ?? 0);
        $stmt = $db->prepare('INSERT INTO page_views (session_id, url, title, ts, duration_s) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssis', $sid, $url, $title, $ts, $dur);
        $stmt->execute();
        j(['success'=>true]);
    }

    if ($type === 'click'){
        $sel = substr($d['payload']['selector'] ?? '', 0, 1000);
        $stmt = $db->prepare('INSERT INTO click_events (session_id, ts, selector, url) VALUES (?,?,?,?)');
        $stmt->bind_param('ssss', $sid, $ts, $sel, $url);
        $stmt->execute();
        j(['success'=>true]);
    }

    // search event tracking removed (unused feature)

    if ($type === 'heartbeat' || $type === 'page_unload'){
        // update total_time_minutes if provided
        $added = intval($d['payload']['duration_s'] ?? 0);
        if ($added > 0){
            $mins = ceil($added/60);
            $db->query("UPDATE anonymous_sessions SET total_time_minutes = total_time_minutes + {$mins}, last_visit = NOW() WHERE session_id='{$sidEsc}'");
        }
        j(['success'=>true]);
    }

    // unknown type
    j(['success'=>false,'error'=>'unknown event type']);
}

// Itineraries: list, add, get, edit, delete
if ($action === 'list_itineraries'){
    $db = get_db();
    
    // Check if this is an admin request
    // Admin can retrieve all itineraries, otherwise only by email
    $is_admin = false;
    if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) $is_admin = true;
    
    // If email parameter provided, get that user's itineraries
    if (!empty($_GET['email'])){
        $email = $db->real_escape_string($_GET['email']);
        $res = $db->query("SELECT * FROM itineraries WHERE user_email='".$email."' ORDER BY created_at DESC");
    } else if ($is_admin) {
        // Admin user: return all itineraries
        $res = $db->query('SELECT * FROM itineraries ORDER BY created_at DESC LIMIT 500');
    } else {
        // Not admin and no email: return empty
        j(['itineraries'=>[]]);
    }
    
    if (!$res) {
        j(['itineraries'=>[], 'error'=>$db->error]);
        exit;
    }
    
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['itineraries'=>$rows]);
}

if ($action === 'add_itinerary'){
    $d = json_input();
    $db = get_db();
    // defensive logging/inspection when debugging
    error_log('add_itinerary payload: '.json_encode($d));

    $email = $d['email'] ?? '';
    $name = $d['name'] ?? '';
    $title = $d['title'] ?? '';
    $days = intval($d['days'] ?? 1);
    $dest = is_array($d['destinations']) ? json_encode($d['destinations']) : ($d['destinations'] ?? '');
    $anon = !empty($d['anonymous']) ? 1 : 0;

    // types: email s, name s, title s, days i, destinations s, anonymous i
    $stmt = $db->prepare('INSERT INTO itineraries (user_email, user_name, title, days, destinations, anonymous) VALUES (?, ?, ?, ?, ?, ?)');
    if($stmt){
      $stmt->bind_param('sssisi', $email, $name, $title, $days, $dest, $anon);
      if ($stmt->execute()) {
          $id = $db->insert_id;
          error_log('Itinerary saved successfully: id='.$id.', title='.$title);
          // echo payload back so caller can see what was interpreted
          j(['success'=>true, 'id'=>$id, 'received'=>$d]);
      } else {
          error_log('Itinerary INSERT failed: '.$stmt->error);
          j(['success'=>false, 'error'=>$stmt->error, 'received'=>$d]);
      }
    } else {
      error_log('Itinerary prepare failed: '.$db->error);
      j(['success'=>false, 'error'=>'prepare failed: '.$db->error, 'received'=>$d]);
    }
}

if ($action === 'get_itinerary'){
    $id = intval($_GET['id'] ?? 0);
    $db = get_db();
    $res = $db->query('SELECT * FROM itineraries WHERE id=' . $id . ' LIMIT 1');
    $row = $res->fetch_assoc();
    j(['itinerary'=>$row]);
}

// DEBUG ENDPOINT: Shows itinerary count and recent saves
if ($action === 'debug_itinerary_count'){
    $db = get_db();
    $res = $db->query('SELECT COUNT(*) AS total FROM itineraries');
    $r = $res->fetch_assoc();
    $total = $r['total'] ?? 0;
    
    $res = $db->query('SELECT id, title, user_name, user_email, days, created_at FROM itineraries ORDER BY created_at DESC LIMIT 10');
    $recent = [];
    while($row = $res->fetch_assoc()) $recent[] = $row;
    
    j(['total_count' => $total, 'recent_itineraries' => $recent]);
}

if ($action === 'edit_itinerary'){
    $d = json_input();
    $db = get_db();
    // Allow edit if admin or owner by email
    if (empty($_SESSION['is_admin'])){
        if (empty($d['email'])) j(['success'=>false,'error'=>'unauthorized']);
        $check = $db->query("SELECT id FROM itineraries WHERE id=".intval($d['id'])." AND user_email='".$db->real_escape_string($d['email'])."' LIMIT 1");
        if (!$check || $check->num_rows === 0) j(['success'=>false,'error'=>'unauthorized']);
    }
    $title = $d['title'] ?? '';
    $name = $d['name'] ?? '';
    $email = $d['email'] ?? '';
    $days = intval($d['days'] ?? 1);
    $dest = is_array($d['destinations']) ? json_encode($d['destinations']) : ($d['destinations'] ?? '');
    $anon = !empty($d['anonymous']) ? 1 : 0;
    $id = intval($d['id'] ?? 0);
    $stmt = $db->prepare('UPDATE itineraries SET title=?,user_name=?,user_email=?,days=?,destinations=?,anonymous=? WHERE id=?');
    $stmt->bind_param('sssissi', $title, $name, $email, $days, $dest, $anon, $id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_itinerary'){
    $d = json_input();
    $id = intval($d['id'] ?? 0);
    $db = get_db();
    if (empty($_SESSION['is_admin'])){
        if (empty($d['email'])) j(['success'=>false,'error'=>'unauthorized']);
        $check = $db->query("SELECT id FROM itineraries WHERE id=".$id." AND user_email='".$db->real_escape_string($d['email'])."' LIMIT 1");
        if (!$check || $check->num_rows === 0) j(['success'=>false,'error'=>'unauthorized']);
    }
    $stmt = $db->prepare('DELETE FROM itineraries WHERE id=?');
    $stmt->bind_param('i',$id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Admin analytics summary
if ($action === 'analytics_summary'){
    require_admin();
    $db = get_db();
    $out = [];
    
    // Auto-initialize critical tables if they don't exist
    $tables_to_check = ['destinations', 'local_experiences', 'shops'];
    foreach ($tables_to_check as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if (!$check || $check->num_rows == 0) {
            // Create the table
            if ($table === 'destinations') {
                $db->query("CREATE TABLE IF NOT EXISTS destinations (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT, location VARCHAR(255), image VARCHAR(255) DEFAULT NULL, category_id INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            } elseif ($table === 'local_experiences') {
                $db->query("CREATE TABLE IF NOT EXISTS local_experiences (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, description TEXT, type VARCHAR(100), price DECIMAL(10, 2), duration VARCHAR(100), image VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            } elseif ($table === 'shops') {
                $db->query("CREATE TABLE IF NOT EXISTS shops (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT, address VARCHAR(255), contact VARCHAR(255), owner_name VARCHAR(255), image VARCHAR(255) DEFAULT NULL, clicks INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            }
        }
    }
    
    // Count functions with error handling
    $res = $db->query('SELECT COUNT(*) AS c FROM users'); 
    $out['total_users'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    
    $res = $db->query('SELECT COUNT(*) AS c FROM events'); 
    $out['total_events'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    
    $res = $db->query('SELECT COUNT(*) AS c FROM destinations'); 
    $out['total_destinations'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    
    $res = $db->query('SELECT COUNT(*) AS c FROM local_experiences'); 
    $out['total_experiences'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    
    $res = $db->query('SELECT COUNT(*) AS c FROM itineraries'); 
    $out['total_itineraries'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    
    $res = $db->query('SELECT COUNT(*) AS c FROM feedback'); 
    $out['total_feedback'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    
    $res = $db->query('SELECT COUNT(*) AS c FROM shops'); 
    $out['total_shops'] = ($res && $r = $res->fetch_assoc()) ? $r['c'] : 0;
    // page views last 30 days (from page_views table)
    $res = $db->query("SELECT DATE(ts) AS d, COUNT(*) AS c FROM page_views WHERE ts >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(ts) ORDER BY DATE(ts)");
    $pv = []; while($r=$res->fetch_assoc()) $pv[]=$r; $out['pv_last_30'] = $pv;
    // guest logs last 30 days (from activity_logs table or logs table)
    $res = $db->query("SELECT DATE(created_at) AS d, COUNT(*) AS c FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
    $gl = []; while($r=$res->fetch_assoc()) $gl[]=$r; $out['guest_logs_by_date'] = $gl;
    // top pages (by views)
    $res = $db->query("SELECT url AS page, COUNT(*) AS c FROM page_views GROUP BY url ORDER BY c DESC LIMIT 15"); $tp=[]; while($r=$res->fetch_assoc()) $tp[]=$r; $out['top_pages']=$tp;
    // peak hours
    $res = $db->query("SELECT HOUR(ts) AS hour, COUNT(*) AS hits FROM page_views GROUP BY HOUR(ts) ORDER BY hour ASC"); $ph=[]; while($r=$res->fetch_assoc()) $ph[]=$r; $out['peak_hours']=$ph;
    // top searches removed (unused feature)
    // city breakdown (from anonymous_sessions)
    $res = $db->query("SELECT COALESCE(city,'Unknown') AS city, COUNT(*) AS visitors FROM anonymous_sessions GROUP BY city ORDER BY visitors DESC LIMIT 20"); $cs=[]; while($r=$res->fetch_assoc()) $cs[]=$r; $out['city_stats']=$cs;
    j($out);
}

// List attendance logs (admin)
if ($action === 'list_attendance'){
    require_admin();
    $db = get_db();
    $res = $db->query('SELECT * FROM attendance_history ORDER BY date DESC LIMIT 500');
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['success'=>true,'attendance'=>$rows]);
}

// Edit attendance (admin)
if ($action === 'edit_attendance'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $id = intval($d['id'] ?? 0);
    if ($id <= 0) j(['success'=>false,'error'=>'invalid id']);
    $event_id = intval($d['event_id'] ?? 0);
    $location = $d['location'] ?? '';
    $date = $d['date'] ?? '';
    $attendance = intval($d['attendance'] ?? 0);
    $stmt = $db->prepare('UPDATE attendance_history SET event_id=?, location=?, date=?, attendance=? WHERE id=?');
    $stmt->bind_param('issii', $event_id, $location, $date, $attendance, $id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Delete attendance (admin)
if ($action === 'delete_attendance'){
    require_admin();
    $d = json_input();
    $id = intval($d['id'] ?? 0);
    if ($id <= 0) j(['success'=>false,'error'=>'invalid id']);
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM attendance_history WHERE id=?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// Track anonymous visitor session
if ($action === 'track_anonymous'){
    $d = json_input();
    $db = get_db();
    $session_id = $d['session_id'] ?? uniqid('sess_');
    $ip = $_SERVER['REMOTE_ADDR'];
    $city = $d['city'] ?? 'Unknown';
    $country = $d['country'] ?? 'Unknown';
    $device = $d['device_type'] ?? 'desktop';
    
    $check = $db->query("SELECT id FROM anonymous_sessions WHERE session_id='".$db->real_escape_string($session_id)."' LIMIT 1");
    if ($check && $check->num_rows > 0){
        $db->query("UPDATE anonymous_sessions SET last_visit=NOW(), page_views=page_views+1 WHERE session_id='".$db->real_escape_string($session_id)."'");
    } else {
        $stmt = $db->prepare("INSERT INTO anonymous_sessions (session_id, ip_address, city, country, device_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $session_id, $ip, $city, $country, $device);
        $stmt->execute();
    }
    j(['success'=>true, 'session_id'=>$session_id]);
}

// Track place views/clicks (anonymous analytics)
if ($action === 'track_place'){
    $d = json_input();
    $db = get_db();
    $place_name = $d['place_name'] ?? null;
    $action_type = $d['type'] ?? 'view'; // view or click
    
    if (!$place_name) j(['error'=>'missing place_name']);
    
    $check = $db->query("SELECT id FROM place_analytics WHERE place_name='".$db->real_escape_string($place_name)."' LIMIT 1");
    if ($check && $check->num_rows > 0){
        if ($action_type === 'click') $db->query("UPDATE place_analytics SET click_count=click_count+1, last_viewed=NOW() WHERE place_name='".$db->real_escape_string($place_name)."'");
        else $db->query("UPDATE place_analytics SET view_count=view_count+1, last_viewed=NOW() WHERE place_name='".$db->real_escape_string($place_name)."'");
    } else {
        $vc = $action_type === 'click' ? 0 : 1;
        $cc = $action_type === 'click' ? 1 : 0;
        $stmt = $db->prepare("INSERT INTO place_analytics (place_name, view_count, click_count) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $place_name, $vc, $cc);
        $stmt->execute();
    }
    j(['success'=>true]);
}

// Track shop interactions (anonymous analytics)
if ($action === 'track_shop'){
    $d = json_input();
    $db = get_db();
    $shop_id = intval($d['shop_id'] ?? 0);
    $shop_name = $d['shop_name'] ?? null;
    $action_type = $d['type'] ?? 'view';
    
    if (!$shop_id && !$shop_name) j(['error'=>'missing shop_id or shop_name']);
    
    $check = $db->query("SELECT id FROM shop_interactions WHERE shop_id=".$shop_id." OR shop_name='".$db->real_escape_string($shop_name)."' LIMIT 1");
    if ($check && $check->num_rows > 0){
        if ($action_type === 'click') $db->query("UPDATE shop_interactions SET click_count=click_count+1, last_viewed=NOW() WHERE shop_id=".$shop_id);
        else $db->query("UPDATE shop_interactions SET view_count=view_count+1, last_viewed=NOW() WHERE shop_id=".$shop_id);
    } else {
        $vc = $action_type === 'click' ? 0 : 1;
        $cc = $action_type === 'click' ? 1 : 0;
        $stmt = $db->prepare("INSERT INTO shop_interactions (shop_id, shop_name, view_count, click_count) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isii', $shop_id, $shop_name, $vc, $cc);
        $stmt->execute();
    }
    j(['success'=>true]);
}

// Get most viewed places (admin)
if ($action === 'most_viewed_places'){
    require_admin();
    $db = get_db();
    $res = $db->query("SELECT place_name, view_count, click_count, last_viewed FROM place_analytics ORDER BY view_count DESC LIMIT 20");
    $places = []; while($r=$res->fetch_assoc()) $places[]=$r;
    j(['places'=>$places]);
}

// Get most clicked shops (admin)
if ($action === 'most_clicked_shops'){
    require_admin();
    $db = get_db();
    $res = $db->query("SELECT shop_id, shop_name, view_count, click_count, last_viewed FROM shop_interactions ORDER BY click_count DESC LIMIT 20");
    $shops = []; while($r=$res->fetch_assoc()) $shops[]=$r;
    j(['shops'=>$shops]);
}

// Admin: fetch recent guest-side event logs (for dashboard analytics)
if ($action === 'get_guest_logs'){
    require_admin();
    $db = get_db();
    $limit = intval($_GET['limit'] ?? 200);
    if ($limit <= 0 || $limit > 2000) $limit = 200;
    $res = $db->query("SELECT id, event_action, event_data, timestamp, user_agent, ip_address, created_at FROM admin_event_logs ORDER BY created_at DESC LIMIT {$limit}");
    $rows = [];
    while($r = $res->fetch_assoc()){
        // decode event_data for convenience (if JSON)
        $r['event_parsed'] = json_decode($r['event_data'], true);
        $rows[] = $r;
    }
    j(['success'=>true, 'logs'=>$rows]);
}

// Admin: fetch saved guest itineraries (for review)
if ($action === 'get_guest_itineraries'){
    require_admin();
    $db = get_db();
    $limit = intval($_GET['limit'] ?? 200);
    if ($limit <= 0 || $limit > 2000) $limit = 200;
    $res = $db->query("SELECT id, user_email, user_name, title, days, destinations, anonymous, created_at FROM itineraries ORDER BY created_at DESC LIMIT {$limit}");
    $rows = [];
    while($r = $res->fetch_assoc()){
        // parse destinations JSON if present
        if (!empty($r['destinations']) && is_string($r['destinations'])){
            $try = json_decode($r['destinations'], true);
            if ($try !== null) $r['destinations_parsed'] = $try;
        }
        $rows[] = $r;
    }
    j(['success'=>true, 'itineraries'=>$rows]);
}

// Admin: fetch guest activity logs (from activity_logs)
if ($action === 'get_guest_activity_logs'){
    require_admin();
    $db = get_db();
    $limit = intval($_GET['limit'] ?? 500);
    if ($limit <= 0 || $limit > 5000) $limit = 500;
    $res = $db->query("SELECT id, user_email, user_name, anonymous, page, action, meta, created_at FROM activity_logs ORDER BY created_at DESC LIMIT {$limit}");
    $rows = [];
    while($r = $res->fetch_assoc()){
        if (!empty($r['meta']) && is_string($r['meta'])){
            $m = json_decode($r['meta'], true);
            if ($m !== null) $r['meta_parsed'] = $m;
        }
        $rows[] = $r;
    }
    j(['success'=>true, 'activity_logs'=>$rows]);
}

// Admin: get experiences statistics (top experiences, counts)
if ($action === 'get_experiences_stats'){
    require_admin();
    $db = get_db();
    // total experiences
    $res = $db->query('SELECT COUNT(*) AS c FROM local_experiences'); $total = $res->fetch_assoc()['c'];
    // top experiences by implicit popularity (if activity stored in place_analytics)
    $res = $db->query("SELECT le.id, le.title, COALESCE(pa.view_count,0) AS views, COALESCE(pa.click_count,0) AS clicks FROM local_experiences le LEFT JOIN place_analytics pa ON pa.place_name = le.title ORDER BY views DESC LIMIT 30");
    $top = []; while($r=$res->fetch_assoc()) $top[] = $r;
    j(['success'=>true, 'total_experiences'=>$total, 'top_experiences'=>$top]);
}

// Admin: event alerts - upcoming events and overcrowding risks
if ($action === 'get_event_alerts'){
    require_admin();
    $db = get_db();
    $daysAhead = intval($_GET['days'] ?? 7);
    if ($daysAhead <= 0) $daysAhead = 7;
    // upcoming events within next N days
    $stmt = $db->prepare("SELECT id, title, datetime, location, capacity FROM events WHERE datetime >= NOW() AND datetime <= DATE_ADD(NOW(), INTERVAL ? DAY) ORDER BY datetime ASC");
    $stmt->bind_param('i', $daysAhead);
    $stmt->execute();
    $res = $stmt->get_result(); $upcoming = [];
    while($r = $res->fetch_assoc()) $upcoming[] = $r;
    // overcrowding predictions with High risk (from event_predictions)
    $res2 = $db->query("SELECT event_id, event_name, predicted_visitors, predicted_garbage_kg, crowd_status, prediction_date FROM event_predictions WHERE crowd_status='High' OR phase='overcrowding_risk' ORDER BY prediction_date DESC LIMIT 100");
    $risks = []; while($r = $res2->fetch_assoc()) $risks[] = $r;
    j(['success'=>true, 'upcoming_events'=>$upcoming, 'overcrowding_risks'=>$risks]);
}

// Auto-generate predictions for upcoming events (AI-powered visitor forecast)
if ($action === 'predict_events'){
    require_admin();
    $db = get_db();
    
    // Helper: smart AI predictor based on event attributes
    function predictVisitors($event_id, $event_title, $capacity, $location, $db){
        $capacity = intval($capacity);
        if($capacity === 0) $capacity = 100; // default
        
        // Base score: 40-60% capacity fill with randomness
        $base_prediction = intval($capacity * (0.4 + (mt_rand(0, 20) / 100)));
        
        // Keyword boost: popular festivals/events get higher prediction
        $title_lower = strtolower($event_title . ' ' . $location);
        $popular_keywords = ['mayon', 'festival', 'volcano', 'celebration', 'cultural', 'ibalong', 'tourist', 'international', 'eco', 'adventure', 'tour', 'heritage'];
        $keyword_boost = 0;
        foreach($popular_keywords as $kw) if(strpos($title_lower, $kw) !== false) $keyword_boost += 0.08;
        
        // Historical data: boost from similar past events in same location
        $hist_avg = 0;
        $location_esc = $db->real_escape_string($location);
        $hist_res = $db->query("SELECT AVG(COALESCE(attendance, 0)) AS avg_att FROM attendance_history WHERE location='".$location_esc."' AND date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        if($hist_res && $row = $hist_res->fetch_assoc()) $hist_avg = intval($row['avg_att'] ?? 0);
        
        // Combine: 60% base prediction + 40% historical average + keyword boost applied
        $weighted = intval($base_prediction * (1 + $keyword_boost) * 0.6 + 0.4 * max($hist_avg, $base_prediction * 0.5));
        
        // Cap at capacity, min 10
        $final = min(max($weighted, 10), $capacity);
        return $final;
    }
    
    // Generate predictions for all events without recent predictions
    $res = $db->query("SELECT e.id, e.title, e.capacity, e.location FROM events e LEFT JOIN event_predictions p ON e.id=p.event_id AND p.prediction_date >= DATE_SUB(NOW(), INTERVAL 3 DAY) WHERE p.id IS NULL AND e.datetime > NOW() ORDER BY e.datetime ASC");
    
    $count = 0;
    while($ev = $res->fetch_assoc()){
        // server-side validation
        $ev_id = intval($ev['id']);
        $ev_title = trim($ev['title'] ?? '');
        $ev_capacity = intval($ev['capacity'] ?? 0);
        $ev_location = trim($ev['location'] ?? '');
        if ($ev_id <= 0 || $ev_title === '') continue;

        $pred = predictVisitors($ev_id, $ev_title, $ev_capacity, $ev_location, $db);
        $waste_kg = round($pred * 0.5, 2); // 0.5kg waste per person
        $trash_bags = ceil($waste_kg / 10);
        $capacity = intval($ev_capacity);
        $fill_rate = $capacity > 0 ? ($pred / $capacity) : 0;
        $crowd_level = $fill_rate > 0.85 ? 'High' : ($fill_rate > 0.6 ? 'Medium' : 'Low');

        $stmt = $db->prepare("INSERT INTO event_predictions (event_id, event_name, predicted_visitors, predicted_garbage_kg, crowd_status, prediction_date, phase) VALUES (?, ?, ?, ?, ?, CURDATE(), ?)");
        $phase = $fill_rate > 0.9 ? 'overcrowding_risk' : 'normal';
        $title = substr($ev_title, 0, 255);
        $stmt->bind_param('isidss', $ev_id, $title, $pred, $waste_kg, $crowd_level, $phase);
        if($stmt->execute()) $count++;
    }
    
    j(['success'=>true, 'predictions_generated'=>$count, 'message'=>"Generated $count event predictions"]);
}

// Train ML models (admin only) - logistic for overcrowding, linear for waste
if ($action === 'train_ml'){
    require_admin();
    $res = train_ml_models();
    j($res);
}

// Run external Python GBM trainer (LightGBM/XGBoost)
if ($action === 'train_gb_models'){
    require_admin();
    $py = escapeshellcmd(PHP_BINDIR ?? 'python3');
    $script = __DIR__ . '/ml/train_models.py';
    $cmd = "$py " . escapeshellarg($script) . " 2>&1";
    $out = [];
    exec($cmd, $out, $rc);
    $txt = implode("\n", $out);
    j(['success'=> $rc === 0, 'rc'=>$rc, 'output'=>$txt]);
}

// Predict using external GBM model
if ($action === 'predict_with_gb'){
    $d = json_input();
    $py = escapeshellcmd(PHP_BINDIR ?? 'python3');
    $script = __DIR__ . '/ml/predict_model.py';
    $input = escapeshellarg(json_encode($d));
    $cmd = "$py " . escapeshellarg($script) . " " . $input . " 2>&1";
    $out = [];
    exec($cmd, $out, $rc);
    $txt = implode("\n", $out);
    // Try to parse JSON output
    $j = json_decode($txt, true);
    if (json_last_error() === JSON_ERROR_NONE) j($j);
    else j(['success'=>false,'error'=>'predict_failed','output'=>$txt]);
}

// Get event predictions
if ($action === 'get_event_predictions'){
    require_admin();
    $db = get_db();
    $event_id = intval($_GET['event_id'] ?? 0);
    if ($event_id){
        $res = $db->query("SELECT * FROM event_predictions WHERE event_id=".$event_id." ORDER BY prediction_date DESC LIMIT 10");
    } else {
        $res = $db->query("SELECT * FROM event_predictions ORDER BY prediction_date DESC LIMIT 50");
    }
    $preds = []; while($r=$res->fetch_assoc()) $preds[]=$r;
    j(['success'=>true, 'predictions'=>$preds]);
}

// Get event alerts (DO/DO NOT, BRING/DO NOT BRING)
if ($action === 'get_event_alerts'){
    $event_id = intval($_GET['event_id'] ?? 0);
    $db = get_db();
    if ($event_id){
        $res = $db->query("SELECT * FROM event_alerts WHERE event_id=".$event_id." ORDER BY alert_type ASC");
    } else {
        $res = $db->query("SELECT * FROM event_alerts ORDER BY event_id, alert_type ASC");
    }
    $alerts = []; while($r=$res->fetch_assoc()) $alerts[]=$r;
    j(['alerts'=>$alerts]);
}

// List guest logs (admin)
if ($action === 'list_logs'){
    require_admin();
    $db = get_db();
    $res = $db->query('SELECT id, user_email, user_name, anonymous, page, action, meta, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 1000');
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['logs'=>$rows]);
}

// Add event alert (admin)
if ($action === 'add_event_alert'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO event_alerts (event_id, alert_type, content) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $d['event_id'], $d['alert_type'], $d['content']);
    if ($stmt->execute()) j(['success'=>true, 'id'=>$db->insert_id]);
    j(['success'=>false, 'error'=>$stmt->error]);
}

// Delete event alert (admin)
if ($action === 'delete_alert'){
    require_admin();
    $d = json_input();
    $id = intval($d['id'] ?? 0);
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM event_alerts WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false, 'error'=>$stmt->error]);
}

// Edit event alert (admin)
if ($action === 'edit_alert'){
    require_admin();
    $d = json_input();
    $id = intval($d['id'] ?? 0);
    $event_id = intval($d['event_id'] ?? 0);
    $alert_type = $d['alert_type'] ?? '';
    $content = $d['content'] ?? '';
    $db = get_db();
    $stmt = $db->prepare("UPDATE event_alerts SET event_id=?, alert_type=?, content=? WHERE id=?");
    $stmt->bind_param('issi', $event_id, $alert_type, $content, $id);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false, 'error'=>$stmt->error]);
}

// Get visitor geolocation statistics for events
if ($action === 'get_visitor_geolocation'){
    $db = get_db();
    $event_id = intval($_GET['event_id'] ?? 0);
    
    // Get event location
    $event_res = $db->query("SELECT location FROM events WHERE id = $event_id");
    $event = $event_res->fetch_assoc();
    if (!$event) j(['success'=>false, 'error'=>'Event not found']);
    
    $event_location = $event['location'] ?? 'Legazpi City';
    
    // Determine local cities (Legazpi area)
    $local_cities = ['legazpi', 'albay', 'ligao', 'tiwi', 'polangui', 'guinobatan'];
    
    // Query geolocation data from anonymous sessions
    // This counts unique cities and determines local vs non-local
    $geo_res = $db->query("
        SELECT 
            city,
            COUNT(*) as visitor_count,
            ROUND(AVG(page_views)) as avg_pages,
            MAX(last_visit) as last_visitor
        FROM anonymous_sessions
        WHERE city IS NOT NULL AND city != ''
        GROUP BY city
        ORDER BY visitor_count DESC
    ");
    
    $local_count = 0;
    $non_local_count = 0;
    $local_cities_list = [];
    $cities_data = [];
    
    while($row = $geo_res->fetch_assoc()) {
        $city = strtolower($row['city']);
        $is_local = false;
        
        foreach($local_cities as $lc) {
            if (strpos($city, $lc) !== false) {
                $is_local = true;
                break;
            }
        }
        
        $cities_data[] = [
            'city' => $row['city'],
            'count' => intval($row['visitor_count']),
            'is_local' => $is_local,
            'avg_pages' => intval($row['avg_pages']),
            'last_visit' => $row['last_visitor']
        ];
        
        if ($is_local) {
            $local_count += intval($row['visitor_count']);
            $local_cities_list[] = $row['city'];
        } else {
            $non_local_count += intval($row['visitor_count']);
        }
    }
    
    $total = $local_count + $non_local_count;
    $local_percentage = $total > 0 ? round(($local_count / $total) * 100) : 50;
    
    j([
        'success' => true,
        'event_location' => $event_location,
        'local_visitors' => $local_count,
        'non_local_visitors' => $non_local_count,
        'total_visitors' => $total,
        'local_percentage' => $local_percentage,
        'non_local_percentage' => (100 - $local_percentage),
        'local_cities_sampled' => $local_cities_list,
        'cities_detail' => $cities_data
    ]);
}

// Get event parade details dynamically (all information in one call)
if ($action === 'get_event_parade_details') {
    $event_id = intval($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
    if ($event_id <= 0) j(['success' => false, 'error' => 'Invalid event_id']);
    
    $db = get_db();
    
    // Get event with prediction data
    $event_res = $db->query("SELECT e.*, p.attendance, p.waste_prediction, p.overcrowding_probability FROM events e LEFT JOIN ml_predictions p ON e.id=p.event_id WHERE e.id=$event_id ORDER BY p.created_at DESC LIMIT 1");
    if (!$event_res) j(['success' => false, 'error' => 'Query failed']);
    
    $event = $event_res->fetch_assoc();
    if (!$event) j(['success' => false, 'error' => 'Event not found']);
    
    // Extract location details
    $start_name = 'Legazpi City Start';
    $end_name = 'Legazpi Port District';
    
    // Try to get location-based names (can be customized)
    if (!empty($event['location'])) {
        $start_name = ucwords($event['location']) . ' Start';
    }
    
    $start_lat = $event['start_lat'] ?? 13.112621547653507;
    $start_lng = $event['start_lng'] ?? 123.75351323035586;
    $end_lat = $event['end_lat'] ?? 13.134327078936007;
    $end_lng = $event['end_lng'] ?? 123.76561383427422;
    
    // Calculate expected visitors (from prediction or default 70% capacity)
    $expected_visitors = intval($event['attendance'] ?? (intval($event['capacity'] ?? 100) * 0.7));
    $predicted_waste = round($event['waste_prediction'] ?? 100, 2);
    $overcrowding_prob = floatval($event['overcrowding_probability'] ?? 0.5);
    
    // Calculate crowd percentage for traffic level classification (0-100)
    $capacity = intval($event['capacity'] ?? 100);
    $crowd_percentage = $capacity > 0 ? min(100, ($expected_visitors / $capacity) * 100) : 50;
    
    // Fetch routes if start and end coordinates are provided
    $routes = [];
    if ($start_lat && $start_lng && $end_lat && $end_lng) {
        $routes = predictOptimalRoutes($crowd_percentage, $event, $start_lat, $start_lng, $end_lat, $end_lng);
    }
    
    j([
        'success' => true,
        'event_id' => $event_id,
        'event_title' => $event['title'],
        'event_datetime' => $event['datetime'],
        'event_location' => $event['location'],
        'event_capacity' => intval($event['capacity'] ?? 0),
        'event_description' => $event['description'],
        'event_image' => $event['image'],
        'start_point' => [
            'name' => $start_name,
            'latitude' => floatval($start_lat),
            'longitude' => floatval($start_lng)
        ],
        'end_point' => [
            'name' => $end_name,
            'latitude' => floatval($end_lat),
            'longitude' => floatval($end_lng)
        ],
        'predictions' => [
            'expected_visitors' => $expected_visitors,
            'predicted_waste_kg' => $predicted_waste,
            'overcrowding_probability' => round($overcrowding_prob, 2),
            'risk_level' => $overcrowding_prob > 0.70 ? 'HIGH' : ($overcrowding_prob > 0.45 ? 'MEDIUM' : 'LOW')
        ],
        'routes' => $routes
    ]);
}

// Get optimal route suggestions for events (LEGAZPI CITY ONLY)
if ($action === 'suggest_optimal_route') {
    $event_id = intval($_GET['event_id'] ?? 0);
    $crowd_level = intval($_GET['crowd_level'] ?? 1000);
    $db = get_db();
    
    // Get event details (note: lat/lng columns don't exist yet, using defaults)
    $event_res = $db->query("SELECT id, title, location FROM events WHERE id = $event_id");
    if (!$event_res) {
        j(['success' => false, 'error' => 'Database query failed: ' . $db->error]);
    }
    
    $event = $event_res->fetch_assoc();
    
    if (!$event) {
        j(['success' => false, 'error' => 'Event not found']);
    }
    
    // Determine start/end points for routing. Prefer explicit start/end coordinates
    // from the event record (start_lat/start_lng, end_lat/end_lng) or from GET parameters.
    $event_start_lat = $event['start_lat'] ?? ($event['lat'] ?? null);
    $event_start_lng = $event['start_lng'] ?? ($event['lng'] ?? null);
    $event_end_lat = $event['end_lat'] ?? null;
    $event_end_lng = $event['end_lng'] ?? null;

    if (isset($_GET['start_lat']) && isset($_GET['start_lng'])) {
        $event_start_lat = floatval($_GET['start_lat']);
        $event_start_lng = floatval($_GET['start_lng']);
    }
    if (isset($_GET['end_lat']) && isset($_GET['end_lng'])) {
        $event_end_lat = floatval($_GET['end_lat']);
        $event_end_lng = floatval($_GET['end_lng']);
    }

    // Fallbacks for Legazpi City if not provided
    if (!$event_start_lat || !$event_start_lng) {
        $event_start_lat = 13.112621547653507;
        $event_start_lng = 123.75351323035586;
    }
    if (!$event_end_lat || !$event_end_lng) {
        $event_end_lat = 13.134327078936007;
        $event_end_lng = 123.76561383427422;
    }

    // Only suggest routes for Legazpi City events (enforced by location field)
    if (!$event['location'] || stripos($event['location'], 'legazpi') === false) {
        j(['success' => false, 'error' => 'Route optimization only available for Legazpi City events']);
    }

    // ML-powered route optimization based on crowd level and endpoints
    $routes = predictOptimalRoutes($crowd_level, $event, $event_start_lat, $event_start_lng, $event_end_lat, $event_end_lng);
    
    j([
        'success' => true,
        'event_id' => $event_id,
        'event' => $event,
        'routes' => $routes
    ]);
}

// ML function: Predict optimal routes based on crowd level and provided endpoints
function predictOptimalRoutes($crowdLevel, $event, $startLat = null, $startLng = null, $endLat = null, $endLng = null) {
    // Helper: call OSRM for a set of lng,lat points and return geometry/distance/duration
    $callOsrm = function($pts) {
        $pairs = array_map(function($p){ return $p[0].",".$p[1]; }, $pts);
        $coordStr = implode(";", $pairs);
        $url = "https://router.project-osrm.org/route/v1/driving/".$coordStr."?overview=full&geometries=geojson&steps=false";
        $opts = ["http"=>["method"=>"GET","timeout"=>8]];
        $ctx = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) return null;
        $j = json_decode($raw, true);
        if (!$j || empty($j['routes'])) return null;
        $r = $j['routes'][0];
        return ['coords'=>$r['geometry']['coordinates'],'distance_m'=>$r['distance'],'duration_s'=>$r['duration']];
    };

    // Fallback interpolator (lng,lat pairs)
    $interpolate = function($aLat, $aLng, $bLat, $bLng, $steps) {
        $pts = [];
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $lat = $aLat + ($bLat - $aLat) * $t;
            $lng = $aLng + ($bLng - $aLng) * $t;
            $pts[] = [$lng, $lat];
        }
        return $pts;
    };

    $sLat = floatval($startLat ?: 13.112621547653507);
    $sLng = floatval($startLng ?: 123.75351323035586);
    $eLat = floatval($endLat ?: 13.134327078936007);
    $eLng = floatval($endLng ?: 123.76561383427422);

    // define route variants with optional via points (lng,lat)
    $variants = [];
    $variants[] = ['name'=>'🎊 Official Route (Direct)','via'=>[[$sLng,$sLat],[$eLng,$eLat]],'duration_est'=>15,'trafficMultiplier'=>1.2,'crowdTolerance'=>[0,20000],'reason'=>'Direct route between endpoints.'];
    $midInlandLat = ($sLat+$eLat)/2 - 0.002; $midInlandLng = ($sLng+$eLng)/2 + 0.015;
    $variants[] = ['name'=>'🛣️ Inland Bypass','via'=>[[$sLng,$sLat],[$midInlandLng,$midInlandLat],[$eLng,$eLat]],'duration_est'=>20,'trafficMultiplier'=>1.1,'crowdTolerance'=>[0,25000],'reason'=>'Bypass congested corridors.'];
    $midCoastLat = ($sLat+$eLat)/2 + 0.002; $midCoastLng = ($sLng+$eLng)/2 - 0.012;
    $variants[] = ['name'=>'🌊 Coastal Alternate','via'=>[[$sLng,$sLat],[$midCoastLng,$midCoastLat],[$eLng,$eLat]],'duration_est'=>22,'trafficMultiplier'=>1.4,'crowdTolerance'=>[0,15000],'reason'=>'Scenic coastal alternative.'];

    $routes = [];
    foreach ($variants as $v) {
        $os = $callOsrm($v['via']);
        if ($os) {
            $dist_km = round($os['distance_m']/1000,2);
            $dur_min = round($os['duration_s']/60,1);
            $coords = $os['coords'];
        } else {
            // fallback to interpolation between endpoints
            $coords = $interpolate($v['via'][0][1], $v['via'][0][0], end($v['via'])[1], end($v['via'])[0], 6);
            $dist_km = round(hypot(($eLat - $sLat) * 111, ($eLng - $sLng) * 111), 2);
            $dur_min = $v['duration_est'];
        }
        
        // Convert coordinates from [lng,lat] to [lat,lng] for Leaflet/frontend
        $coords_latlong = array_map(function($c) { return [$c[1], $c[0]]; }, $coords);

        // Classify traffic level based on duration and predicted crowd
        // 0 = green (no traffic): duration < 18 mins AND crowd <= 50%
        // 1 = orange (moderate): duration 18-25 OR crowd 50-75%
        // 2 = red (heavy): duration > 25 OR crowd > 75%
        $trafficLevel = 0;
        if ($dur_min > 25 || $crowdLevel > 75) {
            $trafficLevel = 2;
        } elseif ($dur_min > 18 || $crowdLevel > 50) {
            $trafficLevel = 1;
        }

        // include GeoJSON LineString for easier frontend rendering (coordinates in [lat,lng] format)
        $routes[] = [
            'name'=>$v['name'],
            'coordinates'=>$coords_latlong,
            'geojson' => ['type'=>'Feature','geometry'=>['type'=>'LineString','coordinates'=>$coords],'properties'=>['name'=>$v['name']]],
            'distance'=>$dist_km,
            'duration'=>$dur_min,
            'traffic_level'=>$trafficLevel,
            'crowd_percentage'=>$crowdLevel,
            'reason'=>$v['reason'],
            'trafficMultiplier'=>$v['trafficMultiplier'],
            'crowdTolerance'=>$v['crowdTolerance'],
            'eventType'=>'route'
        ];
    }

    // ML ranking heuristic
    usort($routes, function($a, $b) use ($crowdLevel) {
        $ea = $a['duration'] * $a['trafficMultiplier'];
        $eb = $b['duration'] * $b['trafficMultiplier'];
        if ($crowdLevel > $a['crowdTolerance'][1]) $ea *= 2;
        if ($crowdLevel > $b['crowdTolerance'][1]) $eb *= 2;
        return $ea <=> $eb;
    });

    return array_slice($routes, 0, 3);
}

// Get admin profile image
if ($action === 'get_admin_profile'){
    $db = get_db();
    $res = $db->query("SELECT value FROM admin_settings WHERE key_name='admin_profile_image' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()){
        j(['success'=>true, 'profile_image'=>$row['value']]);
    }
    j(['success'=>true, 'profile_image'=>null]);
}

// Update shop image
if ($action === 'update_shop_image'){
    require_admin();
    $d = json_input();
    $db = get_db();
    if (!isset($d['shop_id']) || !isset($d['image_path'])){
        j(['success'=>false, 'error'=>'shop_id and image_path required']);
    }
    $stmt = $db->prepare('UPDATE shops SET image=? WHERE id=?');
    $stmt->bind_param('si', $d['image_path'], $d['shop_id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false, 'error'=>$stmt->error]);
}

// Update product image
if ($action === 'update_product_image'){
    require_admin();
    $d = json_input();
    $db = get_db();
    if (!isset($d['product_id']) || !isset($d['image_path'])){
        j(['success'=>false, 'error'=>'product_id and image_path required']);
    }
    $stmt = $db->prepare('UPDATE products SET image=? WHERE id=?');
    $stmt->bind_param('si', $d['image_path'], $d['product_id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false, 'error'=>$stmt->error]);
}

// ===== DESTINATIONS =====
if ($action === 'list_destinations'){
    $db = get_db();
    $res = $db->query('SELECT * FROM destinations ORDER BY created_at DESC');
    $rows = [];
    while($r = $res->fetch_assoc()) {
        // Normalize image paths for frontend access
        if (!empty($r['image'])) {
            $r['image'] = normalize_image_path($r['image']);
        }
        $rows[] = $r;
    }
    j(['destinations'=>$rows]);
}

if ($action === 'list_destination_categories'){
    $db = get_db();
    if (!$db) j(['categories'=>[], 'error'=>'Database connection failed']);
    $res = @$db->query('SELECT * FROM destination_categories ORDER BY name');
    if (!$res) j(['categories'=>[]]);
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['categories'=>$rows]);
}

if ($action === 'create_destination_category'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO destination_categories (name, description) VALUES (?, ?)');
    $stmt->bind_param('ss',$d['name'],$d['description']);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_destination_category'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('UPDATE destination_categories SET name=?, description=? WHERE id=?');
    $stmt->bind_param('ssi',$d['name'],$d['description'],$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_destination_category'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM destination_categories WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'create_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $category_id = !empty($d['category_id']) ? intval($d['category_id']) : null;
    $stmt = $db->prepare('INSERT INTO destinations (name,description,location,image,category_id) VALUES (?,?,?,?,?)');
    $stmt->bind_param('ssssi',$d['name'],$d['description'],$d['location'],$image,$category_id);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $category_id = !empty($d['category_id']) ? intval($d['category_id']) : null;
    if ($image) {
        $stmt = $db->prepare('UPDATE destinations SET name=?, description=?, location=?, image=?, category_id=? WHERE id=?');
        $stmt->bind_param('ssssii',$d['name'],$d['description'],$d['location'],$image,$category_id,$d['id']);
    } else {
        $stmt = $db->prepare('UPDATE destinations SET name=?, description=?, location=?, category_id=? WHERE id=?');
        $stmt->bind_param('sssii',$d['name'],$d['description'],$d['location'],$category_id,$d['id']);
    }
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM destinations WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// ===== LOCAL EXPERIENCES =====
if ($action === 'list_experiences'){
    $db = get_db();
    $res = $db->query('SELECT * FROM local_experiences ORDER BY created_at DESC');
    $rows = [];
    while($r = $res->fetch_assoc()) {
        // Normalize image paths for frontend access
        if (!empty($r['image'])) {
            $r['image'] = normalize_image_path($r['image']);
        }
        $rows[] = $r;
    }
    j(['experiences'=>$rows]);
}

if ($action === 'create_experience'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $stmt = $db->prepare('INSERT INTO local_experiences (title,description,type,price,duration,image) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('sssdss',$d['title'],$d['description'],$d['type'],$d['price'],$d['duration'],$image);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_experience'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    if ($image) {
        $stmt = $db->prepare('UPDATE local_experiences SET title=?, description=?, type=?, price=?, duration=?, image=? WHERE id=?');
        $stmt->bind_param('sssdsi',$d['title'],$d['description'],$d['type'],$d['price'],$d['duration'],$image,$d['id']);
    } else {
        $stmt = $db->prepare('UPDATE local_experiences SET title=?, description=?, type=?, price=?, duration=? WHERE id=?');
        $stmt->bind_param('sssdsi',$d['title'],$d['description'],$d['type'],$d['price'],$d['duration'],$d['id']);
    }
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_experience'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM local_experiences WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// ===== FESTIVALS & EVENTS =====
if ($action === 'list_festivals'){
    $db = get_db();
    $res = $db->query('SELECT * FROM festivals_events ORDER BY date_start DESC');
    $rows = [];
    while($r = $res->fetch_assoc()) {
        // Normalize image paths for frontend access
        if (!empty($r['image'])) {
            $r['image'] = normalize_image_path($r['image']);
        }
        $rows[] = $r;
    }
    j(['festivals'=>$rows]);
}

if ($action === 'create_festival'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $stmt = $db->prepare('INSERT INTO festivals_events (name,description,date_start,date_end,location,image) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('ssssss',$d['name'],$d['description'],$d['date_start'],$d['date_end'],$d['location'],$image);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_festival'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    if ($image) {
        $stmt = $db->prepare('UPDATE festivals_events SET name=?, description=?, date_start=?, date_end=?, location=?, image=? WHERE id=?');
        $stmt->bind_param('sssssi',$d['name'],$d['description'],$d['date_start'],$d['date_end'],$d['location'],$image,$d['id']);
    } else {
        $stmt = $db->prepare('UPDATE festivals_events SET name=?, description=?, date_start=?, date_end=?, location=? WHERE id=?');
        $stmt->bind_param('sssssi',$d['name'],$d['description'],$d['date_start'],$d['date_end'],$d['location'],$d['id']);
    }
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'delete_festival'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM festivals_events WHERE id=?');
    $stmt->bind_param('i',$d['id']);
    if ($stmt->execute()) j(['success'=>true]);
    j(['success'=>false,'error'=>$stmt->error]);
}

// ===== ITINERARY MANAGEMENT (ADMIN ONLY) =====

// Add custom itinerary destination
if ($action === 'add_itinerary_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    
    $name = $d['name'] ?? '';
    $category = $d['category'] ?? '';
    $latitude = floatval($d['latitude'] ?? 0);
    $longitude = floatval($d['longitude'] ?? 0);
    $description = $d['description'] ?? '';
    $activities = $d['activities'] ?? '';
    $image = $d['image'] ?? null;
    
    // Validate coordinates are in Legazpi
    if (!is_in_legazpi($latitude, $longitude)) {
        j(['success'=>false, 'error'=>'Coordinates must be within Legazpi City']);
        exit;
    }
    
    $stmt = $db->prepare('INSERT INTO itinerary_destinations (name, category, latitude, longitude, description, activities, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssddsss', $name, $category, $latitude, $longitude, $description, $activities, $image);
    
    if ($stmt->execute()) {
        j(['success'=>true, 'id'=>$db->insert_id]);
    } else {
        j(['success'=>false, 'error'=>$stmt->error]);
    }
}

// List custom itinerary destinations
if ($action === 'list_itinerary_destinations'){
    $db = get_db();
    $res = $db->query("SELECT * FROM itinerary_destinations ORDER BY created_at DESC");
    $rows = [];
    while($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    j(['success'=>true, 'destinations'=>$rows]);
}

// Delete custom itinerary destination
if ($action === 'delete_itinerary_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $id = intval($d['id'] ?? 0);
    
    $stmt = $db->prepare('DELETE FROM itinerary_destinations WHERE id=?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        j(['success'=>true]);
    } else {
        j(['success'=>false, 'error'=>$stmt->error]);
    }
}

// Update custom itinerary destination
if ($action === 'edit_itinerary_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    
    $id = intval($d['id'] ?? 0);
    $name = $d['name'] ?? '';
    $category = $d['category'] ?? '';
    $latitude = floatval($d['latitude'] ?? 0);
    $longitude = floatval($d['longitude'] ?? 0);
    $description = $d['description'] ?? '';
    $activities = $d['activities'] ?? '';
    $image = $d['image'] ?? null;
    
    // Validate coordinates are in Legazpi
    if (!is_in_legazpi($latitude, $longitude)) {
        j(['success'=>false, 'error'=>'Coordinates must be within Legazpi City']);
        exit;
    }
    
    $stmt = $db->prepare('UPDATE itinerary_destinations SET name=?, category=?, latitude=?, longitude=?, description=?, activities=?, image=? WHERE id=?');
    $stmt->bind_param('ssddssi', $name, $category, $latitude, $longitude, $description, $activities, $image, $id);
    
    if ($stmt->execute()) {
        j(['success'=>true]);
    } else {
        j(['success'=>false, 'error'=>$stmt->error]);
    }
}

// Add custom itinerary hotel
if ($action === 'add_itinerary_hotel'){
    require_admin();
    $d = json_input();
    $db = get_db();
    
    $name = $d['name'] ?? '';
    $category = $d['category'] ?? '';
    $latitude = floatval($d['latitude'] ?? 0);
    $longitude = floatval($d['longitude'] ?? 0);
    $rating = floatval($d['rating'] ?? 4.0);
    $rate_per_night = intval($d['ratePerNight'] ?? 0);
    $phone = $d['phone'] ?? '';
    $address = $d['address'] ?? '';
    $description = $d['description'] ?? '';
    $features = $d['features'] ?? '';
    $image = $d['image'] ?? null;
    
    // Validate coordinates are in Legazpi
    if (!is_in_legazpi($latitude, $longitude)) {
        j(['success'=>false, 'error'=>'Coordinates must be within Legazpi City']);
        exit;
    }
    
    $stmt = $db->prepare('INSERT INTO itinerary_hotels (name, category, latitude, longitude, rating, rate_per_night, phone, address, description, features, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssddidssss', $name, $category, $latitude, $longitude, $rating, $rate_per_night, $phone, $address, $description, $features, $image);
    
    if ($stmt->execute()) {
        j(['success'=>true, 'id'=>$db->insert_id]);
    } else {
        j(['success'=>false, 'error'=>$stmt->error]);
    }
}

// List custom itinerary hotels
if ($action === 'list_itinerary_hotels'){
    $db = get_db();
    $res = $db->query("SELECT * FROM itinerary_hotels ORDER BY created_at DESC");
    $rows = [];
    while($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    j(['success'=>true, 'hotels'=>$rows]);
}

// Delete custom itinerary hotel
if ($action === 'delete_itinerary_hotel'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $id = intval($d['id'] ?? 0);
    
    $stmt = $db->prepare('DELETE FROM itinerary_hotels WHERE id=?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        j(['success'=>true]);
    } else {
        j(['success'=>false, 'error'=>$stmt->error]);
    }
}

// Update custom itinerary hotel
if ($action === 'edit_itinerary_hotel'){
    require_admin();
    $d = json_input();
    $db = get_db();
    
    $id = intval($d['id'] ?? 0);
    $name = $d['name'] ?? '';
    $category = $d['category'] ?? '';
    $latitude = floatval($d['latitude'] ?? 0);
    $longitude = floatval($d['longitude'] ?? 0);
    $rating = floatval($d['rating'] ?? 4.0);
    $rate_per_night = intval($d['ratePerNight'] ?? 0);
    $phone = $d['phone'] ?? '';
    $address = $d['address'] ?? '';
    $description = $d['description'] ?? '';
    $features = $d['features'] ?? '';
    $image = $d['image'] ?? null;
    
    // Validate coordinates are in Legazpi
    if (!is_in_legazpi($latitude, $longitude)) {
        j(['success'=>false, 'error'=>'Coordinates must be within Legazpi City']);
        exit;
    }
    
    $stmt = $db->prepare('UPDATE itinerary_hotels SET name=?, category=?, latitude=?, longitude=?, rating=?, rate_per_night=?, phone=?, address=?, description=?, features=?, image=? WHERE id=?');
    $stmt->bind_param('ssddidsssssi', $name, $category, $latitude, $longitude, $rating, $rate_per_night, $phone, $address, $description, $features, $image, $id);
    
    if ($stmt->execute()) {
        j(['success'=>true]);
    } else {
        j(['success'=>false, 'error'=>$stmt->error]);
    }
}

// Save event prediction (admin)
if ($action === 'save_event_prediction'){
    // Note: require_admin() checks session, but we allow this to work if admin is logged in
    // We'll return error if not admin, but with helpful message
    if (empty($_SESSION['is_admin'])){
        j(['success'=>false, 'error'=>'Not authenticated - admin session required']);
        exit;
    }
    
    $d = json_input();
    $db = get_db();
    
    $event_id = intval($d['event_id'] ?? 0);
    $prediction = $d['prediction'] ?? null;
    
    if (!$event_id || !$prediction) {
        j(['success'=>false, 'error'=>'Missing event_id or prediction data']);
        exit;
    }
    
    // Ensure prediction column exists (auto-create if missing)
    $colCheck = $db->query("SHOW COLUMNS FROM events LIKE 'prediction'");
    if (!$colCheck || $colCheck->num_rows == 0) {
        $createCol = $db->query("ALTER TABLE events ADD COLUMN prediction LONGTEXT DEFAULT NULL");
        if (!$createCol) {
            j(['success'=>false, 'error'=>'Failed to create prediction column']);
            exit;
        }
    }
    
    // Encode prediction as JSON
    $prediction_json = json_encode($prediction);
    $stmt = $db->prepare('UPDATE events SET prediction=? WHERE id=?');
    if (!$stmt) {
        j(['success'=>false, 'error'=>'Database prepare failed: ' . $db->error]);
        exit;
    }
    
    $stmt->bind_param('si', $prediction_json, $event_id);
    
    if ($stmt->execute()) {
        j(['success'=>true, 'message'=>'Prediction saved', 'event_id'=>$event_id]);
    } else {
        j(['success'=>false, 'error'=>'Database update failed: ' . $stmt->error]);
    }
}

// Debug endpoint - check database contents
if ($action === 'debug_analytics'){
    require_admin();
    $db = get_db();
    $out = [
        'users' => 0,
        'events' => 0,
        'destinations' => 0,
        'experiences' => 0,
        'itineraries' => 0,
        'feedback' => 0,
        'shops' => 0,
        'errors' => []
    ];
    
    // Check each table
    $tables = ['users', 'events', 'destinations', 'local_experiences', 'itineraries', 'feedback', 'shops'];
    foreach ($tables as $table) {
        $res = $db->query("SELECT COUNT(*) AS c FROM $table");
        if (!$res) {
            $out['errors'][] = "Query failed for $table: " . $db->error;
            continue;
        }
        $row = $res->fetch_assoc();
        $count = $row ? $row['c'] : 0;
        $key = $table === 'local_experiences' ? 'experiences' : $table;
        $out[$key] = (int)$count;
    }
    
    // Also check table existence
    $res = $db->query("SHOW TABLES");
    $tables_exist = [];
    while ($row = $res->fetch_assoc()) {
        $tables_exist[] = reset($row);
    }
    $out['tables'] = $tables_exist;
    $out['database'] = $db->get_charset();
    
    j($out);
}

// Save city risk level (for GeoJSON boundary color)
if ($action === 'save_city_risk_level'){
    $db = get_db();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $risk_level = $input['risk_level'] ?? 'low';
    $city = $input['city'] ?? 'Legazpi';
    $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Validate risk level
    if (!in_array($risk_level, ['low', 'moderate', 'high'])) {
        http_response_code(400);
        j(['success' => false, 'error' => 'Invalid risk level']);
        exit;
    }
    
    // Check if table exists, if not create it
    $table_check = $db->query("SHOW TABLES LIKE 'city_risk_levels'");
    if ($table_check->num_rows === 0) {
        $db->query("CREATE TABLE city_risk_levels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            city VARCHAR(255) NOT NULL,
            risk_level VARCHAR(20) NOT NULL,
            set_by VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }
    
    // Insert or update risk level
    $sql = "INSERT INTO city_risk_levels (city, risk_level, set_by, created_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            risk_level = VALUES(risk_level), 
            updated_at = NOW()";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        // If DUPLICATE KEY doesn't work, try update + insert
        $stmt = $db->prepare("DELETE FROM city_risk_levels WHERE city = ? LIMIT 1");
        $stmt->bind_param("s", $city);
        $stmt->execute();
        
        $stmt = $db->prepare("INSERT INTO city_risk_levels (city, risk_level, set_by) VALUES (?, ?, ?)");
        $admin_user = $_SESSION['admin_email'] ?? 'system';
        $stmt->bind_param("sss", $city, $risk_level, $admin_user);
    } else {
        $admin_user = $_SESSION['admin_email'] ?? 'system';
        $stmt->bind_param("sss", $city, $risk_level, $admin_user);
    }
    
    if ($stmt->execute()) {
        j(['success' => true, 'message' => 'Risk level saved', 'city' => $city, 'level' => $risk_level]);
    } else {
        http_response_code(500);
        j(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}

// Get current city risk level
if ($action === 'get_city_risk_level'){
    $db = get_db();
    $city = $_GET['city'] ?? 'Legazpi';
    
    // Check if table exists
    $table_check = $db->query("SHOW TABLES LIKE 'city_risk_levels'");
    if ($table_check->num_rows === 0) {
        j(['success' => true, 'risk_level' => 'low', 'message' => 'No risk level set, defaulting to low']);
        exit;
    }
    
    $stmt = $db->prepare("SELECT risk_level, updated_at FROM city_risk_levels WHERE city = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        j(['success' => true, 'risk_level' => $row['risk_level'], 'updated_at' => $row['updated_at']]);
    } else {
        j(['success' => true, 'risk_level' => 'low', 'message' => 'No risk level set, defaulting to low']);
    }
    exit;
}

// Initialize database tables
if ($action === 'init_tables'){
    require_admin();
    $db = get_db();
    
    // SQL statements to create tables
    $sql_statements = [
        "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE, name VARCHAR(255), is_admin TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS events (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, description TEXT, image VARCHAR(255) DEFAULT NULL, datetime DATETIME, location VARCHAR(255), capacity INT DEFAULT 0, author VARCHAR(255), anonymous TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS shops (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT, address VARCHAR(255), contact VARCHAR(255), owner_name VARCHAR(255), image VARCHAR(255) DEFAULT NULL, clicks INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS destinations (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT, location VARCHAR(255), image VARCHAR(255) DEFAULT NULL, category_id INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS destination_categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS feedback (id INT AUTO_INCREMENT PRIMARY KEY, user_email VARCHAR(255), user_name VARCHAR(255), anonymous TINYINT DEFAULT 0, message TEXT, rating INT DEFAULT 5, image VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS itineraries (id INT AUTO_INCREMENT PRIMARY KEY, user_email VARCHAR(255), user_name VARCHAR(255), anonymous TINYINT DEFAULT 0, title VARCHAR(255), days INT, destinations TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS local_experiences (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, description TEXT, type VARCHAR(100), price DECIMAL(10, 2), duration VARCHAR(100), image VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
    ];
    
    $results = [];
    foreach ($sql_statements as $sql) {
        if ($db->query($sql)) {
            $results[] = ['success' => true, 'sql' => substr($sql, 0, 50)];
        } else {
            $results[] = ['success' => false, 'sql' => substr($sql, 0, 50), 'error' => $db->error];
        }
    }
    
    j(['initialized' => true, 'results' => $results]);
}

// ===== RECOMMENDATIONS ENDPOINT =====
// Smart recommendations using analytics data and ML heuristics
if ($action === 'recommendations') {
    $db = get_db();
    $user_id = intval($_GET['user_id'] ?? 0);
    $destination = $_GET['destination'] ?? '';
    $limit = intval($_GET['limit'] ?? 10);

    $recommendations = [];

    try {
        // 1. Get popular destinations based on analytics
        $popular_destinations = [];
        $res = $db->query("SELECT place_name, view_count + click_count * 2 as score FROM place_analytics ORDER BY score DESC LIMIT 20");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $popular_destinations[] = $r;
            }
        }

        // 2. Get trending experiences based on recent activity
        $trending_experiences = [];
        $res = $db->query("SELECT e.title, e.type, COUNT(al.id) as activity_count FROM local_experiences e LEFT JOIN activity_logs al ON al.meta LIKE CONCAT('%', e.title, '%') AND al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY e.id ORDER BY activity_count DESC LIMIT 10");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $trending_experiences[] = $r;
            }
        }

        // 3. Get personalized recommendations based on user history (if user_id provided)
        $personalized = [];
        if ($user_id > 0) {
            // Get user's past itineraries and preferences
            $res = $db->query("SELECT destinations FROM itineraries WHERE user_id = $user_id OR (user_id IS NULL AND anonymous_name IS NOT NULL) ORDER BY created_at DESC LIMIT 5");
            if ($res) {
                $user_destinations = [];
                while ($r = $res->fetch_assoc()) {
                    $dests = json_decode($r['destinations'], true);
                    if (is_array($dests)) {
                        $user_destinations = array_merge($user_destinations, $dests);
                    }
                }

                // Find similar destinations based on user's history
                if (!empty($user_destinations)) {
                    $dest_list = "'" . implode("','", array_map([$db, 'real_escape_string'], $user_destinations)) . "'";
                    $res = $db->query("SELECT name, description, image FROM destinations WHERE name NOT IN ($dest_list) ORDER BY RAND() LIMIT 5");
                    if ($res) {
                        while ($r = $res->fetch_assoc()) {
                            $personalized[] = array_merge($r, ['reason' => 'Based on your previous trips']);
                        }
                    }
                }
            }
        }

        // 4. Weather-based recommendations
        $weather_recommendations = [];
        if (!empty($destination)) {
            // Get current weather for the destination
            $coords = get_destination_coords($destination);
            if ($coords) {
                // Simple weather logic - in real implementation, this would call weather API
                $weather_recommendations = [
                    ['type' => 'activity', 'title' => 'Indoor Museum Visit', 'reason' => 'Perfect weather for cultural exploration'],
                    ['type' => 'restaurant', 'title' => 'Local Cafe Experience', 'reason' => 'Cozy indoor dining option']
                ];
            }
        }

        // 5. ML-based predictions using existing analytics
        $ml_recommendations = [];
        $res = $db->query("SELECT e.title as event_name, p.attendance, p.waste_prediction FROM events e LEFT JOIN ml_predictions p ON e.id = p.event_id WHERE p.attendance > 100 ORDER BY p.created_at DESC LIMIT 5");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $ml_recommendations[] = [
                    'title' => $r['event_name'],
                    'type' => 'event',
                    'predicted_crowd' => intval($r['attendance']),
                    'waste_prediction' => floatval($r['waste_prediction']),
                    'reason' => 'ML prediction: High attendance expected'
                ];
            }
        }

        // Combine all recommendations with scoring
        $all_recommendations = [];

        // Add popular destinations
        foreach ($popular_destinations as $dest) {
            $all_recommendations[] = [
                'type' => 'destination',
                'title' => $dest['place_name'],
                'score' => $dest['score'],
                'reason' => 'Popular choice based on visitor analytics',
                'confidence' => min(95, $dest['score'] / 10) // Simple confidence calculation
            ];
        }

        // Add trending experiences
        foreach ($trending_experiences as $exp) {
            $all_recommendations[] = [
                'type' => 'experience',
                'title' => $exp['title'],
                'category' => $exp['type'],
                'score' => $exp['activity_count'],
                'reason' => 'Trending based on recent activity',
                'confidence' => min(90, $exp['activity_count'] * 10)
            ];
        }

        // Add personalized recommendations
        foreach ($personalized as $pers) {
            $all_recommendations[] = [
                'type' => 'destination',
                'title' => $pers['name'],
                'description' => $pers['description'],
                'image' => $pers['image'],
                'score' => 100, // High score for personalized
                'reason' => $pers['reason'],
                'confidence' => 95
            ];
        }

        // Add weather-based recommendations
        foreach ($weather_recommendations as $weather) {
            $all_recommendations[] = array_merge($weather, [
                'score' => 80,
                'confidence' => 85
            ]);
        }

        // Add ML recommendations
        foreach ($ml_recommendations as $ml) {
            $all_recommendations[] = array_merge($ml, [
                'score' => $ml['predicted_crowd'] / 10,
                'confidence' => 88
            ]);
        }

        // Sort by score and limit results
        usort($all_recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $recommendations = array_slice($all_recommendations, 0, $limit);

        j([
            'success' => true,
            'recommendations' => $recommendations,
            'total' => count($recommendations),
            'analytics_used' => [
                'popular_destinations' => count($popular_destinations),
                'trending_experiences' => count($trending_experiences),
                'personalized' => count($personalized),
                'weather_based' => count($weather_recommendations),
                'ml_based' => count($ml_recommendations)
            ]
        ]);

    } catch (Exception $e) {
        j(['success' => false, 'error' => 'Recommendations failed: ' . $e->getMessage()]);
    }
}

// Default
http_response_code(400);
echo json_encode(['error'=>'unknown action']);

?>
