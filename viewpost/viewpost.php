<?php
session_start();
include('../db.php');

// Initialize variables for search
$search_query = '';
$category_filter = '';
$tags_filter = '';
$user_search = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get search input
    if (!empty($_GET['search'])) {
        $search_query = trim($_GET['search']);
    }

    // Get category filter
    if (!empty($_GET['category'])) {
        $category_filter = trim($_GET['category']);
    }

    // Get tags filter
    if (!empty($_GET['tags'])) {
        $tags_filter = trim($_GET['tags']);
    }

    // Get user filter
    if (!empty($_GET['user_search'])) {
        $user_search = trim($_GET['user_search']); // Corrected from 'search_user'
    }
}

// Prepare the SQL query with search and filter options
$sql = "SELECT p.*, u.pfpimg FROM posts p 
        LEFT JOIN users u ON p.author = u.username
        WHERE 1=1"; // 1=1 to simplify appending conditions

// Append search conditions
if (!empty($search_query)) {
    $sql .= " AND (p.title LIKE :search OR p.content LIKE :search)";
}

// Append category filter if provided
if (!empty($category_filter)) {
    $sql .= " AND p.category = :category";
}

// Append tags filter if provided
if (!empty($tags_filter)) {
    $sql .= " AND p.tags LIKE :tags";
}

// Append user search filter if provided
if (!empty($user_search)) {
    $sql .= " AND u.username LIKE :user_search";
}

// Order posts by created_at in descending order
$sql .= " ORDER BY p.created_at DESC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if applicable
if (!empty($search_query)) {
    $stmt->bindValue(':search', '%' . $search_query . '%');
}
if (!empty($category_filter)) {
    $stmt->bindValue(':category', $category_filter);
}
if (!empty($tags_filter)) {
    $stmt->bindValue(':tags', '%' . $tags_filter . '%');
}
if (!empty($user_search)) {
    $stmt->bindValue(':user_search', '%' . $user_search . '%');
}

// Execute the statement

// Execute the query
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

 // Fetch user details
$stmt = $conn->prepare("SELECT username, pfpimg FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


//profile from session
$username = $_SESSION['username'];
$pfp_query = "SELECT pfpimg FROM users WHERE username = :username";
$stmt = $conn->prepare($pfp_query);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();

//profile fetch
$pfpresult = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pfpresult && !empty($pfpresult['pfpimg'])) {
    $profilePicture = $pfpresult['pfpimg'];
} else {
    echo "No profile picture found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts - My Blog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --sidebar-color: rgb(221, 221, 221);
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f5f6fa;
            color: var(--text-color);
        }

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--sidebar-color);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            width: 240px;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .profile-section {
            text-align: center;
            margin: 2rem 0;
        }

        .profile-image img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: background-color 0.3s;
        }

        .sidebar-nav a i {
            margin-right: 1rem;
            width: 20px;
        }

        .sidebar-nav a:hover {
            background-color: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 350px;
            margin-right: 80px;
            padding: 2rem;
            width: calc(100% - 280px);
            box-sizing: border-box;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .search-form {
            background-color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            box-shadow: var(--card-shadow);
        }

        .search-form input,
        .search-form select {
            width: 250px;
            padding: 0.8rem;
            border: 1px solid #333;
            border-radius: 8px;
            background-color: white;
            color: var(--text-color);
        }

        .btn-search {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            width: 200px;
        }
        .posts-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(800px, 1fr));
            gap: 1.5rem;
            
        }

        .post-card {
            background-color:  #f0f3f4;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .profile-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin: 1rem 0;
        }
        /*see more*/
        .content-full {
    display: none; /* Hide full content by default */
}

    </style>
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
                <?php if ($profilePicture): ?>
                    <img src="../dashboard/<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
                <?php else: ?>
                    <img src="../viewpost/profile/user.png">
                <?php endif ?>
                </div>
                <h2 class="username"><?php echo $_SESSION['username']; ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard/dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                <a href="../createpost/createpost.php"><i class="fas fa-pen"></i>Create Post</a>
                <a href="../dashboard/profile2.php"><i class="fas fa-user"></i>Profile</a>
                <a href="../dashboard/personalblog2.php"><i class="fas fa-book"></i>Your Posts</a>
                <a href="../chat/chatlist.php"><i class="fas fa-comments"></i>Chat</a>
                <a href="../dashboard/friend_requests.php"><i class="fas fa-user-plus"></i>Requests</a>
                <a href="about.php"><i class="fas fa-info-circle"></i>About</a>
                <a href="contact.php"><i class="fas fa-envelope"></i>Contact</a>
                <a href="../index.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket fa-flip-horizontal"></i>Logout</a>
                
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Blog Posts</h1>
            </div>

            <!-- Search Form -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="search-form">
                <input class="search_posts" type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search_query); ?>">
                <input type="text" name="user_search" placeholder="Search Friends" value="<?php echo htmlspecialchars($user_search); ?>">
                <select name="category" class="choice">
                    <option value="">All Categories</option>
                    <option value="Technology" <?php if($category_filter == 'Technology') echo 'selected'; ?>>Technology</option>
                    <option value="Lifestyle" <?php if($category_filter == 'Lifestyle') echo 'selected'; ?>>Lifestyle</option>
                    <option value="Health" <?php if($category_filter == 'Health') echo 'selected'; ?>>Health</option>
                    <option value="Education" <?php if($category_filter == 'Education') echo 'selected'; ?>>Education</option>
                    <option value="Game" <?php if($category_filter == 'Game') echo 'selected'; ?>>Game</option>
                    <option value="Anime" <?php if($category_filter == 'Anime') echo 'selected'; ?>>Anime</option>
                </select>
                <input type="text" name="tags" placeholder="Filter by tags..." value="<?php echo htmlspecialchars($tags_filter); ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>

            <!-- Posts Container -->
            <?php if (empty($posts)): ?>
                <p>No posts found.</p>
            <?php else: ?>
                <div class="posts-container">
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <div class="author-info">
                                <?php if (!empty($post['pfpimg'])): ?>
                                    <img src="../dashboard/<?php echo htmlspecialchars($post['pfpimg']); ?>" alt="Profile Picture" class="profile-picture">
                                <?php else: ?>
                                    <img src="profile/user.png" alt="Default Profile Picture" class="profile-picture">
                                <?php endif; ?>
                                <a href="viewuser2.php?username=<?php echo urlencode($post['author']); ?>">
                                    <?php echo htmlspecialchars($post['author']); ?>
                                </a>
                            </div>
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <?php if (!empty($post['featured_image'])): ?>
                                <a href="./uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" target="_blank">
                                    <img src="./uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Featured Image" class="featured-image">
                                </a> 
                            <?php endif; ?>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?></p>
                            <p><strong>Tags:</strong> <?php echo htmlspecialchars($post['tags']); ?></p>
                            <div class="post-content">
                                <?php
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
                                <?php endif; ?>
                            </div>
                            <p><small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></small></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <div id="image-viewer" class="hidden">
        <span id="close-btn">&times;</span>
        <img id="viewer-img" src="" alt="Full View">
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