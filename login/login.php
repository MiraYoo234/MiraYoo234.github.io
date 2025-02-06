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

    // Validate the form
    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        // Check if the user exists in the database
        try {
            // Ensure $conn is properly initialized
            if (!$conn) {
                throw new Exception("Database connection error.");
            }

            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // If the user exists, verify the password
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, store username in session and log in the user
                $_SESSION['username'] = $user['username']; // Store username in session
                $_SESSION['user_id'] = $user['id']; // Optionally store the user ID
                header("Location: ../viewpost/viewpost.php"); // Redirect to dashboard after login
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
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
    <title>Login - My Blog</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="dark-theme">
    <!-- Header with navigation -->
    <header>
        <div class="logo">My Blog</div>
        <nav>
            <a href="../index.php">Home</a>
            <a href="../viewpost/viewpost.php">Blogs</a>
            <a href="about.php">About</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <div class="login-container">
        <div class="form-box">
            <h2>Login</h2>

            <!-- Display error message -->
            <?php if (!empty($error)): ?>
                <div class="error-messages">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="post">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <p class="register-redirect">Don't have an account? <a href="register.php">Register</a></p>
            <p class="reset-redirect">Forgot password? <a href="reset_request.php">Reset Password</a></p>w
        </div>
    </div>

    <!-- Footer -->
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
