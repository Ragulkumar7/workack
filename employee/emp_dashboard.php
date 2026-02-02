<?php
require_once('../include/db_connect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = 1;
$user_id = $_SESSION['user_id'];

// --- ATTENDANCE HANDLER ---
if (isset($_POST['toggle_attendance'])) {
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');
    $check_att = mysqli_query($conn, "SELECT id FROM attendance WHERE emp_id = $user_id AND work_date = '$today' AND punch_out IS NULL LIMIT 1");
    
    if (mysqli_num_rows($check_att) > 0) {
        mysqli_query($conn, "UPDATE attendance SET punch_out = '$now' WHERE emp_id = $user_id AND work_date = '$today' AND punch_out IS NULL");
    } else {
        mysqli_query($conn, "INSERT INTO attendance (emp_id, punch_in, work_date, status) VALUES ($user_id, '$now', '$today', 'Present')");
    }
    header("Location: emp_dashboard.php");
    exit();
}

$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE id = $user_id"));
$tasks = mysqli_query($conn, "SELECT * FROM tasks WHERE emp_id = $user_id");
$att_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id = $user_id AND work_date = '" . date('Y-m-d') . "' AND punch_out IS NULL"));
$is_checked_in = $att_today ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workack | Dashboard</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #FF9B44;
            --bg: #f4f7fc;
            --white: #ffffff;
        }

        /* FLEX LAYOUT: Body no longer uses absolute positioning for sidebar */
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* Sidebar container is handled by the include, it will push this div */
        .main-content { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            height: 100vh; 
            overflow-x: auto; /* ENABLES HORIZONTAL SCROLLING */
            overflow-y: hidden;
            transition: all 0.3s ease;
        }

        /* HEADER */
        .header-container { width: 100%; flex-shrink: 0; }

        /* BODY CONTENT */
        .page-body { 
            padding: 25px; 
            min-width: 1100px; /* FORCES HORIZONTAL SCROLL IF SCREEN IS TOO SMALL */
            overflow-y: auto; 
            flex: 1; 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px;
            align-content: start;
        }

        .ed-card { 
            background: var(--white); 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            border: 1px solid #eef; 
            display: flex; 
            flex-direction: column;
            min-height: 240px;
        }

        .card-header { font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }

        /* PROFILE */
        .profile-box { display: flex; gap: 15px; margin-bottom: 15px; }
        .avatar-circle { width: 60px; height: 60px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; }
        
        /* ATTENDANCE */
        .punch-btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: auto; }
        .btn-in { background: #22c55e; } .btn-out { background: #ef4444; }

        /* STATS */
        .stat-row { display: flex; justify-content: space-between; padding: 10px; border-radius: 8px; margin-bottom: 8px; font-size: 13px; font-weight: 600; }
        .row-green { background: #f0fdf4; color: #166534; }
        .row-blue { background: #eff6ff; color: #1e40af; }
        .row-orange { background: #fff7ed; color: #9a3412; }
        .row-red { background: #fef2f2; color: #991b1b; }

        /* SCROLLABLE AREAS */
        .scroll-area { flex: 1; overflow-y: auto; max-height: 160px; padding-right: 5px; }
        .task-item { padding: 10px; background: #f9fafb; border-left: 4px solid var(--primary); margin-bottom: 8px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }

        .pie-placeholder { width: 90px; height: 90px; border-radius: 50%; background: conic-gradient(var(--primary) 40%, #22c55e 40% 70%, #3b82f6 70% 100%); flex-shrink: 0; }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    </style>
</head>
<body>

    <?php include('../include/sidebar.php'); ?>

    <div class="main-content">
        <div class="header-container">
            <?php include('../include/header.php'); ?>
        </div>

        <div class="page-body">
            
            <div class="ed-card">
                <div class="profile-box">
                    <div class="avatar-circle"><?= $emp['initials'] ?></div>
                    <div>
                        <h2 style="margin:0; font-size:18px;"><?= $emp['name'] ?></h2>
                        <span style="color:var(--primary); font-size:12px; font-weight:700;"><?= $emp['role'] ?></span>
                        <div style="font-size:11px; color:#888; margin-top:5px;"><i data-lucide="mail" style="width:12px; vertical-align:middle;"></i> <?= $emp['email'] ?></div>
                    </div>
                </div>
                <div style="background:#f9fafb; padding:12px; border-radius:8px; margin-top:auto;">
                    <div style="font-size:10px; font-weight:800; color:#aaa; text-transform:uppercase; margin-bottom:8px;">Documents</div>
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:5px;">Aadhaar_Card.pdf <i data-lucide="download" style="width:14px;"></i></div>
                    <div style="display:flex; justify-content:space-between; font-size:12px;">PAN_Card.pdf <i data-lucide="download" style="width:14px;"></i></div>
                </div>
            </div>

            <div class="ed-card" style="text-align: center;">
                <div class="card-header"><i data-lucide="clock"></i> Attendance</div>
                <div style="flex:1; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                    <div style="width:60px; height:60px; background:#f0f0f0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                         <i data-lucide="user-check" style="color:#888;"></i>
                    </div>
                    <div style="font-size:13px; color:#999;">Status</div>
                    <h3 style="margin:5px 0;"><?= $is_checked_in ? "Checked In" : "Checked Out" ?></h3>
                </div>
                <form method="POST">
                    <button type="submit" name="toggle_attendance" class="punch-btn <?= $is_checked_in ? 'btn-out' : 'btn-in' ?>">
                        <i data-lucide="<?= $is_checked_in ? 'log-out' : 'log-in' ?>"></i>
                        <?= $is_checked_in ? "Punch Out" : "Punch In" ?>
                    </button>
                </form>
            </div>

            <div class="ed-card">
                <div class="card-header"><i data-lucide="activity"></i> Statistics</div>
                <div class="stat-row row-green"><span>On Time</span> <span>1254</span></div>
                <div class="stat-row row-blue"><span>WFH</span> <span>12</span></div>
                <div class="stat-row row-orange"><span>Late</span> <span>5</span></div>
                <div class="stat-row row-red"><span>Absent</span> <span>14</span></div>
            </div>

            <div class="ed-card">
                <div class="card-header">Leave Distribution</div>
                <div style="display:flex; align-items:center; gap:20px; flex:1;">
                    <div class="pie-placeholder"></div>
                    <div style="font-size:13px; flex:1;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;"><span>Sick</span> <b>3</b></div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;"><span>Casual</span> <b>4</b></div>
                        <div style="display:flex; justify-content:space-between; font-weight:700; color:var(--primary); border-top:1px solid #eee; pt-5;"><span>Total</span> <b>7</b></div>
                    </div>
                </div>
            </div>

            <div class="ed-card">
                <div class="card-header">My Tasks</div>
                <div class="scroll-area">
                    <?php while($t = mysqli_fetch_assoc($tasks)): ?>
                        <div class="task-item">
                            <span style="font-size:13px; font-weight:600;"><?= $t['title'] ?></span>
                            <small style="font-size:10px; background:#eee; padding:2px 6px; border-radius:10px;"><?= $t['status'] ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="ed-card">
                <div class="card-header">Announcements</div>
                <div class="scroll-area">
                    <div style="padding:10px; background:#fff7ed; border-left:4px solid #f97316; border-radius:5px; margin-bottom:10px; font-size:12px;">
                        <strong>Town Hall:</strong> Q1 growth update on Jan 30.
                    </div>
                    <div style="padding:10px; background:#eff6ff; border-left:4px solid #3b82f6; border-radius:5px; font-size:12px;">
                        <strong>System:</strong> Maintenance tonight at 11 PM.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>