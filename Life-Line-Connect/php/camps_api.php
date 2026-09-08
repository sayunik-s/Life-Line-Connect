<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$action = $_GET['action'] ?? 'fetch';

if ($action == 'fetch') {
    $stmt = oci_parse($ora_conn, "SELECT id, name, venue, TO_CHAR(camp_date, 'YYYY-MM-DD') AS c_date, latitude, longitude, status FROM camps ORDER BY camp_date ASC");
    oci_execute($stmt);
    $camps = [];

    while ($row = oci_fetch_assoc($stmt)) {
        $camps[] = [
            'id' => $row['ID'],
            'name' => $row['NAME'],
            'venue' => $row['VENUE'],
            'date' => $row['C_DATE'],
            'lat' => (float)$row['LATITUDE'],
            'lng' => (float)$row['LONGITUDE'],
            'status' => $row['STATUS']
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $camps]);

} elseif ($action == 'add') {
    $name = trim($_POST['name']);
    $venue = trim($_POST['venue']);
    $camp_date = date('d-M-Y', strtotime($_POST['camp_date']));
    $lat = (float)$_POST['latitude'];
    $lng = (float)$_POST['longitude'];
    $status = $_POST['status'];

    $sql = "INSERT INTO camps (name, venue, camp_date, latitude, longitude, status) VALUES (:nm, :ven, TO_DATE(:dt, 'DD-MON-YYYY'), :lat, :lng, :stat)";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":ven", $venue);
    oci_bind_by_name($stmt, ":dt", $camp_date);
    oci_bind_by_name($stmt, ":lat", $lat);
    oci_bind_by_name($stmt, ":lng", $lng);
    oci_bind_by_name($stmt, ":stat", $status);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => $e['message']]);
    }

} elseif ($action == 'update') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $venue = trim($_POST['venue']);
    $camp_date = date('d-M-Y', strtotime($_POST['camp_date']));
    $lat = (float)$_POST['latitude'];
    $lng = (float)$_POST['longitude'];
    $status = $_POST['status'];

    $sql = "UPDATE camps SET name = :nm, venue = :ven, camp_date = TO_DATE(:dt, 'DD-MON-YYYY'), latitude = :lat, longitude = :lng, status = :stat WHERE id = :id";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":ven", $venue);
    oci_bind_by_name($stmt, ":dt", $camp_date);
    oci_bind_by_name($stmt, ":lat", $lat);
    oci_bind_by_name($stmt, ":lng", $lng);
    oci_bind_by_name($stmt, ":stat", $status);
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => $e['message']]);
    }

} elseif ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = oci_parse($ora_conn, "DELETE FROM camps WHERE id = :id");
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
    }
}
?>