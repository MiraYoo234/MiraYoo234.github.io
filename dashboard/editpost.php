<?php
session_start();
include 'db2.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Fetch current user
$current_user = $_SESSION['username'];

// Check if post ID is set in the query string
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    try {
        // Fetch the post details
        $query = "SELECT * FROM posts WHERE id = :id AND author = :author";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':author', $current_user, PDO::PARAM_STR);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the post exists and belongs to the logged-in user
        if (!$post) {
            echo "No such post found or you don't have permission to edit this post.";
            exit;
        }

        // Get the current category for the dropdown
        $category = $post['category'];

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get updated post details
            $title = $_POST['title'];
            $content = $_POST['content'];
            $category = $_POST['category'];
            $tags = $_POST['tags'];

            // Prepare update query
            $updateQuery = "UPDATE posts SET title = :title, content = :content, category = :category, tags = :tags WHERE id = :id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':tags', $tags);
            $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);

            // Execute update
            $stmt->execute();

            // Redirect back to personal_blog.php
            header('Location: personalblog.php');
            exit;
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No post ID provided.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="editpost.css"> <!-- Your CSS file -->
</head>
<body>
    <header>
        <div class="logo">My Blog</div>
        <br>
        <nav>
            <a href="../viewpost/viewpost.php">Blogs</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="../logout/logout.php">Logout</a>
        </nav>
    </header>

    <!-- Centered Card Layout -->
    <div class="container">
        <div class="form-card">
            <h1>Edit Your Post</h1>
            <form action="editpost.php?id=<?php echo $post_id; ?>" method="POST">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

                <label for="content">Content:</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="" disabled>Select Category</option>
                    <option value="Technology" <?php if ($category == 'Technology') echo 'selected'; ?>>Technology</option>
                    <option value="Lifestyle" <?php if ($category == 'Lifestyle') echo 'selected'; ?>>Lifestyle</option>
                    <option value="Health" <?php if ($category == 'Health') echo 'selected'; ?>>Health</option>
                    <option value="Education" <?php if ($category == 'Education') echo 'selected'; ?>>Education</option>
                    <option value="Game" <?php if ($category == 'Game') echo 'selected'; ?>>Game</option>
                    <option value="Anime" <?php if ($category == 'Anime') echo 'selected'; ?>>Anime</option>
                </select>

                <label for="tags">Tags:</label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($post['tags']); ?>">

                <button type="submit">Update Post</button>
            </form>

            <a href="personalblog.php" class="cancel-btn">Cancel</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 My Blog. All rights reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        <div class="social-icons">
            <img src="../images/fb.png" width="15px" height="15px"><a href="#">Facebook</a>
            <img src="../images/twitter.png" width="15px" height="15px"><a href="#">Twitter</a>
            <img src="../images/instagram.png" width="15px" height="15px"><a href="#">Instagram</a>
        </div>
    </footer>
</body>
</html>
