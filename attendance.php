<?php
// 1. DATABASE CONNECTION & SESSION HANDLING
include 'include/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TEMPORARY FIX: AUTOMATIC LOGIN FOR TESTING
if (!isset($_SESSION['user_id'])) {
    $temp_user_id = 1; 
    $user_query = $conn->query("SELECT id, username, role FROM users WHERE id = $temp_user_id");
    if ($user_row = $user_query->fetch_assoc()) {
        $_SESSION['user_id'] = $user_row['id'];
        $_SESSION['username'] = $user_row['username'];
        $_SESSION['role'] = $user_row['role'];
    }
}

$emp_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';
$date = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// 2. BACKEND LOGIC (AJAX HANDLING)
// 2. BACKEND LOGIC (AJAX HANDLING)
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $response = ['status' => 'error', 'message' => 'Unknown action'];

    try {
        // Fetch today's record first to determine current state
        $stmt = $conn->prepare("SELECT * FROM attendance WHERE emp_id = ? AND work_date = ?");
        $stmt->bind_param("is", $emp_id, $date);
        $stmt->execute();
        $res = $stmt->get_result();
        $att_data = $res->fetch_assoc();

        if ($action == 'punch_in') {
            if (!$att_data) {
                $stmt = $conn->prepare("INSERT INTO attendance (emp_id, punch_in, status, work_date, production_hours, overtime_hours, break_duration, late) VALUES (?, ?, 'Present', ?, '0h 00m', '0h 00m', '0m 00s', '0m')");
                $stmt->bind_param("iss", $emp_id, $now, $date);
                if ($stmt->execute()) {
                    $response = ['status' => 'success'];
                } else {
                    $response = ['status' => 'error', 'message' => 'DB Error: ' . $conn->error];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Already punched in for today'];
            }
        } 
        elseif ($action == 'punch_out') {
            if ($att_data && $att_data['punch_in']) {
                $start = new DateTime($att_data['punch_in']);
                $end = new DateTime($now);
                $interval = $start->diff($end);
                
                // Format matching your UI: e.g., "8h 36m"
                $prod = $interval->format('%hh %im'); 

                $stmt = $conn->prepare("UPDATE attendance SET punch_out = ?, production_hours = ?, status = 'Present' WHERE emp_id = ? AND work_date = ?");
                $stmt->bind_param("ssis", $now, $prod, $emp_id, $date);
                if ($stmt->execute()) {
                    $response = ['status' => 'success'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Update Error: ' . $conn->error];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'No active Punch In found.'];
            }
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit; 
}

// 3. DATA FETCHING FOR STAT CARDS
// Today's Stats
$att_query = $conn->query("SELECT * FROM attendance WHERE emp_id = '$emp_id' AND work_date = '$date'");
$att = $att_query->fetch_assoc();
$is_punched_in = ($att && $att['punch_in'] && !$att['punch_out']);
$punch_in_time = $att['punch_in'] ?? null;

// Weekly Stats Calculation
$week_query = $conn->query("SELECT COUNT(*) as days, SUM(CAST(production_hours AS DECIMAL(10,2))) as total_hrs FROM attendance WHERE emp_id = '$emp_id' AND YEARWEEK(work_date, 1) = YEARWEEK(CURDATE(), 1)");
$week_stats = $week_query->fetch_assoc();

// Monthly Stats Calculation
$month_query = $conn->query("SELECT SUM(CAST(production_hours AS DECIMAL(10,2))) as total_hrs, SUM(CAST(overtime_hours AS UNSIGNED)) as total_ot FROM attendance WHERE emp_id = '$emp_id' AND MONTH(work_date) = MONTH(CURDATE()) AND YEAR(work_date) = YEAR(CURDATE())");
$month_stats = $month_query->fetch_assoc();

// Filter Logic for Table
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$sql_table = "SELECT * FROM attendance WHERE emp_id = '$emp_id'";
if ($filter_status != '') {
    $sql_table .= " AND status = '$filter_status'";
}
$sql_table .= " ORDER BY work_date DESC";
$logs = $conn->query($sql_table);

include 'include/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Attendance Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #334155; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
        
        /* Sidebar Profile Card */
        .profile-card { text-align: center; padding: 25px; height: 100%; }
        .profile-img-wrap { position: relative; display: inline-block; margin-bottom: 15px; }
        .profile-img-wrap img { width: 100px; height: 100px; border-radius: 50%; border: 3px solid #10b981; }
        .prod-badge { background: #ef4444; color: white; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: 600; }
        
        /* Stats Cards */
        .stat-card { padding: 20px; position: relative; }
        .stat-icon { width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; color: white; }
        .stat-val { font-size: 22px; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 14px; color: #64748b; font-weight: 500; }
        .stat-trend { font-size: 12px; margin-top: 5px; }
        .trend-up { color: #10b981; } .trend-down { color: #ef4444; }

        /* Timeline Chart */
        .timeline-container { padding: 25px; margin-top: 20px; }
        .timeline-bar-wrap { height: 35px; background: #f1f5f9; border-radius: 6px; position: relative; margin: 30px 0 10px 0; overflow: hidden; }
        .timeline-segment { height: 100%; position: absolute; }
        .segment-work { background: #10b981; }
        .segment-break { background: #f59e0b; }
        .segment-overtime { background: #3b82f6; }
        .time-labels { display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; }

        /* Punch Button */
        .btn-punch-main { background: #1e293b; color: white; padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; border: none; transition: 0.3s; }
        .btn-punch-main.punched-in { background: #f8fafc; color: #1e293b; border: 1px solid #e2e8f0; }

        /* Filter Controls */
        .filter-btn { border: 1px solid #e2e8f0; background: white; padding: 6px 15px; border-radius: 6px; font-size: 14px; color: #475569; }
        .table thead th { background: #f8fafc; font-size: 12px; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .badge-present { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .badge-absent { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    </style>
</head>
<body>

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-lg-3">
            <div class="card profile-card">
    <p class="text-muted small">Good Morning, <?php echo htmlspecialchars($username); ?></p>
    <h5 class="font-weight-bold" id="liveClock"><?php echo date('h:i A, d M Y'); ?></h5>
    
    <div class="profile-img-wrap mt-3">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random" alt="User">
    </div>
    
    <div class="my-3">
        <span class="prod-badge">Production : <span id="timerDisplay"><?php echo $att['production_hours'] ?? '0h 00m'; ?></span></span>
        <p class="small text-muted mt-2"><i class="fas fa-fingerprint text-danger"></i> 
            Punch In at <?php echo $att['punch_in'] ? date('h:i A', strtotime($att['punch_in'])) : '--:--'; ?>
        </p>
    </div>

    <div id="punch-actions">
        <?php if (!$att): ?>
            <button class="btn-punch-main" onclick="handlePunch('punch_in')">Punch In</button>
        <?php elseif (!$att['punch_out']): ?>
            <?php if ($status == 'On Break'): ?>
                <button class="btn btn-success btn-block py-2 mb-2" onclick="handlePunch('end_break')">End Break</button>
            <?php else: ?>
                <button class="btn btn-warning btn-block py-2 mb-2" onclick="handlePunch('start_break')">Take Break</button>
            <?php endif; ?>
            <button class="btn-punch-main punched-in" onclick="handlePunch('punch_out')">Punch Out</button>
        <?php else: ?>
            <div class="alert alert-secondary py-2">Shift Completed</div>
        <?php endif; ?>
    </div>
</div>
        </div>

        <div class="col-lg-9">
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon bg-danger"><i class="fas fa-clock"></i></div>
                        <div class="stat-val"><?php echo ($att['production_hours'] ?? '0.00'); ?> <span class="text-muted small">/ 9</span></div>
                        <div class="stat-label">Total Hours Today</div>
                        <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 5% This Week</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon bg-dark"><i class="fas fa-calendar-week"></i></div>
                        <div class="stat-val"><?php echo round($week_stats['total_hrs'] ?? 0, 2); ?> <span class="text-muted small">/ 40</span></div>
                        <div class="stat-label">Total Hours Week</div>
                        <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 7% Last Week</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon bg-primary"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-val"><?php echo round($month_stats['total_hrs'] ?? 0, 2); ?> <span class="text-muted small">/ 180</span></div>
                        <div class="stat-label">Total Hours Month</div>
                        <div class="stat-trend trend-down"><i class="fas fa-arrow-down"></i> 8% Last Month</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon bg-warning"><i class="fas fa-history"></i></div>
                        <div class="stat-val"><?php echo ($month_stats['total_ot'] ?? 0); ?> <span class="text-muted small">/ 28</span></div>
                        <div class="stat-label">Overtime this Month</div>
                        <div class="stat-trend trend-down"><i class="fas fa-arrow-down"></i> 6% Last Month</div>
                    </div>
                </div>
            </div>

            <div class="card timeline-container mt-4">
                <div class="row text-center mb-2">
                    <div class="col-3 border-right"><small class="text-muted">Total Working hours</small><h6><?php echo ($att['production_hours'] ?? '0h 00m'); ?></h6></div>
                    <div class="col-3 border-right"><small class="text-muted"><i class="fas fa-circle text-success small"></i> Productive Hours</small><h6><?php echo ($att['production_hours'] ?? '0h 00m'); ?></h6></div>
                    <div class="col-3 border-right"><small class="text-muted"><i class="fas fa-circle text-warning small"></i> Break hours</small><h6><?php echo ($att['break_duration'] ?? '0m 22s'); ?></h6></div>
                    <div class="col-3"><small class="text-muted"><i class="fas fa-circle text-primary small"></i> Overtime</small><h6><?php echo ($att['overtime_hours'] ?? '02h 15m'); ?></h6></div>
                </div>
                
                <div class="timeline-bar-wrap">
                    <div class="timeline-segment segment-work" style="left: 15%; width: 15%;"></div>
                    <div class="timeline-segment segment-break" style="left: 30%; width: 5%;"></div>
                    <div class="timeline-segment segment-work" style="left: 35%; width: 25%;"></div>
                    <div class="timeline-segment segment-break" style="left: 60%; width: 10%;"></div>
                    <div class="timeline-segment segment-work" style="left: 70%; width: 15%;"></div>
                    <div class="timeline-segment segment-break" style="left: 85%; width: 3%;"></div>
                    <div class="timeline-segment segment-overtime" style="left: 88%; width: 8%;"></div>
                </div>
                <div class="time-labels">
                    <span>06:00</span><span>07:00</span><span>08:00</span><span>09:00</span><span>10:00</span><span>11:00</span>
                    <span>12:00</span><span>01:00</span><span>02:00</span><span>03:00</span><span>04:00</span><span>05:00</span>
                    <span>06:00</span><span>07:00</span><span>08:00</span><span>09:00</span><span>10:00</span><span>11:00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="font-weight-bold">Employee Attendance</h5>
                <div class="d-flex align-items-center">
                    <button class="filter-btn mr-2"><i class="far fa-calendar-alt"></i> 01/29/2026 - 02/04/2026</button>
                    
                    <div class="dropdown mr-2">
                        <button class="filter-btn dropdown-toggle" type="button" data-toggle="dropdown">Select Status</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="attendance.php?status=Present">Present</a>
                            <a class="dropdown-item" href="attendance.php?status=Absent">Absent</a>
                            <a class="dropdown-item" href="attendance.php">All</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="filter-btn dropdown-toggle" type="button" data-toggle="dropdown">Sort By : Last 7 Days</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">Last 30 Days</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Status</th>
                            <th>Check Out</th>
                            <th>Break</th>
                            <th>Late</th>
                            <th>Overtime</th>
                            <th>Production Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = $logs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['work_date'])); ?></td>
                            <td><?php echo $row['punch_in'] ? date('h:i A', strtotime($row['punch_in'])) : '-'; ?></td>
                            <td>
                                <span class="badge badge-pill <?php echo ($row['status'] == 'Present' ? 'badge-present' : 'badge-absent'); ?>">
                                    <i class="fas fa-circle small mr-1"></i> <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['punch_out'] ? date('h:i A', strtotime($row['punch_out'])) : '-'; ?></td>
                            <td><?php echo $row['break_duration']; ?> Min</td>
                            <td><?php echo $row['late']; ?> Min</td>
                            <td><?php echo $row['overtime_hours']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-success py-0 px-2 font-weight-bold" style="border-radius: 4px; font-size: 12px;">
                                    <i class="far fa-clock"></i> <?php echo $row['production_hours']; ?>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. LIVE CLOCK
    setInterval(() => {
        document.getElementById('liveClock').innerText = new Date().toLocaleString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true, day: '2-digit', month: 'short', year: 'numeric' }).replace(',', '');
    }, 1000);

    // 2. PRODUCTION TIMER
    let startTime = <?php echo ($att && $att['punch_in']) ? strtotime($att['punch_in']) * 1000 : 'null'; ?>;
    let isPunchedIn = <?php echo ($is_punched_in && $status != 'On Break') ? 'true' : 'false'; ?>;

    function updateTimer() {
        if (startTime && isPunchedIn) {
            const now = new Date().getTime();
            const diff = now - startTime;
            const h = Math.floor(diff / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);
            document.getElementById('timerDisplay').innerText = `${h}h ${m < 10 ? '0'+m : m}m ${s < 10 ? '0'+s : s}s`;
        }
    }
    if (isPunchedIn) { setInterval(updateTimer, 1000); }

    // 3. ACTION HANDLER
    function handlePunch(actionType) {
        $.post('attendance.php', { action: actionType }, function(data) {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert("Error: " + (data.message || "Operation failed"));
            }
        }, 'json').fail(function(xhr) {
            console.log(xhr.responseText);
            alert("Server connection failed.");
        });
    }
</script>
</body>
</html>