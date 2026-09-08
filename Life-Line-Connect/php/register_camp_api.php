<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donor_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $mobile = trim($_POST['mobile']);

    $stmt = oci_parse($ora_conn, "INSERT INTO camp_registrations (donor_id, full_name, age, mobile) VALUES (:did, :fn, :age, :mob)");
    oci_bind_by_name($stmt, ":did", $donor_id);
    oci_bind_by_name($stmt, ":fn", $name);
    oci_bind_by_name($stmt, ":age", $age);
    oci_bind_by_name($stmt, ":mob", $mobile);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Successfully registered for the camp!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed.']);
    }
}
?>