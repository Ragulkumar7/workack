<?php
// attendance.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../include/db_connect.php';

// Employee ID from session (fallback = 1 for Arun Kumar)
$emp_id = $_SESSION['emp_id'] ?? 1;

// Current date & time (IST)
date_default_timezone_set('Asia/Kolkata');
$today        = date('Y-m-d');
$current_time = date('h:i A, d M Y');

// Fetch employee details
$sql_emp = "SELECT name, role FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql_emp);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$employee_name = $employee['name'] ?? 'Employee';

// Fetch Today's attendance record
$sql_today = "SELECT * FROM attendance WHERE emp_id = ? AND work_date = ?";
$stmt_today = $conn->prepare($sql_today);
$stmt_today->bind_param("is", $emp_id, $today);
$stmt_today->execute();
$today_record = $stmt_today->get_result()->fetch_assoc();

// Initialize Today's Variables
$total_hours_today = '0.00 / 9.00';
$punch_in_time     = 'Not punched';
$production_today  = '0h 00m';

if ($today_record && $today_record['punch_in']) {
    $punch_in_time = date('h:i A', strtotime($today_record['punch_in']));
    $out = $today_record['punch_out'] ? strtotime($today_record['punch_out']) : time();
    $in  = strtotime($today_record['punch_in']);
    $sec = $out - $in;
    $total_hours_today = sprintf("%.2f / 9.00", ($sec / 3600));
    $production_today = $today_record['production_hours'] ?? '0h 00m';
}

// Fetch history records
$sql_att = "SELECT * FROM attendance WHERE emp_id = ? ORDER BY work_date DESC LIMIT 10";
$stmt_att = $conn->prepare($sql_att);
$stmt_att->bind_param("i", $emp_id);
$stmt_att->execute();
$result_att = $stmt_att->get_result();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .main-wrapper { display: flex; }
    .content-body { flex: 1; padding: 20px; }
    
    /* Card Styles */
    .card { border: none; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .punch-card { text-align: center; padding: 20px; }
    .profile-img { width: 100px; height: 100px; border-radius: 50%; border: 4px solid #00d2ff; margin-bottom: 15px; }
    
    /* Stats Icons */
    .stat-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 10px; }
    .bg-orange { background-color: #ff9b44; }
    .bg-blue { background-color: #007bff; }
    .bg-pink { background-color: #e83e8c; }
    
    /* Production Timeline */
    .production-bar { height: 25px; border-radius: 5px; background: #e9ecef; display: flex; overflow: hidden; margin: 15px 0; }
    .bar-segment { height: 100%; }
    .bar-green { background-color: #00d084; width: 40%; }
    .bar-yellow { background-color: #ffbc00; width: 10%; }
    
    /* Status Badges */
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .status-present { background: #e6faf2; color: #00d084; border: 1px solid #00d084; }
    .status-absent { background: #fff0f0; color: #ff4d4d; border: 1px solid #ff4d4d; }
    .prod-hours-badge { background: #00d084; color: white; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
</style>

<div class="main-wrapper">
    <?php include '../include/sidebar.php'; ?>

    <div class="content-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold">Employee Attendance</h4>
                <small class="text-muted">Attendance > Employee Attendance</small>
            </div>
            <div>
                <button class="btn btn-outline-secondary btn-sm me-2">Export <i class="fa fa-chevron-down"></i></button>
                <button class="btn btn-danger btn-sm"><i class="fa fa-file-pdf"></i> Report</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card punch-card">
                    <p class="text-muted mb-1">Good Morning, <?= htmlspecialchars($employee_name) ?></p>
                    <h5 class="fw-bold mb-3"><?= date('h:i A') ?></h5>
                    <img src="https://via.placeholder.com/100" class="profile-img" alt="Profile">
                    <div class="badge bg-danger mb-3 p-2">Production: <?= htmlspecialchars($production_today) ?></div>
                    <p class="small text-muted mb-4"><i class="fa fa-clock"></i> Punch In at <?= $punch_in_time ?></p>
                    <button class="btn btn-dark w-100 fw-bold">Punch Out</button>
                </div>
            </div>

            <div class="col-md-9">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card p-3">
                            <div class="stat-icon bg-orange"><i class="fa fa-clock"></i></div>
                            <h4 class="fw-bold mb-0"><?= $total_hours_today ?></h4>
                            <small class="text-muted">Total Hours Today</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3">
                            <div class="stat-icon bg-dark"><i class="fa fa-briefcase"></i></div>
                            <h4 class="fw-bold mb-0">10 / 40</h4>
                            <small class="text-muted">Total Hours Week</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3">
                            <div class="stat-icon bg-blue"><i class="fa fa-calendar-check"></i></div>
                            <h4 class="fw-bold mb-0">75 / 98</h4>
                            <small class="text-muted">Total Hours Month</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3">
                            <div class="stat-icon bg-pink"><i class="fa fa-hourglass-half"></i></div>
                            <h4 class="fw-bold mb-0">16 / 28</h4>
                            <small class="text-muted">Overtime this Month</small>
                        </div>
                    </div>
                </div>

                <div class="card p-4 mt-3">
                    <div class="row text-center mb-3">
                        <div class="col"><small class="text-muted">Total Working hours</small><h6>12h 36m</h6></div>
                        <div class="col"><small class="text-muted">Productive Hours</small><h6>08h 36m</h6></div>
                        <div class="col"><small class="text-muted">Break hours</small><h6>22m 15s</h6></div>
                        <div class="col"><small class="text-muted">Overtime</small><h6>02h 15m</h6></div>
                    </div>
                    <div class="production-bar">
                        <div class="bar-segment bar-green"></div>
                        <div class="bar-segment bar-yellow"></div>
                        <div class="bar-segment bar-green" style="width: 30%"></div>
                        <div class="bar-segment bg-primary" style="width: 15%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small px-1">
                        <span>06:00</span><span>10:00</span><span>14:00</span><span>18:00</span><span>22:00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="fw-bold mb-4">Recent Attendance History</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Status</th>
                            <th>Check Out</th>
                            <th>Production Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_att->fetch_assoc()): 
                            $status = $row['status'] ?? 'Unknown';
                            $class = ($status === 'Present') ? 'status-present' : 'status-absent';
                        ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['work_date'])) ?></td>
                            <td><?= $row['punch_in'] ? date('h:i A', strtotime($row['punch_in'])) : '-' ?></td>
                            <td><span class="status-badge <?= $class ?>">● <?= $status ?></span></td>
                            <td><?= $row['punch_out'] ? date('h:i A', strtotime($row['punch_out'])) : '-' ?></td>
                            <td><span class="prod-hours-badge"><?= htmlspecialchars($row['production_hours']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>