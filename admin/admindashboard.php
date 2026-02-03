<?php
// 1. DATABASE & SESSION SETUP
require_once '../login/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    // FIX 1: Redirect explicitly to the dashboard file (which will show the login form)
    header("Location: admindashboard.php");
    exit;
}

// 2. HANDLE LOGIN/REGISTRATION LOGIC
if (isset($_POST['auth_action'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $role     = mysqli_real_escape_string($conn, $_POST['role']); 
    $mode     = $_POST['auth_mode']; 

    $error = null;

    // --- REGISTER LOGIC ---
    if ($mode === 'register') {
        $confirm_password = $_POST['confirm_password'];
        if ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            // Success: Set Session
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
        }
    } 
    // --- LOGIN LOGIC ---
    elseif ($mode === 'login') {
        // TODO: Add database password verification here later
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role; 
    }

    // --- REDIRECT IF SUCCESS ---
    if (!$error && isset($_SESSION['username'])) {
        if ($_SESSION['role'] === 'Admin') {
            // FIX 2: Redirect explicitly to load the dashboard view
            header("Location: admindashboard.php");
            exit;
        } else {
            $error = "Access Denied: Only Admins can view this dashboard.";
            session_unset();
            session_destroy();
        }
    }
}

// 3. SECURITY CHECK: Should we show the dashboard?
$show_dashboard = (isset($_SESSION['username']) && $_SESSION['role'] === 'Admin');

// 4. DASHBOARD DATA (Only run query if logged in)
if ($show_dashboard) {
    if (isset($conn)) {
        $emp_query = mysqli_query($conn, "SELECT id FROM users");
        $total_employees = $emp_query ? mysqli_num_rows($emp_query) : 0;
    } else {
        $total_employees = 0;
    }
    
    // Dummy Data
    $total_projects  = 90;
    $total_clients   = 69;
    $total_tasks     = 96;
    $earnings        = "$21,445";
    $profit_weekly   = "$5,544";
    $job_applicants  = 98;
    $new_hires       = "45/48";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SmartHR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-orange: #FF9B44;
            --sidebar-bg: #34444c;
            --body-bg: #f7f7f7;
            --white: #ffffff;
            --text-dark: #333333;
            --text-muted: #6c757d;
            --sidebar-width: 70px; 
        }

        /* RESET & LAYOUT */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; font-family: 'Segoe UI', sans-serif; }
        body { background: var(--body-bg); display: flex; color: var(--text-dark); }

        /* AUTH FORM STYLES */
        .auth-container { display: flex; justify-content: center; align-items: center; height: 100vh; width: 100%; overflow-y: auto; background: var(--body-bg); }
        .auth-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .auth-card h2 { margin-bottom: 10px; color: var(--primary-orange); text-align: center; }
        .auth-group { margin-bottom: 15px; }
        .auth-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        .auth-group input, .auth-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; outline: none; }
        .btn-auth { width: 100%; background: var(--primary-orange); color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 13px; }
        
        .toggle-text { text-align: center; margin-top: 15px; font-size: 14px; }
        .toggle-text span { color: var(--primary-orange); cursor: pointer; font-weight: bold; text-decoration: underline; }
        
        /* Utility to hide elements */
        .d-none { display: none; }

        /* MAIN CONTENT SCROLLING */
        .main-content { 
            flex: 1; margin-left: var(--sidebar-width); height: 100vh; 
            overflow-y: auto; overflow-x: hidden; padding-bottom: 30px; transition: all 0.3s ease; 
        }
        .container-fluid { width: 100%; padding: 25px; }

        /* DASHBOARD COMPONENTS */
        .breadcrumb { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .welcome-card { background: var(--white); padding: 20px; border-radius: 8px; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid var(--primary-orange); }
        .welcome-card img { width: 60px; height: 60px; border-radius: 50%; margin-right: 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card i { font-size: 24px; padding: 15px; border-radius: 50%; margin-bottom: 10px; display: inline-block; }
        .bg-orange { color: #ff9b44; background: #ff9b4415; }
        .bg-blue { color: #00c5fb; background: #00c5fb15; }
        .bg-green { color: #55ce63; background: #55ce6315; }
        .bg-red { color: #f62d51; background: #f62d5115; }
        .bg-purple { color: #7460ee; background: #7460ee15; }

        .dashboard-row { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .triple-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .content-card { background: var(--white); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .card-title { font-size: 16px; font-weight: bold; }
        .list-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f9f9f9; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-info img { width: 38px; height: 38px; border-radius: 50%; }

        .gauge-container { text-align: center; padding: 20px 0; }
        .attendance-gauge { width: 200px; height: 100px; border: 15px solid #eee; border-bottom: none; border-radius: 200px 200px 0 0; margin: 0 auto; position: relative; }
        .gauge-fill { position: absolute; top: -15px; left: -15px; width: 100%; height: 100%; border: 15px solid #55ce63; border-bottom: none; border-radius: 200px 200px 0 0; clip-path: inset(0 30% 0 0); }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 15px; width: 450px; }
        .add-btn { background: var(--primary-orange); color: white; border: none; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; }

        @media (max-width: 1200px) { .dashboard-row, .triple-row, .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php if (!$show_dashboard): ?>
    <div class="auth-container">
        <div class="auth-card">
            
            <?php if(isset($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <div id="login-box">
                <h2>SmartHR Login</h2>
                <form method="POST">
                    <input type="hidden" name="auth_mode" value="login">
                    <div class="auth-group">
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="Enter username">
                    </div>
                    
                    <div class="auth-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Employee">Employee</option>
                        </select>
                    </div>

                    <div class="auth-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    <button type="submit" name="auth_action" class="btn-auth">Login</button>
                    
                    <div class="toggle-text">
                        Don't have an account? <span onclick="toggleAuth('register')">Register Now</span>
                    </div>
                </form>
            </div>

            <div id="register-box" class="d-none">
                <h2>Create Account</h2>
                <form method="POST">
                    <input type="hidden" name="auth_mode" value="register">
                    <div class="auth-group">
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="Choose a username">
                    </div>
                    <div class="auth-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Employee">Employee</option>
                        </select>
                    </div>
                    <div class="auth-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    <div class="auth-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required placeholder="••••••••">
                    </div>
                    <button type="submit" name="auth_action" class="btn-auth">Register</button>
                    
                    <div class="toggle-text">
                        Already have an account? <span onclick="toggleAuth('login')">Login</span>
                    </div>
                </form>
            </div>

        </div>
    </div>

<?php else: ?>
    <?php include '../include/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../include/header.php'; ?>
        <div class="container-fluid">
            <div class="breadcrumb">
                <div>
                    <h2>Admin Dashboard</h2>
                    <p style="color:var(--text-muted); font-size: 13px;">Welcome, <?php echo $_SESSION['username']; ?> | <a href="?logout=true">Logout</a></p>
                </div>
                <button style="background:var(--primary-orange); color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">+ Add Schedule</button>
            </div>

            <div class="welcome-card">
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['username']; ?>&background=FF9B44&color=fff" alt="Admin">
                <div class="welcome-text">
                    <h3>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                    <p>You have <span style="color:#f62d51; font-weight:bold;">21</span> Pending Approvals & 14 Leave Requests.</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><i class="fa fa-user-check bg-orange"></i><h3><?php echo $total_employees; ?>/154</h3><p>Attendance</p></div>
                <div class="stat-card"><i class="fa fa-cubes bg-blue"></i><h3>90</h3><p>Projects</p></div>
                <div class="stat-card"><i class="fa fa-gem bg-green"></i><h3>69</h3><p>Clients</p></div>
                <div class="stat-card"><i class="fa fa-tasks bg-red"></i><h3>96</h3><p>Tasks</p></div>
                <div class="stat-card"><i class="fa fa-wallet bg-purple"></i><h3>$21,445</h3><p>Earnings</p></div>
                <div class="stat-card"><i class="fa fa-chart-line bg-red"></i><h3>$5,544</h3><p>Weekly Profit</p></div>
                <div class="stat-card"><i class="fa fa-user-tie bg-green"></i><h3>98</h3><p>Applicants</p></div>
                <div class="stat-card"><i class="fa fa-user-plus bg-blue"></i><h3>45/48</h3><p>New Hire</p></div>
            </div>

            <div class="dashboard-row">
                <div class="content-card">
                    <div class="card-header"><span class="card-title">Employee Status</span></div>
                    <div style="padding:10px 0;">
                        <p style="font-size: 14px; margin-bottom: 5px;">Total Employee <span style="float:right; font-weight:bold;">154</span></p>
                        <div style="height:12px; background:#eee; border-radius:10px; overflow:hidden; display:flex; margin-bottom:20px;">
                            <div style="width:48%; background:#ff9b44"></div>
                            <div style="width:20%; background:#00c5fb"></div>
                            <div style="width:22%; background:#f62d51"></div>
                            <div style="width:10%; background:#55ce63"></div>
                        </div>
                        <div class="list-item"><span>Fulltime (48%)</span><strong>112</strong></div>
                        <div class="list-item"><span>Contract (20%)</span><strong>112</strong></div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header"><span class="card-title">Attendance Overview</span></div>
                    <div class="gauge-container">
                        <div class="attendance-gauge"><div class="gauge-fill"></div></div>
                        <h2 style="margin-top:10px;">120</h2>
                        <p style="font-size:12px; color:#888;">Total Attendance</p>
                    </div>
                </div>
            </div>

            <div class="triple-row">
                <div class="content-card">
                    <div class="card-header"><span class="card-title">Jobs Applicants</span></div>
                    <div class="list-item">
                        <div class="user-info">
                            <img src="https://i.pravatar.cc/150?u=1" alt="">
                            <div><p style="font-size:14px; font-weight:600;">Brian Villalobos</p><small>Exp: 5+ Years</small></div>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <div class="card-header"><span class="card-title">Employees</span></div>
                    <div class="list-item">
                        <div class="user-info">
                            <img src="https://i.pravatar.cc/150?u=3" alt="">
                            <div><p style="font-size:14px; font-weight:600;">John Doe</p><small>Software Engineer</small></div>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <div class="card-header"><span class="card-title">Todo List</span><button class="add-btn" id="openTodoModal">+</button></div>
                    <div class="list-item"><label><input type="checkbox" style="margin-right:10px;"> Add Holidays</label></div>
                </div>
            </div>
        </div>
    </main>

    <div id="todoModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:15px;">Add New Todo</h2>
            <input type="text" placeholder="Enter title" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:5px;">
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="document.getElementById('todoModal').style.display='none'" style="padding:10px 20px; border:none; cursor:pointer;">Cancel</button>
                <button style="padding:10px 20px; background:var(--primary-orange); color:white; border:none; border-radius:5px; cursor:pointer;">Save</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // --- AUTH TOGGLE LOGIC ---
    function toggleAuth(mode) {
        const loginBox = document.getElementById('login-box');
        const regBox = document.getElementById('register-box');
        
        if(mode === 'register') {
            loginBox.classList.add('d-none');
            regBox.classList.remove('d-none');
        } else {
            regBox.classList.add('d-none');
            loginBox.classList.remove('d-none');
        }
    }

    // --- SIDEBAR LOGIC ---
    function checkSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.main-content');
        if(sidebar && sidebar.classList.contains('active')) {
            content.style.marginLeft = "240px";
            content.style.width = "calc(100% - 240px)";
        } else if (content) {
            content.style.marginLeft = "70px";
            content.style.width = "calc(100% - 70px)";
        }
    }
    window.onload = checkSidebar;

    const modal = document.getElementById("todoModal");
    const btn = document.getElementById("openTodoModal");
    if(btn) {
        btn.onclick = function() { modal.style.display = "flex"; }
    }
    window.onclick = function(event) {
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>

</body>
</html>