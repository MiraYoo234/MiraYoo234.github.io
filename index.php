<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Welcome to My Blog</title>
</head>
<body>
    <header>
        <div class="logo">My Blog</div>
        <nav>
            <a href="index.php" id="home_nav">Home</a>
            <a href="viewpost/viewpost.php" id="home_nav">Features</a>
            <a href="about.php" id="home_nav">About</a>
            <a href="login.php" id="home_nav">Login</a>
            <a href="register.php" id="home_nav">Register</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Join the Next Generation of Bloggers!</h2>
            <p>Share your thoughts, ideas, and passions with the world.</p>
            <div class="cta-buttons">
                <button onclick="window.location.href='./register/register.php'">Get Started</button>
                <button onclick="window.location.href='./login/login.php'">Log In</button>
            </div>
        </section>

        <section class="features">
            <h2>Why Choose Us?</h2>
            <div class="feature-grid">
                <div class="feature">
                    <img src="./images/ui.png" alt="User-Friendly Interface" width="150px" height="150px">
                    <h3>User-Friendly Interface</h3>
                    <p>Easily navigate and create amazing content.</p>
                </div>
                <div class="feature">
                    <img src="./images/profile.png" alt="Customizable Profiles" width="150px" height="150px">
                    <h3>Customizable Profiles</h3>
                    <p>Make your profile uniquely yours.</p>
                </div>
                <div class="feature">
                    <img src="./images/social-media.png" alt="Engaging Community" width="150px" height="150px">
                    <h3>Engaging Community</h3>
                    <p>Connect with fellow bloggers and readers.</p>
                </div>
                <div class="feature">
                    <img src="./images/content-creator.png" alt="Rich Text Editor" width="150px" height="150px">
                    <h3>Rich Text Editor</h3>
                    <p>Create stunning posts with ease.</p>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <h2>What Our Users Say</h2>
            <div class="testimonial-carousel">
                <div class="testimonial">
                    <p>"This platform changed the way I blog!"</p>
                    <cite>- Jane Doe</cite>
                </div>
                <div class="testimonial">
                    <p>"I love connecting with other writers."</p>
                    <cite>- John Smith</cite>
                </div>
            </div>
        </section>

        <section class="cta">
            <h2>Ready to start your blogging journey?</h2>
            <button onclick="window.location.href='./register/register.php'">Join Now!</button>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 My Blog. All rights reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        <div class="social-icons">
            <img src="images/fb.png" width="15px" height="15px"><a href="#">Facebook</a>
            <img src="images/twitter.png" width="15px" height="15px"><a href="#">Twitter</a>
            <img src="images/instagram.png" width="15px" height="15px"><a href="#">Instagram</a>
        </div>
    </footer>
</body>
</html>
