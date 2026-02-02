<?php
session_start();

// If already logged in → redirect
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'db_connect.php';   // or db_connect_pdo.php

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = "Please fill in both fields.";
    } else {
        // MySQLi version
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // IMPORTANT: assuming password is already hashed with password_hash()
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Your App</title>
  <style>
    body { font-family: Arial, sans-serif; max-width:480px; margin:60px auto; padding:20px; }
    .error { color:red; }
    input { display:block; width:100%; margin:10px 0; padding:10px; box-sizing:border-box; }
    button { width:100%; padding:12px; background:#0066cc; color:white; border:none; cursor:pointer; }
  </style>
</head>
<body>

<h2>Login</h2>

<?php if ($message): ?>
  <p class="error"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
  <input type="text"     name="username" placeholder="Username" required autofocus>
  <input type="password" name="password" placeholder="Password"  required>
  <button type="submit">Login</button>
</form>

<p><small>Not registered? <a href="register.php">Create account</a></small></p>

</body>
</html>