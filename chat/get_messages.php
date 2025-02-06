<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['contact_id'])) {
    exit(json_encode([]));
}

$user_id = $_SESSION['user_id'];
$contact_id = $_GET['contact_id'];

// Mark messages as read
$stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = TRUE 
    WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE
");
$stmt->bindParam(1, $contact_id);
$stmt->bindParam(2, $user_id);
$stmt->execute();

// Fetch messages
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?)
    OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
");
$stmt->bindParam(1, $user_id);
$stmt->bindParam(2, $contact_id);
$stmt->bindParam(3, $contact_id);
$stmt->bindParam(4, $user_id);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
