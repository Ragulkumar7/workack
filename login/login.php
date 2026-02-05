<?php
// 1. ERROR REPORTING & SESSION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. DATABASE CONNECTION 
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
    die("Error: Could not find db_connect.php.");
}

// Initializing variables to prevent warnings shown in your screenshot
$error = null;
$success = null;

// 3. HANDLE FORM SUBMISSION
if (isset($_POST['auth_action'])) {
    if (!isset($conn)) { die("Error: Database connection failed."); }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $selected_role = $_POST['role']; 

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            if ($row['role'] !== $selected_role) {
                $error = "Account registered as " . $row['role'] . ".";
            } else {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role']; 
                
                $redirects = [
                    'System Admin'      => '../admin/admindashboard.php',
                    'HR'                => '../hr/hr_dashboard.php',
                    'Manager'           => '../manager/dashboard.php',
                    'Team Lead'         => '../team_lead/tl_dashboard.php',
                    'Employee'          => '../employee/emp_dashboard.php',
                    'IT Team'           => '../it/dashboard.php',
                    'Sales'             => '../sales/sales_dashboard.php',
                    'Digital Marketing' => '../digital_marketing/dm_dashboard.php',
                    'Accounts'          => '../accounts/accounts_dashboard.php'
                ];
                header("Location: " . ($redirects[$row['role']] ?? 'login.php'));
                exit();
            }
        } else { $error = "Invalid password."; }
    } else { $error = "User not found."; }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workack HRMS | Secure Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-color: #16636B; /* Premium Brand Color */
            --brand-dark: #0a2d31;
            --text-muted: #64748b;
            --bg-light: #f4f7f7;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; display: flex; overflow: hidden; background: #fff; }

        .page-wrapper { display: flex; width: 100%; height: 100%; }

        /* Left Branding Section */
        .branding-side {
            flex: 1;
            background: var(--brand-color);
            color: white;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        /* Typewriter Effect Styles */
        .branding-side h1 { 
            font-size: 52px; 
            font-weight: 800; 
            line-height: 1.1; 
            margin-bottom: 25px; 
            min-height: 120px;
        }
        
        .cursor {
            display: inline-block;
            width: 4px;
            background-color: #fff;
            margin-left: 5px;
            animation: blink 1s infinite;
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        .branding-side p.desc { font-size: 18px; color: #cbdada; line-height: 1.6; max-width: 500px; margin-bottom: 40px; }

        /* Sliding Insights Box */
        .sliding-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            min-height: 140px;
        }
        .sliding-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #fff; opacity: 0.7; margin-bottom: 12px; font-weight: 700; }
        #sliding-text { font-style: italic; font-size: 18px; color: #fff; transition: opacity 0.5s ease; }

        /* Right Login Section */
        .login-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
        }

        .form-box { width: 100%; max-width: 400px; }
        
        /* Premium Logo Styling */
        .logo { 
            font-weight: 800; 
            font-size: 30px; 
            color: var(--brand-color); 
            margin-bottom: 40px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }
        .logo img { 
            height: 80px; /* Increased size as requested */
            width: auto; 
            object-fit: contain; 
        }

        h2 { font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom: 10px; }
        .subtitle { color: var(--text-muted); margin-bottom: 30px; font-size: 15px; }

        .group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #334155; }
        
        input, select {
            width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 12px;
            font-size: 15px; background: #f8fafc; transition: all 0.3s ease;
        }
        input:focus { outline: none; border-color: var(--brand-color); background: #fff; box-shadow: 0 0 0 4px rgba(22, 99, 107, 0.1); }

        .btn-primary {
            width: 100%; padding: 16px; background: var(--brand-color); color: white; border: none;
            border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 15px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { background: var(--brand-dark); transform: translateY(-1px); }

        /* Footer Credits */
        .footer {
            position: absolute;
            bottom: 30px;
            width: 100%;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }
        .footer b { color: var(--brand-color); }

        .alert { padding: 14px; border-radius: 10px; font-size: 14px; margin-bottom: 25px; background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }

        @media (max-width: 1024px) { .branding-side { display: none; } }
    </style>
</head>
<body>

<div class="page-wrapper">
    <section class="branding-side">
        <h1 id="typewriter"></h1><span class="cursor"></span>
        <p class="desc">Workack HRMS provides a modern, intuitive interface to manage payroll, attendance, and team growth in one place.</p>
        
        <div class="sliding-card">
            <div class="label">Workack Insights</div>
            <p id="sliding-text">"Efficiency is the foundation of a great workplace."</p>
        </div>
    </section>

    <section class="login-side">
        <div class="form-box">
            <div class="logo">
                <img src="/workack/assets/logos.png" alt="Workack Logo">
                Workack
            </div>

            <h2>Welcome Back</h2>
            <p class="subtitle">Please enter your credentials to access your dashboard.</p>

            <?php if($error): ?> <div class="alert"><?php echo $error; ?></div> <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="auth_mode" value="login">
                
                <div class="group">
                    <label>Username / Email</label>
                    <input type="text" name="username" placeholder="alex_hr" required>
                </div>

                <div class="group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="group">
                    <label>Operational Role</label>
                    <select name="role">
                        <option value="System Admin">System Admin</option>
                        <option value="HR">HR</option>
                        <option value="Manager">Manager</option>
                        <option value="Team Lead">Team Lead</option>
                        <option value="Employee">Employee</option>
                        <option value="IT Team">IT Team</option>
                        <option value="Sales">Sales</option>
                        <option value="Digital Marketing">Digital Marketing</option>
                        <option value="Accounts">Accounts</option>
                    </select>
                </div>

                <button type="submit" name="auth_action" class="btn-primary">Sign In to Workack</button>
            </form>
        </div>

        <div class="footer">
            &copy; 2026 Workack HRMS. All rights reserved by <b>neoerainfotech.in</b>
        </div>
    </section>
</div>

<script>
    // Typing Effect Logic
    const titleText = "Empowering Talent, \nSimplifying HR.";
    let charIndex = 0;
    const typewriter = document.getElementById('typewriter');

    function typeEffect() {
        if (charIndex < titleText.length) {
            typewriter.innerHTML += titleText.charAt(charIndex) === '\n' ? '<br>' : titleText.charAt(charIndex);
            charIndex++;
            setTimeout(typeEffect, 100);
        }
    }

    // Sliding Box Logic
    const insights = [
        '"Efficiency is the foundation of a great workplace."',
        '"Empower your employees with transparent management."',
        '"Automate payroll and focus on growing your talent."',
        '"Workack HRMS: Where data meets human connection."'
    ];
    let i = 0;
    const slidingText = document.getElementById('sliding-text');

    setInterval(() => {
        slidingText.style.opacity = 0;
        setTimeout(() => {
            i = (i + 1) % insights.length;
            slidingText.textContent = insights[i];
            slidingText.style.opacity = 1;
        }, 500);
    }, 4000);

    window.onload = typeEffect;
</script>
</body>
</html>