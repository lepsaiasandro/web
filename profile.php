<?php
session_start();

// Redirect if not authenticated
if (!isset($_SESSION["user"]) || !isset($_SESSION["email"])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$current_user = null;
$email = $_SESSION["email"];
$hashedPass = ''; // Initialize hashedPass variable
$users = [];

// Load user data with error handling
if (file_exists("users.txt")) {
    $users = file("users.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($users as $user) {
        $fields = explode("|", $user);
        if (count($fields) >= 4 && $fields[2] === $email) {
            $current_user = [
                'name' => $fields[0] ?? '',
                'age' => $fields[1] ?? '',
                'email' => $fields[2] ?? '',
                'password' => $fields[3] ?? ''
            ];
            $hashedPass = $fields[3]; // Store the hashed password for verification
            break;
        }
    }
}

// If user not found, log them out
if (!$current_user) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? '');
    $age = intval($_POST["age"] ?? 0);
    $current_password = $_POST["current_password"] ?? '';
    $new_password = $_POST["new_password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';
    
    if (empty($name) || $age < 16 || $age > 99) {
        $error = "Invalid name or age";
    } 
    elseif (!empty($new_password)) {
        if (empty($current_password)) {
            $error = "Please enter current password";
        } 
        elseif (!password_verify($current_password, $hashedPass)) {
            $error = "Current password is incorrect";
        }
        elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        }
        elseif (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters";
        }
    }

    if (empty($error)) {
        $updated_users = [];
        $updated = false;
        
        foreach ($users as $user) {
            $fields = explode("|", $user);
            if (isset($fields[2]) && $fields[2] === $email) {
                $password_to_store = !empty($new_password) ? 
                    password_hash($new_password, PASSWORD_DEFAULT) : 
                    $hashedPass;
                
                $updated_users[] = implode("|", [
                    $name,
                    $age,
                    $email,
                    $password_to_store
                ]);
                $updated = true;
            } else {
                $updated_users[] = $user;
            }
        }
        
        if ($updated) {
            if (file_put_contents("users.txt", implode(PHP_EOL, $updated_users))) {
                $_SESSION["user"] = $name;
                $success = "Profile updated successfully!";
                $current_user['name'] = $name;
                $current_user['age'] = $age;
            } else {
                $error = "Failed to update profile (file write error)";
            }
        } else {
            $error = "Failed to update profile (user not found)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - TSU Jobs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
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
            <a href="profile.php" class="nav-link active">Profile</a>
            <a href="about.php" class="nav-link">About us</a>
            <a href="logout.php" class="nav-link">Log out</a>
        </nav>
    </section>

    <main class="main">
        <section class="profile-container">
            <h1 class="welcome">My Profile</h1>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form class="profile-form" method="POST">
                <div class="profile-header">
                    <h2><?php echo htmlspecialchars($current_user['name'] ?? ''); ?></h2>
                    <p>Member since <?php echo date('F Y', strtotime('-3 months')); ?></p>
                </div>
                
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-row">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="age">Age:</label>
                        <input type="number" id="age" name="age" min="16" max="99"
                               value="<?php echo htmlspecialchars($current_user['age'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <label>Email:</label>
                        <p class="static-value"><?php echo htmlspecialchars($current_user['email'] ?? ''); ?></p>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Change Password</h3>
                    <div class="form-row">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-row">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-row">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="save-button">Save Changes</button>
                </div>
            </form>
        </section>
    </main>
    
    <footer class="footer">
        <p class="footer-text">&copy; Website from Sandro Lepsaia</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>