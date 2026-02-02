<?php
require_once('include/db_connect.php');

// FOR TESTING: We assume the logged-in user is 'Arun Kumar' (ID: 1)
$_SESSION['user_id'] = 1;
$user_id = $_SESSION['user_id'];

// --- 1. HANDLE ATTENDANCE (PUNCH IN/OUT) ---
if (isset($_POST['toggle_attendance'])) {
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');
    
    // Check if user is already punched in for today
    $check_att = mysqli_query($conn, "SELECT id FROM attendance WHERE emp_id = $user_id AND work_date = '$today' AND punch_out IS NULL LIMIT 1");
    
    if (mysqli_num_rows($check_att) > 0) {
        // PUNCH OUT
        mysqli_query($conn, "UPDATE attendance SET punch_out = '$now' WHERE emp_id = $user_id AND work_date = '$today' AND punch_out IS NULL");
    } else {
        // PUNCH IN
        mysqli_query($conn, "INSERT INTO attendance (emp_id, punch_in, work_date, status) VALUES ($user_id, '$now', '$today', 'Present')");
    }
    header("Location: dashboard.php?msg=Attendance Updated");
    exit();
}

// --- 2. HANDLE LEAVE APPLICATION ---
if (isset($_POST['apply_leave'])) {
    $type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];
    
    $sql = "INSERT INTO leaves (emp_id, leave_type, reason, from_date, to_date, status) VALUES ($user_id, '$type', '$reason', '$from', '$to', 'Pending')";
    mysqli_query($conn, $sql);
    header("Location: dashboard.php?msg=Leave Applied");
    exit();
}

// --- 3. FETCH DATA ---
$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE id = $user_id")); //
$tasks = mysqli_query($conn, "SELECT * FROM tasks WHERE emp_id = $user_id"); //
$att_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id = $user_id AND work_date = '" . date('Y-m-d') . "' AND punch_out IS NULL"));
$is_checked_in = $att_today ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workack | HRMS Dashboard</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Incorporating your React CSS Styles */
        body { font-family: -apple-system, sans-serif; background-color: #f4f7fc; padding: 40px; color: #333; margin: 0; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 30px; }
        .ed-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1; }
        .avatar-circle { width: 70px; height: 70px; border-radius: 50%; background: #FF9B44; color: white; font-size: 24px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 4px solid #ffedd5; }
        .punch-btn { width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-in { background: #22c55e; } .btn-out { background: #ef4444; }
        .task-item { padding: 12px; background: #f9fafb; border-left: 4px solid #FF9B44; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; border-radius: 6px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 30px; border-radius: 12px; width: 400px; }
    </style>
</head>
<body>

    <div style="display:flex; justify-content:space-between; margin-bottom:30px;">
        <h1>Employee Dashboard</h1>
        <p>Welcome, <strong><?php echo $emp['name']; ?></strong> (<?php echo $emp['emp_code']; ?>)</p>
    </div>

    <div class="grid-3">
        <div class="ed-card">
            <div style="display:flex; gap:15px; margin-bottom:20px;">
                <div class="avatar-circle"><?php echo $emp['initials']; ?></div>
                <div>
                    <h2 style="margin:0;"><?php echo $emp['name']; ?></h2>
                    <span style="color:#FF9B44; font-weight:700; font-size:12px;"><?php echo $emp['role']; ?></span>
                    <p style="font-size:13px; color:#666;"><i data-lucide="mail" style="width:14px;"></i> <?php echo $emp['email']; ?></p>
                </div>
            </div>
            <div style="background:#f9fafb; padding:15px; border-radius:8px;">
                <p style="font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase;">Department</p>
                <p><?php echo $emp['department']; ?></p>
            </div>
        </div>

        <div class="ed-card">
            <h3>Attendance Status</h3>
            <div style="text-align:center; padding: 20px 0;">
                <div style="color: <?php echo $is_checked_in ? '#22c55e' : '#9ca3af'; ?>">
                    <i data-lucide="clock" style="width:48px; height:48px;"></i>
                </div>
                <h4 style="font-size:20px; margin:10px 0;">
                    <?php echo $is_checked_in ? "Checked In" : "Checked Out"; ?>
                </h4>
            </div>
            <form method="POST">
                <button type="submit" name="toggle_attendance" class="punch-btn <?php echo $is_checked_in ? 'btn-out' : 'btn-in'; ?>">
                    <i data-lucide="<?php echo $is_checked_in ? 'log-out' : 'log-in'; ?>"></i>
                    <?php echo $is_checked_in ? "Punch Out" : "Punch In"; ?>
                </button>
            </form>
        </div>

        <div class="ed-card">
            <h3>My Tasks</h3>
            <div style="max-height: 250px; overflow-y: auto;">
                <?php while($t = mysqli_fetch_assoc($tasks)): ?>
                    <div class="task-item">
                        <div>
                            <p style="margin:0; font-weight:700;"><?php echo $t['title']; ?></p>
                            <small><?php echo $t['estimated_minutes']; ?> mins est.</small>
                        </div>
                        <span style="font-size:11px; padding:4px 8px; border-radius:4px; background:<?php echo $t['status'] == 'Completed' ? '#dcfce7' : '#fff7ed'; ?>; color:<?php echo $t['status'] == 'Completed' ? '#166534' : '#c2410c'; ?>;">
                            <?php echo $t['status']; ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="grid-3">
        <div class="ed-card">
            <h3>Quick Actions</h3>
            <button onclick="document.getElementById('leaveModal').style.display='block'" style="width:100%; padding:12px; background:white; border:1px solid #FF9B44; color:#FF9B44; border-radius:8px; font-weight:700; cursor:pointer;">
                Apply New Leave
            </button>
        </div>
    </div>

    <div id="leaveModal" class="modal">
        <div class="modal-content">
            <h2>Apply New Leave</h2>
            <form method="POST">
                <label>Leave Type</label><br>
                <select name="leave_type" required style="width:100%; padding:10px; margin-bottom:15px;">
                    <option value="Casual">Casual leave</option>
                    <option value="Sick">Sick</option>
                    <option value="Earned">Earned</option>
                </select><br>
                <label>From Date</label>
                <input type="date" name="from_date" required style="width:100%; padding:10px; margin-bottom:15px;">
                <label>To Date</label>
                <input type="date" name="to_date" required style="width:100%; padding:10px; margin-bottom:15px;">
                <label>Reason</label>
                <textarea name="reason" required style="width:100%; height:80px; margin-bottom:15px;"></textarea>
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="document.getElementById('leaveModal').style.display='none'" style="flex:1; padding:10px;">Cancel</button>
                    <button type="submit" name="apply_leave" style="flex:1; padding:10px; background:#FF9B44; color:white; border:none; border-radius:6px;">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>