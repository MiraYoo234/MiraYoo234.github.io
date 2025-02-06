<?php
session_start();
include '../dashboard/db2.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Fetch users from the database
$users = [];

// Check if a search term is provided
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare SQL query
if ($searchTerm) {
    $query = "SELECT * FROM users WHERE username LIKE :search";
    $stmt = $pdo->prepare($query);
    $searchTerm = "%$searchTerm%";
    $stmt->bindParam(':search', $searchTerm);
} else {
    $query = "SELECT * FROM users";
    $stmt = $pdo->prepare($query);
}

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users</title>
    <link rel="stylesheet" href="styles.css"> <!-- Your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-box input {
            padding: 10px;
            width: 300px;
        }
        .user-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .user-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin: 10px;
            padding: 10px;
            text-align: center;
            width: 200px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .user-card img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .user-card h3 {
            font-size: 18px;
            margin: 10px 0;
        }
        .user-card a {
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Search Users</h1>
    </div>
    <div class="search-box">
        <form action="searchuser.php" method="GET">
            <input type="text" name="search" placeholder="Search by username..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="user-list">
        <?php if ($users): ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <a href="viewuser.php?username=<?php echo htmlspecialchars($user['username']); ?>">
                    <img src="<?php echo !empty($user['pfpimg']) ? htmlspecialchars($user['pfpimg']) : 'profile/user.png'; ?>" alt="<?php echo htmlspecialchars($user['username']); ?>'s profile picture">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
