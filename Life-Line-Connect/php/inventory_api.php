<?php
header('Content-Type: application/json');
require_once 'db_config.php';
$action = $_GET['action'] ?? '';

if ($action == 'fetch') {
    $stmt = oci_parse($ora_conn, "SELECT id, blood_group, units FROM blood_inventory ORDER BY blood_group ASC");
    oci_execute($stmt);
    $data = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $data[] = [
            'id' => $row['ID'],
            'blood_group' => $row['BLOOD_GROUP'],
            'units' => (int)$row['UNITS']
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $data]);

} elseif ($action == 'add') {
    $blood_group = $_POST['blood_group'];
    $units = (int)$_POST['units'];

    $sql = "INSERT INTO blood_inventory (blood_group, units) VALUES (:bg, :units)";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":bg", $blood_group);
    oci_bind_by_name($stmt, ":units", $units);

    if (@oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => $e['message']]);
    }

} elseif ($action == 'update') {
    $id = $_POST['id'];
    $blood_group = $_POST['blood_group'];
    $units = (int)$_POST['units'];

    $sql = "UPDATE blood_inventory SET blood_group = :bg, units = :units WHERE id = :id";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":bg", $blood_group);
    oci_bind_by_name($stmt, ":units", $units);
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => $e['message']]);
    }

} elseif ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = oci_parse($ora_conn, "DELETE FROM blood_inventory WHERE id = :id");
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
    }
}
?>