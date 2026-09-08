<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

// User ලොග් වෙලාද බලනවා
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    
    // දත්ත තියෙනවද කියලා බලලා ගන්නවා (අලුත් ඒවාත් එක්කම)
    $dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
    $weight = isset($_POST['weight']) ? trim($_POST['weight']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $district = isset($_POST['district']) ? trim($_POST['district']) : '';
    $last_donated = isset($_POST['last_donated']) ? trim($_POST['last_donated']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // SQL Query එකට අවශ්‍ය කෑලි ටික හදනවා (හිස් නැති ඒවා විතරක්)
    $update_fields = [];
    
    if ($weight !== '') { $update_fields[] = "WEIGHT = :weight"; }
    if ($mobile !== '') { $update_fields[] = "MOBILE = :mobile"; }
    if ($address !== '') { $update_fields[] = "ADDRESS = :address"; }
    if ($district !== '') { $update_fields[] = "DISTRICT = :district"; }
    if ($dob !== '') { $update_fields[] = "DOB = TO_DATE(:dob, 'YYYY-MM-DD')"; }
    if ($last_donated !== '') { $update_fields[] = "LAST_DONATED = TO_DATE(:ld, 'YYYY-MM-DD')"; }
    if ($password !== '') { $update_fields[] = "PASSWORD = :pwd"; }

    // මොකුත්ම වෙනස් කරලා නැත්නම්
    if (count($update_fields) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'No changes made to update.']);
        exit();
    }

    // අවසාන SQL එක හදනවා (Table එකේ නම DONORS)
    $sql = "UPDATE DONORS SET " . implode(", ", $update_fields) . " WHERE ID = :did";
    
    $stmt = oci_parse($ora_conn, $sql);
    
    // Bind Variables (දත්ත සම්බන්ධ කිරීම)
    oci_bind_by_name($stmt, ":did", $user_id);
    
    if ($weight !== '') { 
        $w = (int)$weight; 
        oci_bind_by_name($stmt, ":weight", $w); 
    }
    if ($mobile !== '') { oci_bind_by_name($stmt, ":mobile", $mobile); }
    if ($address !== '') { oci_bind_by_name($stmt, ":address", $address); }
    if ($district !== '') { oci_bind_by_name($stmt, ":district", $district); }
    if ($dob !== '') { oci_bind_by_name($stmt, ":dob", $dob); }
    if ($last_donated !== '') { oci_bind_by_name($stmt, ":ld", $last_donated); }
    if ($password !== '') { oci_bind_by_name($stmt, ":pwd", $password); }

    // Execute කිරීම
    if (oci_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
    } else {
        $e = oci_error($stmt);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e['message']]);
    }
}
?>