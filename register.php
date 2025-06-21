<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $age = intval($_POST["age"]);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm-password"];
    $terms = isset($_POST["terms"]) ? true : false;
    
    $errors = [];
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords don't match";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($age < 16 || $age > 99) {
        $errors[] = "Age must be between 16 and 99";
    }
    
    if (!$terms) {
        $errors[] = "You must agree to the terms of service";
    }

    // Check if email exists
    if (empty($errors)) {
        if (file_exists("users.txt")) {
            $users = file("users.txt", FILE_IGNORE_NEW_LINES);
            foreach ($users as $user) {
                $fields = explode("|", $user);
                if (isset($fields[2]) && $fields[2] === $email) {
                    $errors[] = "Email already registered";
                    break;
                }
            }
        }
    }

    if (!empty($errors)) {
        die("❌ " . implode("<br>❌ ", $errors) . " <a href='register.html'>Try again</a>.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $user_data = "$name|$age|$email|$hashed_password" . PHP_EOL;
    file_put_contents("users.txt", $user_data, FILE_APPEND);
    
    header("Location: login.php?registered=1");
    exit;
} else {
    header("Location: register.html");
    exit;
}
?>