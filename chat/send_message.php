<?php
session_start();
include '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? '';
$image_path = null;

// Validate inputs
if (!$receiver_id) {
    echo json_encode(['success' => false, 'error' => 'No receiver specified']);
    exit;
}

// Handle image upload if present
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $file_name = uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $image_path = $target_path;
    }
}

// Insert message into database
try {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$sender_id, $receiver_id, $message, $image_path]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
