<?php
header('Content-Type: application/json');
require_once 'db_config.php';
$action = $_GET['action'] ?? '';

if ($action == 'fetch') {
    $stmt = oci_parse($ora_conn, "SELECT * FROM hospital_requests ORDER BY FIELD(status, 'Pending', 'Dispatched'), id DESC");
    // Note: Oracle uses CASE instead of FIELD for custom ordering if needed, simple ORDER BY ID DESC works fine:
    $stmt = oci_parse($ora_conn, "SELECT * FROM hospital_requests ORDER BY id DESC");
    oci_execute($stmt);
    $data = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $data[] = [
            'id' => $row['ID'],
            'req_format_id' => "REQ-" . str_pad($row['ID'], 3, "0", STR_PAD_LEFT),
            'hospital_name' => $row['HOSPITAL_NAME'],
            'blood_group' => $row['BLOOD_GROUP'],
            'units' => $row['UNITS'],
            'urgency' => $row['URGENCY'],
            'status' => $row['STATUS']
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
} elseif ($action == 'add') {
    // Calling PL/SQL Procedure created in Oracle
    $sql = "BEGIN log_hospital_request(:hosp, :bg, :units, :urg); END;";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":hosp", $_POST['hospital']);
    oci_bind_by_name($stmt, ":bg", $_POST['group']);
    oci_bind_by_name($stmt, ":units", $_POST['units']);
    oci_bind_by_name($stmt, ":urg", $_POST['urgency']);
    
    if(@oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => $e['message']]);
    }
} elseif ($action == 'dispatch') {
    $stmt = oci_parse($ora_conn, "UPDATE hospital_requests SET status = 'Dispatched' WHERE id = :id");
    oci_bind_by_name($stmt, ":id", $_POST['id']);
    if(oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to dispatch']);
    }
}
?>