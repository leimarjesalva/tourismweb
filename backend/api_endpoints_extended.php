<?php
// ===== DESTINATIONS =====
if ($action === 'list_destinations'){
    $db = get_db();
    $res = $db->query('SELECT * FROM destinations ORDER BY created_at DESC');
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    j(['destinations'=>$rows]);
}

if ($action === 'create_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    $stmt = $db->prepare('INSERT INTO destinations (name,description,location,image) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss',$d['name'],$d['description'],$d['location'],$image);
    if ($stmt->execute()) j(['success'=>true,'id'=>$db->insert_id]);
    j(['success'=>false,'error'=>$stmt->error]);
}

if ($action === 'edit_destination'){
    require_admin();
    $d = json_input();
    $db = get_db();
    $image = $d['image'] ?? null;
    if ($image) {
        $stmt = $db->prepare('UPDATE destinations SET name=?, description=?, location=?, image=? WHERE id=?');
        $stmt->bind_param('ssssi',$d['name'],$d['description'],$d['location'],$image,$d['id']);
    } else {
        $stmt = $db->prepare('UPDATE destinations SET name=?, description=?, location=? WHERE id=?');
        $stmt->bind_param('sssi',$d['name'],$d['description'],$d['location'],$d['id']);
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
    while($r = $res->fetch_assoc()) $rows[] = $r;
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
    while($r = $res->fetch_assoc()) $rows[] = $r;
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
?>
