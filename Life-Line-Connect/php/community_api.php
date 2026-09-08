<?php
header('Content-Type: application/json');
require_once 'db_config.php';
$action = $_GET['action'] ?? '';

if ($action == 'fetch') {
    try {
        $cursor = $mongo_posts->find([], ['sort' => ['created_at' => -1]]);
        $data = [];
        foreach ($cursor as $doc) {
            $data[] = [
                'id' => (string)$doc['_id'],
                'post_type' => $doc['type'],
                'author_name' => $doc['author'],
                'message_body' => $doc['body'],
                'rating' => isset($doc['rating']) ? (int)$doc['rating'] : null,
                'formatted_date' => isset($doc['created_at']) ? date("M d, Y - h:i A", $doc['created_at']->toDateTime()->getTimestamp()) : "Just now"
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} elseif ($action == 'add') {
    try {
        $type = $_POST['type'];
        $author = trim($_POST['author']);
        $body = trim($_POST['body']);

        $document = [
            'type' => $type,
            'author' => $author,
            'body' => $body,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        if ($type === 'Review' && isset($_POST['rating'])) {
            $document['rating'] = (int)$_POST['rating'];
        }

        $result = $mongo_posts->insertOne($document);
        if ($result->getInsertedCount() === 1) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save to MongoDB']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} elseif ($action == 'update') {
    try {
        $id = $_POST['id'];
        $type = $_POST['type'];
        $author = trim($_POST['author']);
        $body = trim($_POST['body']);

        $updateFields = [
            'type' => $type,
            'author' => $author,
            'body' => $body
        ];

        if ($type === 'Review' && isset($_POST['rating'])) {
            $updateFields['rating'] = (int)$_POST['rating'];
        } else {
            $updateFields['rating'] = null; // Clear rating if type changed from review
        }

        $result = $mongo_posts->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => $updateFields]
        );

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} elseif ($action == 'delete') {
    try {
        $id = $_POST['id'];
        $result = $mongo_posts->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        
        if ($result->getDeletedCount() === 1) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>