<?php
session_start();
include('../db.php');

// Get the username from the query string
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Get the viewed user's ID
    $stmt = $conn->prepare("SELECT id, username, pfpimg FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $viewed_user_id = $user['id'];

    // Check friendship status
    $stmt = $conn->prepare("
        SELECT * FROM friendships 
        WHERE (user_id = :user_id AND friend_id = :friend_id)
        OR (user_id = :friend_id AND friend_id = :user_id)
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':friend_id', $viewed_user_id);
    $stmt->execute();
    $friendship = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch user blogs
    $stmt2 = $conn->prepare("SELECT * FROM posts WHERE author = :author ORDER BY created_at DESC");
    $stmt2->bindParam(':author', $username);
    $stmt2->execute();
    $posts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Redirect if no username provided
    header("Location: viewpost.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .header {
    background-color: #1f1f1f;
    padding: 15px;
    text-align: center;
}

.navbar .nav-link {
    color: #fff;
    margin: 0 15px;
    text-decoration: none;
}

.navbar .nav-link:hover {
    color: #00aaff;
}
.friend-action {
    margin: 20px 0;
    text-align: center;
}

.btn-add-friend, .btn-accept, .btn-reject {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin: 0 5px;
}

.btn-add-friend {
    background-color: #4CAF50;
    color: white;
}

.btn-pending, .btn-friends {
    background-color: #888;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
}

.btn-accept {
    background-color: #4CAF50;
    color: white;
}

.btn-reject {
    background-color: #f44336;
    color: white;
}
    </style>
</head>
<body class="dark-theme">
<header class="header">
        <nav class="navbar">
            <a href="../viewpost/viewpost.php" class="nav-link">Home</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="contact.php" class="nav-link">Contact</a>
            <a href="../createpost/createpost.php" class="nav-link">Create Post</a>
            <a href="../logout/logout.php" class="nav-link">Logout</a>
            <a style="text-decoration: none;"  href="../dashboard/dashboard.php"><?php echo $_SESSION['username']; ?></a>
        </nav>
    </header>
    <div class="container">
        <h2><?php echo htmlspecialchars($user['username']); ?>'s Profile</h2>

        <!-- Display user profile picture -->
        <div class="profile-picture">
            <img src="<?php echo !empty($user['pfpimg']) ? htmlspecialchars($user['pfpimg']) : 'profile/user.png'; ?>" alt="Profile Picture" style="width:150px; height:150px; object-fit: cover; border-radius: 50%;">
        </div>

        <h3>Blog Posts by <?php echo htmlspecialchars($user['username']); ?></h3>

        <?php
        // Add friend button code here - after the header but before displaying posts
        if ($username !== $_SESSION['username']): // Don't show friend button for own profile
            echo '<div class="friend-action">';
            if (!$friendship) {
                echo '<form action="add_friend.php" method="POST">
                        <input type="hidden" name="friend_id" value="' . $viewed_user_id . '">
                        <button type="submit" class="btn-add-friend">Add Friend</button>
                      </form>';
            } elseif ($friendship['status'] === 'pending') {
                if ($friendship['user_id'] == $_SESSION['user_id']) {
                    echo '<button class="btn-pending" disabled>Friend Request Sent</button>';
                } else {
                    echo '<div class="friend-request-actions">
                            <form action="handle_friend_request.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="friendship_id" value="' . $friendship['id'] . '">
                                <button type="submit" class="btn-accept">Accept</button>
                            </form>
                            <form action="handle_friend_request.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="friendship_id" value="' . $friendship['id'] . '">
                                <button type="submit" class="btn-reject">Reject</button>
                            </form>
                          </div>';
                }
            } else {
                echo '<button class="btn-friends" disabled>Friends</button>';
            }
            echo '</div>';
        endif;
        ?>

        <?php if (empty($posts)): ?>
            <p>No blog posts found.</p>
        <?php else: ?>
            <div class="posts-container">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="./uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Post Image" class="featured-image" style="max-width:100%; height:auto;">
                        <?php endif; ?>
                        <div><?php
                            $content = htmlspecialchars($post['content']);
                            // Check if the content length is greater than 300 characters
                            if (strlen($content) > 10):
                                // Shorten the content to 300 characters
                                $short_content = substr($content, 0, 10);
                            ?>
                                <span class="content-short"><?php echo nl2br($short_content); ?>...</span>
                                <span class="content-full"><?php echo nl2br($content); ?></span>
                                <a class="see-more">See more</a>
                            <?php else: ?>
                                <span class="content-full"><?php echo htmlspecialchars($content); ?></span>
                            <?php endif; ?></div>
                        <p><small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></small></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 My Blog. All Rights Reserved.</p>
    </footer>
    <script>
        // JavaScript for the "see more" functionality
        document.querySelectorAll('.see-more').forEach(function(seeMoreLink) {
            seeMoreLink.addEventListener('click', function() {
                const postContent = this.previousElementSibling; // Get the full content
                const shortContent = this.previousElementSibling.previousElementSibling; // Get the short content
                if (postContent.style.display === 'none') {
                    postContent.style.display = 'inline';
                    shortContent.style.display = 'none';
                    this.innerHTML = 'See less';
                } else {
                    postContent.style.display = 'none';
                    shortContent.style.display = 'inline';
                    this.innerHTML = 'See more';
                }
            });
        });
    </script>
</body>
</html>
