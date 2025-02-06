<?php
// Start the session
session_start();

// Include database configuration file (Assuming you have db.php for database connection)
include('../db.php');

// Initialize error and success variables
$error = "";
$success = "";

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate the form
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into the database using PDO
        try {
            // Prepare the SQL query
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            
            // Execute the query
            $stmt->execute();

            // Registration successful
            $success = "Registration successful!";
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
    <title>Register - My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dark-theme">
<header>
        <div class="logo">My Blog</div>
        <nav>
            <a href="../index.php">Home</a>
            <a href="features.php">Features</a>
            <a href="about.php">About</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
</header>
    <div class="register-container">
        <div class="form-box">
            <h2>Register</h2>

            <!-- Display error message -->
            <?php if (!empty($error)): ?>
                <div class="error-messages">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <!-- Display success message -->
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="register.php" method="post">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-register">Register</button>
            </form>
            <p class="login-redirect">Already have an account? <a href="../login/login.php">Log In</a></p>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 My Blog. All rights reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        <div class="social-icons">
            <a href="#">Facebook</a>
            <a href="#">Twitter</a>
            <a href="#">Instagram</a>
        </div>
    </footer>
</body>
</html>
