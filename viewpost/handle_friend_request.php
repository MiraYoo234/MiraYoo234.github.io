<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['friendship_id']) || !isset($_POST['action'])) {
    header('Location: ../index.php');
    exit;
}

$friendship_id = $_POST['friendship_id'];
$action = $_POST['action'];

try {
    if ($action === 'accept') {
        $stmt = $conn->prepare("
            UPDATE friendships 
            SET status = 'accepted' 
            WHERE id = :friendship_id
        ");
    } else {
        $stmt = $conn->prepare("
            DELETE FROM friendships 
            WHERE id = :friendship_id
        ");
    }
    
    $stmt->bindParam(':friendship_id', $friendship_id);
    $stmt->execute();
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
