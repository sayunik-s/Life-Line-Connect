<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

// User ලොග් වෙලාද කියලා බලනවා
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// අලුත් Columns (USERNAME, GENDER, ADDRESS, DISTRICT) ඇතුළත් කර හැදූ SELECT Query එක
$sql = "SELECT 
            USERNAME,
            FULL_NAME, 
            NIC, 
            GENDER,
            BLOOD_GROUP, 
            TO_CHAR(DOB, 'YYYY-MM-DD') AS DOB, 
            WEIGHT, 
            MOBILE, 
            ADDRESS,
            DISTRICT,
            TO_CHAR(LAST_DONATED, 'YYYY-MM-DD') AS LAST_DONATED_DATE 
        FROM DONORS 
        WHERE ID = :did";

$stmt = oci_parse($ora_conn, $sql);

// Bind Variable
oci_bind_by_name($stmt, ":did", $user_id);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);

if ($row) {
    // දත්ත ටික JSON විදිහට Frontend එකට යවනවා
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>