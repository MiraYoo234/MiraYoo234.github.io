<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db2.php';

// Check if post ID is provided in the URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Ensure that the logged-in user is the author of the post
    $current_user = $_SESSION['username'];

    try {
        // Prepare SQL to delete the post, but only if the current user is the author
        $query = "DELETE FROM posts WHERE id = :id AND author = :author";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':author', $current_user, PDO::PARAM_STR);

        // Execute the deletion
        $stmt->execute();

        // Check if the post was deleted
        if ($stmt->rowCount() > 0) {
            // Post deleted successfully, redirect to personalblog.php
            header('Location: personalblog.php');
            exit;
        } else {
            // Post not found or user is not the author
            echo "Error: Post not found or you do not have permission to delete this post.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

} else {
    echo "Error: Post ID not provided.";
}
?>
