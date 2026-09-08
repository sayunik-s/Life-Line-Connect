<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donor_id = (int)$_POST['donor_id'];
    $camp_name = trim($_POST['camp_name']);
    $units = (int)$_POST['units'];

    // මෙතන DONATION_DATE එකට SYSDATE එක යනවා
    $sql = "INSERT INTO donations (donor_id, camp_name, units, status, donation_date) 
            VALUES (:did, :camp, :units, 'Completed', SYSDATE)";
            
    $stmt = oci_parse($ora_conn, $sql);
    
    oci_bind_by_name($stmt, ":did", $donor_id);
    oci_bind_by_name($stmt, ":camp", $camp_name);
    oci_bind_by_name($stmt, ":units", $units);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Successfully recorded! User history updated.']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e['message']]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>