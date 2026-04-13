<?php
$db = new mysqli('localhost','root','','capstone_db');

// Add columns if missing
$cols = $db->query("SHOW COLUMNS FROM events LIKE 'open_days_start'");
if (!$cols || $cols->num_rows == 0) {
    $db->query("ALTER TABLE events ADD COLUMN open_days_start VARCHAR(20) DEFAULT NULL");
    echo "Added open_days_start column\n";
}
$cols = $db->query("SHOW COLUMNS FROM events LIKE 'open_days_end'");
if (!$cols || $cols->num_rows == 0) {
    $db->query("ALTER TABLE events ADD COLUMN open_days_end VARCHAR(20) DEFAULT NULL");
    echo "Added open_days_end column\n";
}

// Backfill from prediction JSON
$r = $db->query("SELECT id, title, prediction, open_days_start, open_days_end FROM events");
while($row = $r->fetch_assoc()) {
    echo "ID: {$row['id']} | {$row['title']} | open_days: [{$row['open_days_start']}..{$row['open_days_end']}]\n";
    if (empty($row['open_days_start']) && !empty($row['prediction'])) {
        $pred = json_decode($row['prediction'], true);
        $ods = $pred['open_days_start'] ?? null;
        $ode = $pred['open_days_end'] ?? null;
        $sched = $pred['event_schedule'] ?? null;
        if ($ods && $ode) {
            $stmt = $db->prepare("UPDATE events SET open_days_start=?, open_days_end=? WHERE id=?");
            $stmt->bind_param('ssi', $ods, $ode, $row['id']);
            $stmt->execute();
            echo "  -> Backfilled from prediction: $ods to $ode\n";
        } elseif ($sched) {
            $schedule = json_decode($sched, true);
            if (is_array($schedule) && count($schedule) > 0) {
                $dateLabel = $schedule[0]['date'] ?? '';
                $parts = preg_split('/\s*(?:to|–|-)\s*/i', $dateLabel);
                if (count($parts) >= 2) {
                    $stmt = $db->prepare("UPDATE events SET open_days_start=?, open_days_end=? WHERE id=?");
                    $stmt->bind_param('ssi', $parts[0], $parts[1], $row['id']);
                    $stmt->execute();
                    echo "  -> Backfilled from schedule: {$parts[0]} to {$parts[1]}\n";
                }
            }
        }
    }
}
echo "\nDone!\n";
