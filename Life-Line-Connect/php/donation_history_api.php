<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$donor_id = $_SESSION['user_id'];
$sql = "SELECT camp_name, TO_CHAR(donation_date, 'YYYY-MM-DD') AS d_date, units, status FROM donations WHERE donor_id = :did ORDER BY donation_date DESC";
$stmt = oci_parse($ora_conn, $sql);
oci_bind_by_name($stmt, ":did", $donor_id);
oci_execute($stmt);

$history = [];
while ($row = oci_fetch_assoc($stmt)) {
    $history[] = [
        'date' => $row['D_DATE'],
        'camp' => $row['CAMP_NAME'],
        'units' => $row['UNITS'],
        'status' => $row['STATUS']
    ];
}

echo json_encode(['status' => 'success', 'data' => $history]);
?>