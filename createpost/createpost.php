<?php
session_start();
include('../db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$title = '';
$content = '';
$category = '';
$tags = '';
$featured_image = '';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category = trim($_POST["category"]);
    $tags = trim($_POST["tags"]);
    
    // Handle file upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $uploads_dir = '../viewpost/uploads/';
        $tmp_name = $_FILES['featured_image']['tmp_name'];
        $name = basename($_FILES['featured_image']['name']);
        $target_file = $uploads_dir . $name;

        // Check if the file is an image
        $check = getimagesize($tmp_name);
        if ($check === false) {
            $error = "Uploaded file is not an image.";
        } elseif (!move_uploaded_file($tmp_name, $target_file)) {
            $error = "Failed to upload image.";
        } else {
            $featured_image = $name; // Store the image name
        }
    }

    // Validate form fields
    if (empty($title) || empty($content) || empty($category) || empty($tags)) {
        $error = "All fields are required.";
    } else {
        try {
            $sql = "INSERT INTO posts (title, content, author, category, tags, featured_image, created_at) 
                    VALUES (:title, :content, :author, :category, :tags, :featured_image, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':author', $_SESSION['username']);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':tags', $tags);
            $stmt->bindParam(':featured_image', $featured_image);
            $stmt->execute();

            $success = "Post created successfully!";
            $title = '';
            $content = '';
            $category = '';
            $tags = '';
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - My Blog</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
</head>
<body class="dark-theme">
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="nav-link">Home</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="contact.php" class="nav-link">Contact</a>
            <a href="view_posts.php" class="nav-link">View Posts</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </header>

    <div class="form-container">
        <h2>Create a New Post</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea name="content" id="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
                
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required>
                    <option value="">Select Category</option>
                    <option value="Technology" <?php if($category == 'Technology') echo 'selected'; ?>>Technology</option>
                    <option value="Lifestyle" <?php if($category == 'Lifestyle') echo 'selected'; ?>>Lifestyle</option>
                    <option value="Health" <?php if($category == 'Health') echo 'selected'; ?>>Health</option>
                    <option value="Education" <?php if($category == 'Education') echo 'selected'; ?>>Education</option>
                    <option value="Game" <?php if($category == 'Game') echo 'selected'; ?>>Game</option>
                    <option value="Anime" <?php if($category == 'Anime') echo 'selected'; ?>>Anime</option>
        <!-- Add more categories as needed -->
                </select>
            </div>


            <div class="form-group">
                <label for="tags">Tags (comma separated)</label>
                <input type="text" name="tags" id="tags" value="<?php echo htmlspecialchars($tags); ?>" required>
            </div>

            <div class="form-group">
                <label for="featured_image">Featured Image</label>
                <input type="file" name="featured_image" id="featured_image" accept="image/*" required>
            </div>

            <button type="submit" class="btn-submit">Create Post</button>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; 2024 My Blog. All Rights Reserved.</p>
    </footer>
</body>
</html>
