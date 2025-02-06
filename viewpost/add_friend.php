<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['friend_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'];

try {
    $stmt = $conn->prepare("
        INSERT INTO friendships (user_id, friend_id, status)
        VALUES (:user_id, :friend_id, 'pending')
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':friend_id', $friend_id);
    $stmt->execute();
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
