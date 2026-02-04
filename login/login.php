<?php
// login.php

// 1. ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. SESSION START
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php', 'db_connect.php'];
$conn_found = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $conn_found = true;
        break;
    }
}
if (!$conn_found) {
    die("Error: Could not find db_connect.php. Please check your folder structure.");
}

// --- LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$error = null;
$success = null;

// 4. HANDLE FORM SUBMISSION
if (isset($_POST['auth_action'])) {
    
    if (!isset($conn)) { die("Error: Database connection failed."); }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $selected_role = $_POST['role']; 
    $mode = $_POST['auth_mode']; 

    // --- REGISTER MODE ---
    if ($mode === 'register') {
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashed_password, $selected_role);
                
                if ($stmt->execute()) {
                    $success = "Registration successful as <strong>$selected_role</strong>! Please login.";
                } else {
                    $error = "Database Error: " . $conn->error;
                }
            }
            $stmt->close();
        }
    } 
    // --- LOGIN MODE ---
    elseif ($mode === 'login') {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                
                // === ROLE AUTO-FIX ===
                if (empty(trim($row['role']))) {
                    $fix_stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $fix_stmt->bind_param("si", $selected_role, $row['id']);
                    $fix_stmt->execute();
                    $fix_stmt->close();
                    $row['role'] = $selected_role; 
                }

                // Check Role Match
                if ($row['role'] !== $selected_role) {
                    $error = "Error: This account is registered as <strong>" . $row['role'] . "</strong>, but you selected <strong>" . $selected_role . "</strong>.";
                } else {
                    // Login Success
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role']; 

                    // --- REDIRECTION LOGIC ---
                    if ($row['role'] === 'Admin') {
                        header("Location: ../admin/admindashboard.php");
                        exit();
                    } elseif ($row['role'] === 'HR Management') {
                        header("Location: ../hr/hr_dashboard.php");
                        exit();
                    } elseif ($row['role'] === 'Employee') {
                         header("Location: ../employee/emp_dashboard.php");
                         exit();
                    } elseif ($row['role'] === 'Accounts') {
                        header("Location: ../accounts/accounts_dashboard.php");
                        exit();
                    } elseif ($row['role'] === 'Team Lead') {
                        header("Location: ../team_lead/tl_dashboard.php");
                        exit();
                    } elseif ($row['role'] === 'Digital Marketing') {
                        header("Location: ../digital_marketing/dm_dashboard.php");
                        exit();
                    } elseif ($row['role'] === 'Sales') {
                        // NEW: Redirects to Sales Dashboard
                        header("Location: ../sales/sales_dashboard.php");
                        exit();
                    }
                }

            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
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
    <title>SmartHR Login</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; flex-direction: column; }
        .login-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        .login-card h2 { color: #f29040; margin-bottom: 20px; font-weight: 600; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { display: block; margin-bottom: 5px; color: #333; font-size: 0.9em; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; background-color: #eef2f5; }
        button { width: 100%; padding: 12px; background-color: #f29040; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        button:hover { background-color: #e07e30; }
        .toggle-area { margin-top: 20px; font-size: 0.85em; color: #666; }
        .toggle-area a { color: #f29040; text-decoration: none; font-weight: bold; cursor: pointer; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; border: 1px solid #f5c6cb; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; border: 1px solid #c3e6cb; }
        .hidden { display: none; }
        
        .status-bar { background: #e2e3e5; color: #383d41; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 13px; text-align: center; border: 1px solid #d6d8db;}
        .logout-link { color: #dc3545; font-weight: bold; text-decoration: underline; cursor: pointer; }
    </style>
</head>
<body>

<?php if(isset($_SESSION['username'])): ?>
    <div class="status-bar">
        Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (<?php echo htmlspecialchars($_SESSION['role']); ?>)<br>
        <a href="login.php?logout=1" class="logout-link">Force Logout</a>
    </div>
<?php endif; ?>

<div class="login-card">
    <h2 id="formTitle">SmartHR Login</h2>

    <?php if($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="success"><?php echo $success; ?></div> <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="auth_mode" id="auth_mode" value="login">
        
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group hidden" id="confirmPassGroup">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password">
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="Admin">Admin</option>
                <option value="Team Lead">Team Lead</option>
                <option value="Employee">Employee</option>
                <option value="HR Management">HR Management</option>
                <option value="Accounts">Accounts</option>
                <option value="Digital Marketing">Digital Marketing</option>
                <option value="Sales">Sales</option> </select>
        </div>

        <button type="submit" name="auth_action" id="submitBtn">Login</button>
    </form>

    <div class="toggle-area">
        <span id="toggleText">Don't have an account? </span>
        <a onclick="toggleMode()" id="toggleLink">Register Now</a>
    </div>
</div>

<script>
    function toggleMode() {
        const mode = document.getElementById('auth_mode');
        const confirm = document.getElementById('confirmPassGroup');
        const btn = document.getElementById('submitBtn');
        const title = document.getElementById('formTitle');
        const text = document.getElementById('toggleText');
        const link = document.getElementById('toggleLink');

        if (mode.value === 'login') {
            mode.value = 'register';
            confirm.classList.remove('hidden');
            btn.textContent = 'Register';
            title.textContent = 'SmartHR Register';
            text.textContent = 'Already have an account? ';
            link.textContent = 'Login';
        } else {
            mode.value = 'login';
            confirm.classList.add('hidden');
            btn.textContent = 'Login';
            title.textContent = 'SmartHR Login';
            text.textContent = "Don't have an account? ";
            link.textContent = 'Register Now';
        }
    }
</script>

</body>
</html>