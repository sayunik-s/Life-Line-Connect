<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$action = $_GET['action'] ?? 'fetch';

if ($action == 'fetch') {
    $stmt = oci_parse($ora_conn, "SELECT id, full_name, username, blood_group FROM donors ORDER BY id DESC");
    oci_execute($stmt);
    $donors = [];

    while ($row = oci_fetch_assoc($stmt)) {
        $donors[] = [
            'id' => $row['ID'],
            'name' => $row['FULL_NAME'],
            'username' => $row['USERNAME'],
            'age' => 25, // ඔබ dob එකක් save කර ඇත්නම් මෙහි වයස ගණනය කළ හැක
            'bg' => $row['BLOOD_GROUP']
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $donors]);

} elseif ($action == 'update') {
    $id = $_POST['id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $blood_group = $_POST['blood_group'];

    $sql = "UPDATE donors SET full_name = :fn, username = :un, blood_group = :bg WHERE id = :id";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":fn", $full_name);
    oci_bind_by_name($stmt, ":un", $username);
    oci_bind_by_name($stmt, ":bg", $blood_group);
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => $e['message']]);
    }

} elseif ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = oci_parse($ora_conn, "DELETE FROM donors WHERE id = :id");
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
    }
}
?>