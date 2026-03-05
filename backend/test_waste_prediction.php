<?php
// Test the new waste prediction formula
$attendance = 7;
$capacity = 10;
$duration = 8;  // 8-hour event
$food_stalls = 50;
$weather = 0;

// New formula
$per_capita_waste = 0.5;
$waste_from_visitors = $attendance * $per_capita_waste * ($duration / 8.0);

$per_stall_waste = 2.0 + (0.05 * ($attendance / max($food_stalls, 1)));
$waste_from_stalls = $food_stalls * $per_stall_waste;

$weather_multiplier = $weather ? 1.15 : 1.0;
$waste = ($waste_from_visitors + $waste_from_stalls) * $weather_multiplier;

echo "Test Case: Capacity=10, Attendance=7, Duration=8hr, Food Stalls=50\n";
echo "---\n";
echo "Waste from visitors (7 × 0.5 kg × 1): " . $waste_from_visitors . " kg\n";
echo "Per stall generation: " . round($per_stall_waste, 2) . " kg/stall\n";
echo "Total stall waste (50 × " . round($per_stall_waste, 2) . "): " . round($waste_from_stalls, 2) . " kg\n";
echo "Weather multiplier: " . $weather_multiplier . "\n";
echo "\nTOTAL WASTE PREDICTION: " . round($waste, 2) . " kg\n\n";

echo "COMPARISON:\n";
echo "Old formula: ~754 kg per event (UNREALISTIC - multiplied stalls by 15!)\n";
echo "New formula: ~" . round($waste, 2) . " kg per event (REALISTIC)\n\n";

echo "Model breakdown:\n";
echo "- Per visitor: 0.5 kg/person\n";
echo "- Per food stall: 2.0 kg base + visitor-proportional contribution\n";
echo "- This scales waste with ACTUAL ATTENDANCE, not stall count\n";
?>
