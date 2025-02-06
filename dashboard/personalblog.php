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

// Fetch the current logged-in user's username from session
$current_user = $_SESSION['username'];

try {
    // Prepare SQL query to fetch posts where the author is the current user
    $query = "SELECT * FROM posts WHERE author = :author";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':author', $current_user);
    $stmt->execute();

    // Fetch all the posts by the current user
    $user_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Personal Blog</title>
    <link rel="stylesheet" href="personalblog.css"> <!-- Your CSS file -->
</head>
<body>
<header>
        <div class="logo">My Blog</div>
        <nav>
            <a href="../dashboard/dashboard.php">Dashboard</a>
            <a href="../viewpost/viewpost.php">Blogs</a>
            <a href="about.php">About</a>
            <a href="../logout/logout.php">Logout</a>
        </nav>
    </header>
    <h1>Welcome, <?php echo htmlspecialchars($current_user); ?>! Here are your blogs:</h1>
    <div class="activity-list">
        <?php if (empty($user_posts)): ?>
            <p>No posts found.</p>
        <?php else: ?>
            <div class="posts-container">
                <?php foreach ($user_posts as $post): ?>
                    <div class="post-card">
                        <div class="author-info">
                                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture" width="50px" height="50px">                           
                            <a class="pfp_user" href="viewuser.php?username=<?php echo urlencode($post['author']); ?>">
                                <?php echo htmlspecialchars($post['author']); ?>
                            </a>
                        </div>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="../viewpost/uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Featured Image" class="featured-image">
                        <?php endif; ?>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?></p>
                        <p><strong>Tags:</strong> <?php echo htmlspecialchars($post['tags']); ?></p>
                        <div class="post-content">
                            <?php
                            $content = htmlspecialchars($post['content']);
                            if (strlen($content) > 10):
                                $short_content = substr($content, 0, 10);
                            ?>
                                <span class="content-short"><?php echo nl2br($short_content); ?>...</span>
                                <span class="content-full"><?php echo nl2br($content); ?></span>
                                <a class="see-more">See more</a>
                                <div class="edit_post">
                                <a href="editpost.php?id=<?php echo $post['id']; ?>">Edit</a> |
                                <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                </div>
                            <?php else: ?>
                                <span class="content-full"><?php echo nl2br($content); ?></span>
                            <?php endif; ?>
                        </div>
                        <p><small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></small></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>




    <?php if ($user_posts): ?>
        <ul>
            <?php foreach ($user_posts as $post): ?>
                <li>
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                    <a href="editpost.php?id=<?php echo $post['id']; ?>">Edit</a> |
                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No blogs found. <a href="create_blog.php">Create your first blog post!</a></p>
    <?php endif; ?>
    <footer>
        <p>&copy; 2024 My Blog. All rights reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        <div class="social-icons">
            <img src="../images/fb.png" width="15px" height="15px"><a href="#">Facebook</a>
            <img src="../images/twitter.png" width="15px" height="15px"><a href="#">Twitter</a>
            <img src="../images/instagram.png" width="15px" height="15px"><a href="#">Instagram</a>
        </div>
    </footer>
</body>
</html>
