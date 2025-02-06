<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['contact_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$contact_id = $_GET['contact_id'];

try {
    // Begin transaction
    $conn->beginTransaction();

    // Delete messages between these users
    $stmt = $conn->prepare("
        DELETE FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?)
        OR (sender_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$user_id, $contact_id, $contact_id, $user_id]);

    // Delete conversation record
    $stmt = $conn->prepare("
        DELETE FROM conversations 
        WHERE (user1_id = ? AND user2_id = ?)
        OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->execute([
        min($user_id, $contact_id),
        max($user_id, $contact_id),
        max($user_id, $contact_id),
        min($user_id, $contact_id)
    ]);

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
