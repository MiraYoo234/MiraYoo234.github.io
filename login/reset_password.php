<?php
// Start the session
session_start();

// Include database configuration file (db.php)
include('../db.php');

// Initialize variables for error message
$error = "";
$success = "";

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate the form
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if the email exists
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Update the user's password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = :password WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $success = "Your password has been reset successfully.";
            } else {
                $error = "Email address not found.";
            }
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
    <title>Reset Password - My Blog</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="dark-theme">
    <div class="reset-container">
        <div class="form-box">
            <h2>Reset Password</h2>

            <!-- Display error or success message -->
            <?php if (!empty($error)): ?>
                <div class="error-messages">
                    <p><?php echo $error; ?></p>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="success-messages">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <!-- Reset Password Form -->
            <form action="reset_password.php" method="post">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-reset">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>