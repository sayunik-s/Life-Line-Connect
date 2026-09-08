<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$stats = ['total_donors' => 0, 'total_units' => 0, 'pending_requests' => 0, 'active_camps' => 0];
$inventory = ["A+" => 0, "A-" => 0, "B+" => 0, "B-" => 0, "O+" => 0, "O-" => 0, "AB+" => 0, "AB-" => 0];

// Total Donors
$s1 = oci_parse($ora_conn, "SELECT COUNT(*) AS CNT FROM donors");
oci_execute($s1);
if($row = oci_fetch_assoc($s1)) $stats['total_donors'] = (int)$row['CNT'];

// Pending Requests
$s2 = oci_parse($ora_conn, "SELECT COUNT(*) AS CNT FROM hospital_requests WHERE status = 'Pending'");
oci_execute($s2);
if($row = oci_fetch_assoc($s2)) $stats['pending_requests'] = (int)$row['CNT'];

// Active Camps
$s3 = oci_parse($ora_conn, "SELECT COUNT(*) AS CNT FROM camps WHERE status = 'Upcoming'");
oci_execute($s3);
if($row = oci_fetch_assoc($s3)) $stats['active_camps'] = (int)$row['CNT'];

// Inventory & Total Units
$s4 = oci_parse($ora_conn, "SELECT d.blood_group, SUM(dn.units) as stock FROM donors d JOIN donations dn ON d.id = dn.donor_id WHERE dn.status = 'Completed' GROUP BY d.blood_group");
oci_execute($s4);
while($row = oci_fetch_assoc($s4)) {
    $bg = $row['BLOOD_GROUP'];
    $stock = (int)$row['STOCK'];
    if (isset($inventory[$bg])) {
        $inventory[$bg] = $stock;
        $stats['total_units'] += $stock;
    }
}

echo json_encode(['status' => 'success', 'stats' => $stats, 'inventory' => $inventory]);
?>