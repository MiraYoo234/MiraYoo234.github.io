<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db2.php';  // This should have the PDO connection

// Fetch the current logged-in user's username from session
$current_user = $_SESSION['username'];
$message = "";

// Fetch current user details
try {
    $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $current_user);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}   
try {
    $pfp_get_query = "SELECT pfpimg FROM users WHERE username = :username";
    $pfpstmt = $pdo->prepare($pfp_get_query);
    $pfpstmt->bindParam(':username', $profile_picture);
    $pfpstmt->execute();
    $pfp = $pfpstmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "profile/";
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allow only certain file formats
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            // Upload the file
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                // Update profile picture in the database
                try {
                    $sql = "UPDATE users SET pfpimg = :profile_picture WHERE username = :username";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':profile_picture', $target_file);
                    $stmt->bindParam(':username', $current_user);
                    $stmt->execute();

                    $message = "Profile picture updated successfully!";

                } catch (PDOException $e) {
                    $message = "Error updating profile picture: " . $e->getMessage();
                }
            } else {
                $message = "Error uploading the file.";
            }
        } else {
            $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        $message = "No file uploaded.";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile2.css">
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
            <?php if (!empty($user['pfpimg'])): ?>
                    <div class="profile-picture">
                        <img src="<?php echo htmlspecialchars($user['pfpimg']); ?>" alt="Profile Picture" class="current-img" width="120px" height="120px">
                    </div>
            <?php else: ?>
                <div class="profile-picture">
                        <img src="../dashboard/profile/user.png" class="current-img"width="120px" height="120px">
                </div>
                <?php endif; ?>
                <h2 class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../viewpost/viewpost.php"><i class="fas fa-blog"></i>Blogs</a>
                <a href="profile.php"><i class="fas fa-user"></i>Profile</a>
                <a href="../createpost/createpost.php"><i class="fas fa-pen"></i>Create Post</a>
                <a href="personalblog.php"><i class="fas fa-book"></i>Your Posts</a>
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
        <div class="profile-card">
            <h1>Edit Profile Picture</h1>

            <?php if (!empty($message)): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="profile2.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="profile_picture">Upload New Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                </div>

                <!-- Display current profile picture -->
                <?php if (!empty($user['pfpimg'])): ?>
                    <div class="profile-picture">
                        <h3>Current Profile Picture</h3>
                        <img src="<?php echo htmlspecialchars($user['pfpimg']); ?>" alt="Profile Picture" class="current-img" width="60px" height="60px">
                    </div>
                <?php endif; ?>

                <!-- Preview new profile picture -->
                <div id="imagePreview" class="preview-box"></div>

                <button type="submit" class="submit-btn">Update Profile Picture</button>
            </form>

            <a href="../dashboard/dashboard.php" class="cancel-btn">Cancel</a>
        </div>

        <div class="profile-card">
            <h1>Edit Username</h1>

            <?php if (!empty($message)): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="profile2.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="profile_picture">Upload New Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                </div>

                <!-- Display current profile picture -->
                <?php if (!empty($user['pfpimg'])): ?>
                    <div class="profile-picture">
                        <h3>Current Profile Picture</h3>
                        <img src="<?php echo htmlspecialchars($user['pfpimg']); ?>" alt="Profile Picture" class="current-img" width="60px" height="60px">
                    </div>
                <?php endif; ?>

                <!-- Preview new profile picture -->
                <div id="imagePreview" class="preview-box"></div>

                <button type="submit" class="submit-btn">Update Profile Picture</button>
            </form>

            <a href="../dashboard/dashboard.php" class="cancel-btn">Cancel</a>
        </div>

        <div class="profile-card">
            <h1>Delete Account</h1>

            <?php if (!empty($message)): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="profile2.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="profile_picture">Upload New Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                </div>

                <!-- Display current profile picture -->
                <?php if (!empty($user['pfpimg'])): ?>
                    <div class="profile-picture">
                        <h3>Current Profile Picture</h3>
                        <img src="<?php echo htmlspecialchars($user['pfpimg']); ?>" alt="Profile Picture" class="current-img" width="60px" height="60px">
                    </div>
                <?php endif; ?>

                <!-- Preview new profile picture -->
                <div id="imagePreview" class="preview-box"></div>

                <button type="submit" class="submit-btn">Update Profile Picture</button>
            </form>

            <a href="../dashboard/dashboard.php" class="cancel-btn">Cancel</a>
        </div>
        </main>
</div>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.createElement('img');
                output.src = reader.result;
                output.classList.add('preview-img');
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = '';
                preview.appendChild(output);
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
