<?php
/**
 * Path Correction: 
 * If db_connect.php is in the same 'login' folder as this file, 'db_connect.php' works.
 * If it is in the root 'workack' folder, use '../db_connect.php'.
 */
$db_file = 'db_connect.php';

if (file_exists($db_file)) {
    require_once $db_file;
} else {
    die("Error: db_connect.php not found at " . __DIR__ . DIRECTORY_SEPARATOR . $db_file);
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];
        $role     = mysqli_real_escape_string($conn, $_POST['role']);

        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE username = ? AND role = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                // Redirect to dashboard (assuming it's in the root folder /workack/)
                header("Location: ../dashboard.php"); 
                exit;
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No user found with those credentials or role.";
        }
        mysqli_stmt_close($stmt);

    } elseif ($action === 'register') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $role     = mysqli_real_escape_string($conn, $_POST['role']);
        $password = $_POST['password'];
        $confirm  = $_POST['confirm_password'];

        if ($password !== $confirm) {
            $error = "Passwords do not match!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration successful! You can now login.";
            } else {
                $error = "Username already exists or database error.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Portal | Workack</title>
    <style>
        :root { --primary: #1a73e8; --primary-hover: #1557b0; --bg: #f0f2f5; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: var(--primary); margin-top: 0; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .error { color: #d93025; background: #fce8e6; border: 1px solid #f5c2c7; }
        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; }
        input, select { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        button { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 10px; }
        button:hover { background: var(--primary-hover); }
        .toggle-btn { text-align: center; margin-top: 20px; font-size: 14px; color: #555; }
        .toggle-btn span { color: var(--primary); cursor: pointer; font-weight: bold; text-decoration: underline; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div class="container">
    <div id="login-form">
        <h2>HRMS Login</h2>
        <?php if($error) echo "<div class='alert error'>$error</div>"; ?>
        <?php if($message) echo "<div class='alert success'>$message</div>"; ?>

        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="Username" required>
            <select name="role" required>
                <option value="" disabled selected>Select Your Role</option>
                <option value="Admin">Admin</option>
                <option value="Manager">Manager</option>
                <option value="Hr">Hr</option>
                <option value="Teamlead">Teamlead</option>
                <option value="Employee">Employee</option>
                <option value="DigitalMarketing">Digital Marketing</option>
                <option value="Accounts">Accounts</option>
            </select>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        <div class="toggle-btn">
            Don't have an account? <span onclick="toggleForm()">Register Now</span>
        </div>
    </div>

    <div id="register-form" class="hidden">
        <h2>HRMS Register</h2>
        <form method="POST">
            <input type="hidden" name="action" value="register">
            <input type="text" name="username" placeholder="Choose Username" required>
            <select name="role" required>
                <option value="" disabled selected>Select Your Role</option>
                <option value="Admin">Admin</option>
                <option value="Manager">Manager</option>
                <option value="Hr">Hr</option>
                <option value="Teamlead">Teamlead</option>
                <option value="Employee">Employee</option>
                <option value="DigitalMarketing">Digital Marketing</option>
                <option value="Accounts">Accounts</option>
            </select>
            <input type="password" name="password" placeholder="Create Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Create Account</button>
        </form>
        <div class="toggle-btn">
            Already have an account? <span onclick="toggleForm()">Login here</span>
        </div>
    </div>
</div>

<script>
    function toggleForm() {
        document.getElementById('login-form').classList.toggle('hidden');
        document.getElementById('register-form').classList.toggle('hidden');
    }
</script>

</body>
</html>