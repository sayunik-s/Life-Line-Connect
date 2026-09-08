<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "system", "admin", "LifeLine_DB");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// පෝස්ට් ලබා ගැනීම (Fetch Feed)
if ($action == 'fetch') {
    $query = "SELECT * FROM community_posts ORDER BY created_at DESC";
    $result = $conn->query($query);
    $posts = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['formatted_date'] = date("M d, Y - h:i A", strtotime($row['created_at']));
            $posts[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $posts]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Action.']);
}

$conn->close();
?>