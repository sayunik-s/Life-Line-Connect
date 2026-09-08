<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and Password are required.']);
        exit();
    }

    // Admin Check
    if ($username === "admin" && $password === "admin123") {
        $_SESSION['user_id'] = 'Admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['full_name'] = 'System Admin';
        echo json_encode(['status' => 'success', 'message' => 'Admin Login Successful!', 'redirect' => 'dashboard.html']);
        exit();
    }

    // Normal User Check (Oracle Database)
    $sql = "SELECT id, full_name, password FROM donors WHERE username = :uname";
    $stmt = oci_parse($ora_conn, $sql);
    oci_bind_by_name($stmt, ":uname", $username);
    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        if (password_verify($password, $row['PASSWORD'])) {
            $_SESSION['user_id'] = $row['ID'];
            $_SESSION['role'] = 'user';
            $_SESSION['full_name'] = $row['FULL_NAME'];
            echo json_encode(['status' => 'success', 'message' => 'User Login Successful!', 'redirect' => 'user_dashboard.html']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username not found!']);
    }
    oci_free_statement($stmt);
}
$ora_conn = null;
?>