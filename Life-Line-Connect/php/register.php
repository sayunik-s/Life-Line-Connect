<?php
header('Content-Type: application/json');
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $nic = trim($_POST['nic']);
    $dob = date('d-M-Y', strtotime($_POST['dob']));
    $gender = $_POST['gender'];
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $district = $_POST['district'];
    $blood_group = $_POST['blood_group'];
    $weight = (float)$_POST['weight'];
    $last_donated = !empty($_POST['last_donated']) ? date('d-M-Y', strtotime($_POST['last_donated'])) : null;

    if (empty($username) || empty($password) || empty($full_name) || empty($nic) || empty($dob) || empty($gender) || empty($mobile) || empty($address) || empty($district) || empty($blood_group) || empty($weight)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        exit();
    }

    $sql = "INSERT INTO donors (username, password, full_name, nic, dob, gender, mobile, address, district, blood_group, weight, last_donated) 
            VALUES (:un, :pw, :fn, :nic, TO_DATE(:dob, 'DD-MON-YYYY'), :gen, :mob, :addr, :dist, :bg, :wt, TO_DATE(:ld, 'DD-MON-YYYY'))";
    
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":un", $username);
    oci_bind_by_name($stmt, ":pw", $password);
    oci_bind_by_name($stmt, ":fn", $full_name);
    oci_bind_by_name($stmt, ":nic", $nic);
    oci_bind_by_name($stmt, ":dob", $dob);
    oci_bind_by_name($stmt, ":gen", $gender);
    oci_bind_by_name($stmt, ":mob", $mobile);
    oci_bind_by_name($stmt, ":addr", $address);
    oci_bind_by_name($stmt, ":dist", $district);
    oci_bind_by_name($stmt, ":bg", $blood_group);
    oci_bind_by_name($stmt, ":wt", $weight);
    oci_bind_by_name($stmt, ":ld", $last_donated);

    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Registration Successful!']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e['message']]);
    }
    oci_free_statement($stmt);
}
?>