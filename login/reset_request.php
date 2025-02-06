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

    // Validate the form
    if (empty($email)) {
        $error = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        // Check if the user exists in the database
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Email exists, provide a success message
                $success = "An email has been sent to your address with further instructions.";
            } else {
                $error = "No account found with that email.";
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

            <!-- Reset Request Form -->
            <form action="reset_request.php" method="post">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn-reset">Send Reset Link</button>
            </form>
        </div>
    </div>
</body>
</html>