<?php
// user_dashboard_api.php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// User Info
$stmt = oci_parse($ora_conn, "SELECT full_name, blood_group FROM donors WHERE id = :id");
oci_bind_by_name($stmt, ":id", $user_id);
oci_execute($stmt);
$user_res = oci_fetch_assoc($stmt);

// History
$stmt2 = oci_parse($ora_conn, "SELECT camp_name, units, TO_CHAR(donation_date, 'YYYY-MM-DD') AS d_date, status FROM donations WHERE donor_id = :id ORDER BY donation_date DESC");
oci_bind_by_name($stmt2, ":id", $user_id);
oci_execute($stmt2);

$history = [];
$total_units = 0;
$last_donated_date = null;

while ($row = oci_fetch_assoc($stmt2)) {
    $history[] = [
        'camp_name' => $row['CAMP_NAME'],
        'units' => (int)$row['UNITS'],
        'donation_date' => $row['D_DATE'],
        'status' => $row['STATUS']
    ];
    $total_units += (int)$row['UNITS'];
    if($last_donated_date === null) $last_donated_date = $row['D_DATE'];
}

// Eligibility (90 Days rule)
$is_eligible = true;
$next_date_str = "Available Now";
if ($last_donated_date) {
    $last_date = new DateTime($last_donated_date);
    $today = new DateTime();
    $diff = $today->diff($last_date)->days;
    if ($diff < 90) {
        $is_eligible = false;
        $last_date->modify('+90 days');
        $next_date_str = $last_date->format('Y-m-d');
    }
}

// Camps for Map
$camps = [];
$camp_res = oci_parse($ora_conn, "SELECT name, venue, TO_CHAR(camp_date, 'YYYY-MM-DD') AS c_date, latitude, longitude FROM camps WHERE status = 'Upcoming'");
oci_execute($camp_res);
while ($row = oci_fetch_assoc($camp_res)) {
    $camps[] = [
        'name' => $row['NAME'],
        'venue' => $row['VENUE'],
        'date' => $row['C_DATE'],
        'lat' => (float)$row['LATITUDE'],
        'lng' => (float)$row['LONGITUDE']
    ];
}

echo json_encode([
    'status' => 'success',
    'user' => ['full_name' => $user_res['FULL_NAME'], 'blood_group' => $user_res['BLOOD_GROUP']],
    'stats' => ['total_units' => $total_units, 'is_eligible' => $is_eligible, 'next_date' => $next_date_str],
    'history' => $history,
    'camps' => $camps
]);
?>