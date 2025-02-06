<?php
// Start the session
session_start();
include 'db2.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Fetch the profile picture from the database
try {
    $current_user = $_SESSION['username'];
    $query = "SELECT pfpimg FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $current_user);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle the error, e.g., log it and show a user-friendly message
    error_log("Database error: " . $e->getMessage());
    $user = ['pfpimg' => null]; // Default to no image
}

// Set default profile picture if not available
$profile_picture = !empty($user['pfpimg']) ? $user['pfpimg'] : 'profile/user.png';

try {
    $count_query = $pdo->prepare("SELECT COUNT(*) AS post_count FROM posts WHERE author = :username");
    $count_query->bindParam(':username', $current_user, PDO::PARAM_STR);
    $count_query->execute();

    $result = $count_query->fetch(PDO::FETCH_ASSOC);
    $post_count = $result['post_count'];
}
 catch(PDOException $e) {

}
//follower count
try {
    //fetch user id
    $friend_query = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $friend_query->bindParam(':username', $current_user, PDO::PARAM_STR);
    $friend_query->execute();
    $friend_result = $friend_query->fetch(PDO::FETCH_ASSOC);
    $friend_result_part1 = $friend_result['id'];
    echo $friend_result_part1;
    //fetch user count associted to that id
    $friend_count_query = $pdo->prepare("SELECT COUNT(*) AS friend_count FROM friendships WHERE user_id = '$friend_result_part1' OR friend_id = '$friend_result_part1'");
    $friend_count_query->execute();
    $friend_count_result = $friend_count_query->fetch(PDO::FETCH_ASSOC);
    $friend_count_result_final = $friend_count_result['friend_count'];
    echo $friend_count_result_final;
} catch(PDOException $e) {

}
//activity log
try {
    $user_post_query = $pdo->prepare("SELECT * FROM posts WHERE author = :author");
    $user_post_query->bindParam(':author', $current_user, PDO::PARAM_STR);
    $user_post_query->execute();
    $user_posts = $user_post_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $user_posts = []; // Default to empty array on error
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Blog</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">My Blog</h1>
            </div>
            <div class="profile-section">
                <div class="profile-image">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="user_profile">
                </div>
                <h2 class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../viewpost/viewpost.php"><i class="fas fa-blog"></i>Blogs</a>
                <a href="profile2.php"><i class="fas fa-user"></i>Profile</a>
                <a href="../createpost/createpost.php"><i class="fas fa-pen"></i>Create Post</a>
                <a href="personalblog2.php"><i class="fas fa-book"></i>Your Posts</a>
                <a href="../chat/chatlist.php"><i class="fas fa-comments"></i>Chat</a>
                <a href="friend_requests.php"><i class="fas fa-user-plus"></i>Requests</a>
                <a href="about.php"><i class="fas fa-info-circle"></i>About</a>
                <a href="contact.php"><i class="fas fa-envelope"></i>Contact</a>
                <a href="../index.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket fa-flip-horizontal"></i>Logout</a>
            </nav>
            <!-- <div class="sidebar-footer">
                <a href="../logout/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>Logout
                </a>
            </div> -->
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <button class="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </header>

            <div class="dashboard-grid">
                <div class="stats-card">
                    <i class="fas fa-pencil-alt"></i>
                    <h3>Your Posts</h3>
                    <p class="stats-number"><?php echo $post_count?></p>
                </div>
                <div class="stats-card">
                    <i class="fas fa-users"></i>
                    <h3>Followers</h3>
                    <p class="stats-number"><?php echo $friend_count_result_final ?></p>
                </div>
                <div class="stats-card">
                    <i class="fas fa-heart"></i>
                    <h3>Likes</h3>
                    <p class="stats-number">0</p>
                </div>
                <div class="stats-card">
                    <i class="fas fa-comment"></i>
                    <h3>Comments</h3>
                    <p class="stats-number">0</p>
                </div>
            </div>

            <div class="recent-activity">
    <h2>Recent Activity</h2>
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
                            <a href="../viewpost/uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" target="_blank">
                                    <img src="../viewpost/uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Featured Image" class="featured-image">
                                </a> 
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
