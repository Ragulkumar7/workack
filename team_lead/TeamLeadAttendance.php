<?php
// --- 1. TARGETED DATABASE CONNECTION ---
$db_path = '../login/db_connect.php';
if (file_exists($db_path)) {
    include_once($db_path);
} else {
    die("Critical Error: Connection file missing.");
}

// --- 2. DYNAMIC DATA FETCHING & WFH LOGIC ---
$employeesUnderTL = [];
$wfhRequests = [];
$stats = ['present' => 0, 'on_time' => 0, 'late' => 0, 'wfh' => 0];

if (isset($conn) && $conn) {
    // --- UPDATED APPROVAL/REJECTION LOGIC ---
    if (isset($_POST['action']) && isset($_POST['request_id'])) {
        $reqId = $_POST['request_id'];
        
        if ($_POST['action'] == 'approve') {
            // ACCEPT: Update status to Approved AND change Work Type to Remote
            $statusUpdate = "UPDATE team_attendance SET wfh_status='Approved', work_type='Remote' WHERE id=".intval($reqId);
        } else {
            // REJECT: Update status to Rejected and ensure Work Type is Office
            $statusUpdate = "UPDATE team_attendance SET wfh_status='Rejected', work_type='Office' WHERE id=".intval($reqId);
        }
        
        if (is_numeric($reqId)) {
            mysqli_query($conn, $statusUpdate);
        } else {
            // Logic for Test Data (t1, t2, t3) Simulation
            setcookie($reqId, 'actioned_' . $_POST['action'], time() + 3600, "/"); 
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Fetch Attendance Records
    $sql = "SELECT * FROM team_attendance";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $employeesUnderTL[] = $row;
            
            // Stats Calculation
            if (($row['status'] ?? '') == 'Present') {
                $stats['present']++;
                if (strtotime($row['clock_in'] ?? '00:00') <= strtotime('09:15:00')) $stats['on_time']++;
                else $stats['late']++;
            }
            if (($row['work_type'] ?? '') == 'Remote' && ($row['wfh_status'] ?? '') == 'Approved') $stats['wfh']++;

            // Pending Requests (Real DB)
            if (($row['work_type'] ?? '') == 'Remote' && ($row['wfh_status'] ?? '') == 'Pending') {
                $wfhRequests[] = $row;
            }
        }
    }

    // --- 3. ADDING DETAILS TO WFH REQUESTS SECTION (Simulation Data) ---
    if (!isset($_COOKIE['t1'])) {
        $wfhRequests[] = ['id' => 't1', 'employee_name' => 'Anthony Lewis', 'attendance_date' => '2026-02-05', 'shift' => 'Day Shift', 'wfh_reason' => 'Family medical emergency', 'wfh_status' => 'Pending'];
    }
    if (!isset($_COOKIE['t2'])) {
        $wfhRequests[] = ['id' => 't2', 'employee_name' => 'Brian Villalobos', 'attendance_date' => '2026-02-05', 'shift' => 'Night Shift', 'wfh_reason' => 'Home broadband maintenance', 'wfh_status' => 'Pending'];
    }
    if (!isset($_COOKIE['t3'])) {
        $wfhRequests[] = ['id' => 't3', 'employee_name' => 'Doglas Martini', 'attendance_date' => '2026-02-05', 'shift' => 'Day Shift', 'wfh_reason' => 'Travel constraints today', 'wfh_status' => 'Pending'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Attendance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 30px; }
        .tl-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 20px; }
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-mini-card { background: white; padding: 15px; border-radius: 12px; border: 1px solid #e1e1e1; text-align: center; }
        .stat-label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 700; }
        .stat-value { font-size: 18px; font-weight: 800; color: #333; }
        .tl-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e1e1e1; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .card-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; font-weight: 700; font-size: 18px; color: #333; }
        .immersive-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        .immersive-row { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .immersive-cell { padding: 12px 20px; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        .btn-approve { background: #dcfce7; color: #166534; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 12px; }
        .btn-reject { background: #fee2e2; color: #991b1b; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 12px; margin-left: 5px; }
        .wfh-date { font-size: 12px; color: #64748b; font-weight: 500; }
        .wfh-shift { font-size: 11px; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; color: #475569; }
    </style>
</head>
<body>
    <?php include '../include/sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>
        <div class="dashboard-scroll-area">
            <div class="tl-header">
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; margin: 0; color: #1a1a1a;">Attendance & WFH Portal</h1>
                    <p style="font-size: 13px; color: #666; margin: 0;">Managing Stage: <span style="color:#FF9B44; font-weight:bold;">Live Attendance</span></p>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-mini-card"><div class="stat-label">Total Present</div><div class="stat-value"><?= $stats['present'] ?></div></div>
                <div class="stat-mini-card"><div class="stat-label">On Time</div><div class="stat-value" style="color:#16a34a;"><?= $stats['on_time'] ?></div></div>
                <div class="stat-mini-card"><div class="stat-label">Late Arrival</div><div class="stat-value" style="color:#dc2626;"><?= $stats['late'] ?></div></div>
                <div class="stat-mini-card"><div class="stat-label">WFH Active</div><div class="stat-value" style="color:#2563eb;"><?= $stats['wfh'] ?></div></div>
            </div>

            <div class="tl-card">
                <div class="card-header">
                    <div style="display:flex; align-items:center; gap:10px;"><i data-lucide="home" color="#2563eb" size="20"></i> WFH Requests (Pending Approval)</div>
                </div>
                <table class="immersive-table">
                    <thead>
                        <tr style="text-align:left; font-size:11px; color:#94a3b8; text-transform:uppercase;">
                            <th>Employee</th>
                            <th>Request Date</th>
                            <th>Shift Type</th>
                            <th>Reason for WFH</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($wfhRequests)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">No pending WFH requests.</td></tr>
                        <?php else: ?>
                            <?php foreach($wfhRequests as $req): ?>
                                <tr class="immersive-row">
                                    <td class="immersive-cell"><strong><?= htmlspecialchars($req['employee_name'] ?? '') ?></strong></td>
                                    <td class="immersive-cell wfh-date"><?= date('d M, Y', strtotime($req['attendance_date'] ?? 'today')) ?></td>
                                    <td class="immersive-cell"><span class="wfh-shift"><?= htmlspecialchars($req['shift'] ?? 'Day Shift') ?></span></td>
                                    <td class="immersive-cell" style="font-style:italic; color:#64748b;">"<?= htmlspecialchars($req['wfh_reason'] ?? '') ?>"</td>
                                    <td class="immersive-cell" style="text-align:right;">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="tl-card">
                <div class="card-header">
                    <div style="display:flex; align-items:center; gap:10px;"><i data-lucide="users" color="#FF9B44" size="20"></i> Team Attendance Log</div>
                </div>
                <table class="immersive-table">
                    <thead>
                        <tr style="text-align:left; font-size:11px; color:#94a3b8; text-transform:uppercase;">
                            <th style="padding-left:20px;">Employee</th>
                            <th>Status</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Production</th>
                            <th>WFH Stage</th>
                            <th style="text-align:right; padding-right:20px;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($employeesUnderTL as $emp): 
                            $empName = $emp['employee_name'] ?? '';
                            $workType = $emp['work_type'] ?? 'Office';
                            $wfhStatus = $emp['wfh_status'] ?? '';

                            // Updated Simulation Logic for Bottom Table Change
                            if ($empName == 'Anthony Lewis' && isset($_COOKIE['t1']) && $_COOKIE['t1'] == 'actioned_approve') { $workType = 'Remote'; $wfhStatus = 'Approved'; }
                            if ($empName == 'Brian Villalobos' && isset($_COOKIE['t2']) && $_COOKIE['t2'] == 'actioned_approve') { $workType = 'Remote'; $wfhStatus = 'Approved'; }
                            if ($empName == 'Doglas Martini' && isset($_COOKIE['t3']) && $_COOKIE['t3'] == 'actioned_approve') { $workType = 'Remote'; $wfhStatus = 'Approved'; }
                        ?>
                            <tr class="immersive-row">
                                <td class="immersive-cell" style="padding-left:20px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?= htmlspecialchars($emp['avatar'] ?? '') ?>" style="width:30px; height:30px; border-radius:50%; background:#eee;">
                                        <span style="font-weight:600;"><?= htmlspecialchars($empName) ?></span>
                                    </div>
                                </td>
                                <td class="immersive-cell">
                                    <span style="background: <?= (($emp['status'] ?? '')=='Present'?'#dcfce7':'#fee2e2') ?>; color: <?= (($emp['status'] ?? '')=='Present'?'#166534':'#991b1b') ?>; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight:700;">
                                        <?= htmlspecialchars($emp['status'] ?? 'Absent') ?>
                                    </span>
                                </td>
                                <td class="immersive-cell"><?= !empty($emp['clock_in']) ? date('h:i A', strtotime($emp['clock_in'])) : '--:--' ?></td>
                                <td class="immersive-cell"><?= !empty($emp['clock_out']) ? date('h:i A', strtotime($emp['clock_out'])) : '--:--' ?></td>
                                <td class="immersive-cell" style="color:#FF9B44; font-weight:700;"><?= htmlspecialchars($emp['production'] ?? '00:00') ?> Hrs</td>
                                
                                <td class="immersive-cell">
                                    <?php if($workType == 'Remote' && $wfhStatus == 'Approved'): ?>
                                        <span style="color:#16a34a; font-weight:700; font-size:12px;">WFH</span>
                                    <?php elseif($workType == 'Remote' && $wfhStatus == 'Pending'): ?>
                                        <span style="color:#ea580c; font-weight:700; font-size:12px;">Pending</span>
                                    <?php else: ?>
                                        <span style="color:#cbd5e1;">Office</span>
                                    <?php endif; ?>
                                </td>
                                <td class="immersive-cell" style="text-align:right; padding-right:20px;"><i data-lucide="eye" style="color:#64748b; cursor:pointer;"></i></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>