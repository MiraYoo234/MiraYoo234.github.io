<?php
// Add this after your existing session_start() and database connection

// Get the viewed user's ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt->bindParam(':username', $username);
$stmt->execute();
$viewed_user = $stmt->fetch(PDO::FETCH_ASSOC);
$viewed_user_id = $viewed_user['id'];

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

// Add this inside your HTML where you want to display the friend button
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