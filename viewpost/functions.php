<?php
// Fetch posts with optional filters
function fetchPosts($conn, $search_query = '', $category_filter = '', $tags_filter = '') {
    $sql = "SELECT p.*, u.pfpimg FROM posts p 
            LEFT JOIN users u ON p.author = u.username
            WHERE 1=1";

    if (!empty($search_query)) {
        $sql .= " AND (p.title LIKE :search OR p.content LIKE :search)";
    }
    if (!empty($category_filter)) {
        $sql .= " AND p.category = :category";
    }
    if (!empty($tags_filter)) {
        $sql .= " AND p.tags LIKE :tags";
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($search_query)) {
        $stmt->bindValue(':search', '%' . $search_query . '%');
    }
    if (!empty($category_filter)) {
        $stmt->bindValue(':category', $category_filter);
    }
    if (!empty($tags_filter)) {
        $stmt->bindValue(':tags', '%' . $tags_filter . '%');
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch user details
function fetchUser($conn, $username) {
    $stmt = $conn->prepare("SELECT username, pfpimg FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
