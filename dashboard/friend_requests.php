<?php
session_start();
include('../db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// Fetch pending friend requests
$stmt = $conn->prepare("
    SELECT f.id as friendship_id, u.username, u.pfpimg, f.created_at
    FROM friendships f
    JOIN users u ON u.id = f.user_id
    WHERE f.friend_id = :user_id 
    AND f.status = 'pending'
    ORDER BY f.created_at DESC
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$friend_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

//logged-in profile fetch
try {
    $origin_user = $_SESSION['username'];

    //SQL Query
    $origin_user_stmt = $conn->prepare("SELECT pfpimg FROM users WHERE username = :origin_username");
    $origin_user_stmt->bindParam(':origin_username', $origin_user);
    $origin_user_stmt->execute();
    $profile_link = $origin_user_stmt->fetch(PDO::FETCH_ASSOC);
    $origin_profile_link = $profile_link['pfpimg'];
} catch (PDOException $e) {
    echo $e;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="friend_requests.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">My Blog</h1>
            </div>
            <div class="profile-section">
            <?php if (!empty($profile_link['pfpimg'])): ?>
                    <div class="profile-picture">
                        <img src="<?php echo htmlspecialchars($origin_profile_link); ?>" alt="Profile Picture" class="current-img" width="120px" height="120px">
                    </div>
                <?php endif; ?>
                <h2 class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../viewpost/viewpost.php"><i class="fas fa-blog"></i>Blogs</a>
                <a href="profile2.php"><i class="fas fa-user"></i>Profile</a>
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
        <main class="main-content">
        <h1>Friend Requests</h1>
    <div class="friend-requests-container">
        <?php if ($friend_requests): ?>
            <table class="friend-requests-table">
                <?php foreach ($friend_requests as $request): ?>
                    <tr class="friend-request-row">
                        <td class="pfp-cell">
                            <img class="pfp" src="<?php echo !empty($request['pfpimg']) ? htmlspecialchars($request['pfpimg']) : '../dashboard/profile/user.png'; ?>" 
                                 alt="Profile picture">
                        </td>
                        <td class="username-cell">
                            <a class="uname" href="../viewpost/viewuser2.php?username=<?php echo htmlspecialchars($request['username']); ?>">
                                <?php echo htmlspecialchars($request['username']); ?>
                            </a>
                        </td>
                        <td class="action-cell-1">
                            <form action="../viewpost/handle_friend_request.php" method="POST">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="friendship_id" value="<?php echo $request['friendship_id']; ?>">
                                <button type="submit" class="btn-accept">Accept</button>
                            </form>
                        </td>
                        <td class="action-cell-2">
                            <form action="handle_friend_request.php" method="POST">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="friendship_id" value="<?php echo $request['friendship_id']; ?>">
                                <button type="submit" class="btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="no-requests">No pending friend requests.</p>
        <?php endif; ?>
    </div>
    </main>"
</div>
</body>
</html>



