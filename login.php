<?php
session_start();

// Redirect to profile if already logged in
if (isset($_SESSION['user'])) {
    header("Location: profile.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=invalid_email");
        exit;
    }

    // Check if users file exists
    if (!file_exists("users.txt")) {
        header("Location: login.php?error=no_users");
        exit;
    }

    // Check credentials
    $users = file("users.txt", FILE_IGNORE_NEW_LINES);
    $loginSuccessful = false;

    foreach ($users as $user) {
        $fields = explode("|", $user);
        if (count($fields) < 4) continue;
        
        list($name, $age, $userEmail, $hashedPass) = $fields;

        if ($userEmail === $email && password_verify($password, $hashedPass)) {
            $_SESSION["user"] = $name;
            $_SESSION["email"] = $email;
            $_SESSION["loggedin"] = true;

            // Set remember me cookie
            if ($remember) {
                setcookie("user_email", $email, [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            header("Location: profile.php");
            exit;
        }
    }

    // If we get here, login failed
    header("Location: login.php?error=invalid_credentials");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TSU Jobs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <header class="left_header">
        <img src="/images/TSU_Logo.png" alt="Tsu logo" class="logo">
        <h1 class="title">TSU Jobs</h1>
        <button id="hide-sidebar"></button> 
    </header>

    <section class="left_area">
        <nav class="main_buttons">
            <a href="index.php" class="nav-link">Home</a>
            <a href="jobs.php" class="nav-link">Jobs</a>
            <a href="login.php" class="nav-link active">Log in</a>
            <a href="register.php" class="nav-link">Register</a>
            <a href="about.php" class="nav-link">About us</a>
        </nav>
    </section>

    <main class="main">
        <aside class="main_info">
            <h1 class="welcome">Log in to TSU Jobs</h1>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert error">
                    <?php 
                    switch($_GET['error']) {
                        case 'invalid_email':
                            echo "Invalid email format";
                            break;
                        case 'no_users':
                            echo "No users registered yet";
                            break;
                        case 'invalid_credentials':
                            echo "Invalid email or password";
                            break;
                        default:
                            echo "Login failed";
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required
                        value="<?php echo isset($_COOKIE['user_email']) ? htmlspecialchars($_COOKIE['user_email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                </div>

                <div class="form-group remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="login-button">Log In</button>
                
                <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
            </form>
        </aside>
    </main>
    
    <footer class="footer">
        <p class="footer-text">&copy; Website from Sandro Lepsaia</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>